<?php

declare(strict_types = 1);

namespace Rush\Http\Message;

/**
 * Class Request
 * @package Rush\Http\Message
 */
class Request extends Message
{
    /**
     * Request Method
     * @var string
     */
    protected string $method = '';
    
    /**
     * Request Uri
     * @var string
     */
    protected string $uri = '';

    /**
     * Query Param
     * @var array
     */
    protected array $query = [];

    /**
     * Parsed Body Param
     * @var array
     */
    protected array $request = [];
    
    /**
     * Cookie Param
     * @var array
     */
    protected array $cookies = [];

    /**
     * Upload files
     * @var array
     */
    protected array $files = [];

    /**
     * Server Param
     * @var array
     */
    protected array $server = [];

    /**
     * Get request method
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Set request method
     * @param string $method Request method.
     * @return static
     */
    public function withMethod(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get request uri
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Set request uri
     * @param string $uri Request Uri
     * @return static
     */
    public function withUri(string $uri): static
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Get a specific query param by name
     * @param string $name Param name.
     * @param mixed $default Default value.
     * @return mixed
     */
    public function getQuery(string $name, mixed $default = null): mixed
    {
        return $this->query[$name] ?? $default;
    }

    /**
     * Set a query param
     * @param string $name Param name.
     * @param string $value Param value.
     * @return static
     */
    public function withQuery(string $name, string $value): static
    {
        $this->query[$name] = $value;

        return $this;
    }

    /**
     * Get a specific request param by name
     * @param string $name Param name.
     * @param mixed $default Default value.
     * @return mixed
     */
    public function getRequest(string $name, mixed $default = null): mixed
    {
        return $this->request[$name] ?? $default;
    }

    /**
     * Set a request param
     * @param string $name Param name.
     * @param string $value Param value.
     * @return static
     */
    public function withRequest(string $name, string $value): static
    {
        $this->request[$name] = $value;

        return $this;
    }

    /**
     * Get a specific cookie param by name
     * @param string $name Param name.
     * @param mixed $default Default value.
     * @return mixed
     */
    public function getCookie(string $name, mixed $default = null): mixed
    {
        return $this->cookies[$name] ?? $default;
    }

    /**
     * Set a cookie param
     * @param string $name Param name.
     * @param string $value Param value.
     * @return static
     */
    public function withCookie(string $name, string $value): static
    {
        $this->cookies[$name] = $value;

        return $this;
    }

    /**
     * Get a specific server param by name
     * @param string $name Param name.
     * @param mixed $default Default value.
     * @return mixed
     */
    public function getServer(string $name, mixed $default = null): mixed
    {
        return $this->server[$name] ?? $default;
    }

    /**
     * Set a server param
     * @param string $name Param name.
     * @param string $value Param value.
     * @return static
     */
    public function withServer(string $name, string $value): static
    {
        $this->server[$name] = $value;

        return $this;
    }

    /**
     * Get a specific server param by name
     * @return UploadFile[]
     */
    public function getUploadFiles(): array
    {
        return $this->files;
    }

    /**
     * Get a specific server param by name
     * @param string $name File name.
     * @return false|UploadFile
     */
    public function getUploadFile(string $name): false|UploadFile
    {
        return $this->files[$name] ?? false;
    }

    /**
     * Set a server param
     * @param string $name File name.
     * @param UploadFile $value UploadFile object.
     * @return static
     */
    public function withUploadFile(string $name, UploadFile $value): static
    {
        $this->files[$name] = $value;

        return $this;
    }
}
