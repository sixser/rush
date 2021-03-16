<?php

declare(strict_types = 1);

namespace Rush\Http\Cookie;

use Rush\Http\HttpException;

/**
 * Class Manager
 * @package Sixser\Http\Cookie
 */
class CookieMaker
{
    /**
     * New Coolie Variate
     * @var array
     */
    protected array $queue = [];

    /**
     * Generate a new cookie record and put it in queue
     * @param string $name Cookie name.
     * @param mixed $value Cookie value.
     * @return CookieElement
     * @throws HttpException
     */
    public function make(string $name, mixed $value): CookieElement
    {
        return $this->queue[] = new CookieElement($name, $value);
    }

    /**
     * Get all new cookie record from queue
     * @return CookieElement[]
     */
    public function getQueue(): array
    {
        return $this->queue;
    }
}
