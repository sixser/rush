<?php

declare(strict_types = 1);

namespace Rush\Http\Cookie;

use Rush\Http\HttpException;
use Stringable;

/**
 * Class CookieElement
 * @package Sixser\Http\Cookie
 */
class CookieElement implements Stringable
{
    /**
     * Cookie Name
     * @var string
     */
    protected string $name = '';

    /**
     * Cookie Value Be Formatted
     * @var string
     */
    protected string $value = '';

    /**
     * Cookie Expire Time
     * @var string
     */
    protected string $expire = '';

    /**
     * Cookie Permanent
     * @var bool
     */
    protected bool $permanent = false;

    /**
     * Cookie Domain
     * @var string
     */
    protected string $domain = '';

    /**
     * Cookie Path
     * @var string
     */
    protected string $path = '/';

    /**
     * Cookie Secure
     * @var bool
     */
    protected bool $secure = false;

    /**
     * Cookie HttpOnly
     * @var bool
     */
    protected bool $http_only = false;

    /**
     * Cookie SameSite
     * @var string
     */
    protected string $same_site = 'none';

    /**
     * Cookie SameSite Options
     * @var array|string[]
     */
    protected array $same_site_options = ['none', 'lax', 'strict'];

    /**
     * CookieElement constructor
     * @param string $name Cookie name.
     * @param string $value Cookie value.
     * @throws HttpException
     */
    public function __construct(string $name, string $value)
    {
        if (empty($name) === true) {
            throw new HttpException('Cookie name cannot be empty');
        }

        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Set cookie expire
     * @param int $time Time to live.
     * @return static
     */
    public function withExpire(int $time): static
    {
        $this->expire = gmstrftime("%A, %d-%b-%Y %H:%M:%S GMT", time() + $time);

        return $this;
    }

    /**
     * Set cookie permanent
     * @param bool $permanent Enable permanent when true, or not.
     * @return static
     */
    public function withPermanent(bool $permanent): static
    {
        $this->permanent = $permanent;

        return $this;
    }

    /**
     * Set cookie domain
     * @param string $domain The domain.
     * @return static
     */
    public function withDomain(string $domain): static
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Set cookie path
     * @param string $path The path.
     * @return static
     */
    public function withPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Set cookie secure
     * @param bool $secure Enable secure when true, or not.
     * @return static
     */
    public function withSecure(bool $secure): static
    {
        $this->secure = $secure;

        return $this;
    }

    /**
     * Set cookie httponly
     * @param bool $httpOnly Enable httponly when true, or not.
     * @return static
     */
    public function withHttpOnly(bool $httpOnly): static
    {
        $this->http_only = $httpOnly;

        return $this;
    }

    /**
     * Set cookie sameSite
     * @param string $sameSite One of none, lax, strict.
     * @return static
     * @throws HttpException
     */
    public function withSameSite(string $sameSite): static
    {
        $sameSite = strtolower($sameSite);
        if (in_array($sameSite, $this->same_site_options) === false) {
            throw new HttpException('Cookie sameSite must be none, lax and strict');
        }

        $this->same_site = $sameSite;

        return $this;
    }

    /**
     * Convert cookie record object to string
     * @return string
     */
    public function getContent(): string
    {
        $raw = "{$this->name}={$this->value}";
        $raw .= empty($this->expire) === true ? '' : "; Expires:{$this->expire}";
        $raw .= $this->permanent === false ? '' : "; Max-Age:{$this->expire}";
        $raw .= empty($this->domain) === true ? '' : "; Domain:{$this->domain}";
        $raw .= empty($this->path) === true ? '' : "; Path:{$this->path}";
        $raw .= $this->secure === false ? '' : "; Secure";
        $raw .= $this->http_only === false ? '' : "; HttpOnly";
        $raw .= empty($this->same_site) === true ? '' : "; SameSite:{$this->same_site}";

        return $raw;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->getContent();
    }
}
