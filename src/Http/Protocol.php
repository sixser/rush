<?php

declare(strict_types = 1);

namespace Rush\Http;

use Rush\Http\Message\Request;
use Rush\Http\Message\Response;
use Rush\Http\Message\UploadFile;
use Rush\Network\ProtocolInterface;

/**
 * Class Protocol
 * @package Rush\Http
 */
class Protocol implements ProtocolInterface
{
    /**
     * @inheritDoc
     */
    public static function check(string $input): int|string
    {
        $crlfPos = strpos($input, "\r\n\r\n");
        if ($crlfPos === false) {
            if (strlen($input) >= 16384) {
                return "HTTP/1.1 413 Request Entity Too Large\r\n\r\n";
            }

            return 0;
        }

        $method = strstr($input, ' ', true);

        if (
            $method === 'GET' || $method === 'HEAD' ||
            $method === 'DELETE' || $method === 'OPTIONS' || $method === 'TRACE'
        ) {
            return $crlfPos + 4;
        }

        if ($method !== 'POST' && $method !== 'PUT' && $method !== 'PATCH') {
            return "HTTP/1.1 400 Bad Request\r\n\r\n";
        }

        $header = substr($input, 0, $crlfPos);
        preg_match("/\r\ncontent-length: ?(\d+)/i", $header, $match);
        if (isset($match[1]) === false || is_numeric($match[1]) === false) {
            return "HTTP/1.1 400 Bad Request\r\n\r\n";
        }

        return $crlfPos + 4 + (int) $match[1];
    }

    /**
     * Decode http request
     * @param string $raw Raw http request message.
     * @return Request
     */
    public static function decode(string $raw): Request
    {
        [$method, $uri, $version, $headers, $body, $parsed, $files] = static::parse($raw);

        $request = (new Request())
            ->withMethod($method)
            ->withUri($uri)
            ->withVersion($version)
            ->withContent($body);

        foreach ($headers as $name => $val) {
            $request->withHeader($name, $val);
        }

        $cookies = $request->getHeader('Cookie', '');
        if (empty($cookies) === false) {
            parse_str(str_replace('; ', '&', $cookies), $cookies);
            foreach ($cookies as $name => $value) {
                $request->withCookie($name, $value);
            }
        }

        $queryString = (string)parse_url($uri, PHP_URL_QUERY);
        parse_str($queryString, $query);
        foreach ($query as $name => $val) {
            $request->withQuery($name, $val);
        }

        foreach ($parsed as $name => $val) {
            $request->withRequest($name, $val);
        }

        foreach ($files as $name => $val) {
            $request->withUploadfile(
                $name,
                new UploadFile($val['tmp_name'], $val['size'], $val['error'], $val['name'], $val['type'])
            );
        }

        return $request;
    }

    /**
     * Parse http request message
     * @param string $raw Raw http request message.
     * @return array
     */
    protected static function parse(string $raw): array
    {
        // Get the raw header.
        $rawHeader = strstr($raw, "\r\n\r\n", true) ?: '';
        [$method, $uri, $version, $headers] = static::parseHeader($rawHeader);

        // Get the raw Body.
        $rawBody = substr($raw, strpos($raw, "\r\n\r\n") + 4) ?: '';
        [$parsed, $files] = static::parseBody($rawBody, $headers['content-type'] ?? '');

        return [$method, $uri, $version, $headers, $rawBody, $parsed, $files];
    }

    /**
     * Parse request header
     * @param string $rawHeader Raw http request header.
     * @return array
     */
    protected static function parseHeader(string $rawHeader): array
    {
        $lines = explode("\r\n", $rawHeader);

        // Parse the first line, include method, uri and protocol version.
        $first = array_shift($lines) ?: '';
        $tmp = explode(' ', $first, 3);

        $method = $tmp[0];
        $uri = $tmp[1] ?? '/';
        $version = substr($tmp[2] ?? '', 5) ?: '1.0';

        // parse the order lines.
        $headers = [];
        foreach ($lines as $line) {
            [$key, $value] = explode(':', $line, 2);
            $headers[strtolower(trim($key))] = trim($value);
        }

        return [$method, $uri, $version, $headers];
    }

    /**
     * Parse request body
     * @param string $rawBody Raw http request body.
     * @param string $contentType Http request body type.
     * @return array
     */
    protected static function parseBody(string $rawBody, string $contentType): array
    {
        $parsed = $files = [];
        if (empty($rawBody) === true) {
            return [$parsed, $files];
        }

        if (str_contains($contentType, 'json') === true) {
            $parsed = (array) json_decode($rawBody, true);
        } elseif (str_contains($contentType, 'form-data') === true) {
            $boundary = '--' . strstr($contentType, '--') . "\r\n";
            $rawBody = substr($rawBody, 0, -strlen($boundary)-2);
            foreach (explode($boundary, $rawBody) as $boundary_raw) {
                if (empty($boundary_raw) === true) continue;

                [$boundary_header, $boundary_value] = explode("\r\n\r\n", $boundary_raw, 2);
                $boundary_header = strtolower($boundary_header);
                $boundary_value = substr($boundary_value, 0, -2);

                preg_match('/name="(.*?)"/', $boundary_header, $name);
                if (empty($name) === true) continue;

                preg_match('/filename="(.*?)"/', $boundary_header, $filename);
                preg_match('/content-type: (.+)?/', $boundary_header, $type);

                // Is not a file
                if (empty($filename) === true || empty($type) === true) {
                    $parsed[$name[1]] = $boundary_value;
                    continue;
                }

                // Is a file
                $error = UPLOAD_ERR_OK;
                $tmp_name = tempnam(sys_get_temp_dir(), 'rush_upload_');
                if ($tmp_name === false || file_put_contents($tmp_name, $boundary_value) === false) {
                    $error = UPLOAD_ERR_CANT_WRITE;
                }

                $files[$name[1]] = [
                    'tmp_name' => $tmp_name,
                    'name' => $filename[1],
                    'size' => strlen($boundary_value),
                    'type' => $type[1],
                    'error' => $error
                ];
            }
        } else {
            parse_str($rawBody, $parsed);
        }

        return [$parsed, $files];
    }

    /**
     * Encode http response
     * @param Response $response Http response object.
     * @return string
     */
    public static function encode(Response $response): string
    {
        $version = $response->getVersion();
        $code = $response->getCode();
        $phrase = $response->getReason();
        $headers = $response->getHeaders();
        $body = $response->getContent();

        return static::compile($version, $code, $phrase, $headers, $body);
    }

    /**
     * Compile http response message
     * @param string $version Http protocol version.
     * @param integer $code Http response status code.
     * @param string $phrase Http response phrase.
     * @param array $headers Http response header.
     * @param string $body Http response body.
     * @return string
     */
    protected static function compile(string $version, int $code, string $phrase, array $headers, string $body): string
    {
        // compile header
        $raw = sprintf("HTTP/%s %s %s\r\n", $version, $code, $phrase);
        foreach ($headers as $name => $header) {
            if (0 !== strcasecmp($name, 'Set-Cookie')) {
                $raw .= sprintf("%s: %s\r\n", $name, $header);
                continue;
            }

            foreach ($header as $value) {
                $raw .= sprintf("%s: %s\r\n", $name, $value);
            }
        }

        // compile body
        $raw .= sprintf("\r\n%s", $body);

        return $raw;
    }
}
