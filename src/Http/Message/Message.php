<?php

declare(strict_types = 1);

namespace Rush\Http\Message;

/**
 * Class Message
 * @package Rush\Http\Message
 */
class Message
{
    /**
     * Protocol Version
     * @var string
     */
    protected string $version = '1.0';

    /**
     * Message Header
     * @var array
     */
    protected array $headers = [];

    /**
     * Message Body Content
     * @var string
     */
    protected string $content = '';

    /**
     * Get protocol version
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Set protocol version
     * @param string $version Protocol Version.
     * @return static
     */
    public function withVersion(string $version): static
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get all headers
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get a specific header by name
     * @param string $name Header name.
     * @param mixed $default Default value.
     * @return mixed
     */
    public function getHeader(string $name, mixed $default = null): mixed
    {
        return $this->headers[strtolower($name)] ?? $default;
    }

    /**
     * Set a specific header
     * @param string $name Header name.
     * @param int|string $value Header value.
     * @return static
     */
    public function withHeader(string $name, int|string $value): static
    {
        if (0 === strcasecmp($name, 'Set-Cookie')) {
            $this->headers[strtolower($name)][] = $value;
        } else {
            $this->headers[strtolower($name)] = $value;
        }

        return $this;
    }

    /**
     * Get message body content
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Set message body content
     * @param string $content Message body content
     * @return static
     */
    public function withContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }
}
