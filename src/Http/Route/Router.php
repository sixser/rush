<?php

declare(strict_types = 1);

namespace Rush\Http\Route;

use Rush\Http\HttpException;

/**
 * Class Router
 * @package Rush\Http
 */
class Router
{
    /**
     * Router Rules
     * @var Rule[]
     */
    public static array $rules = [];

    /**
     * Register route rule
     * @param Rule $rule Instance of a rule.
     * @return void
     */
    public static function register(Rule $rule): void
    {
        $path = trim($rule->getPath(), '/');

        static::$rules[$path] = $rule;
    }

    /**
     * Route resolution
     * @param string $path Request path.
     * @param string $method Request method.
     * @return array
     * @throws HttpException
     */
    public static function parse(string $path, string $method): array
    {
        $path = trim($path, '/');

        ! isset(static::$rules[$path]) &&
        throw new HttpException("Failed to parse route rule, $path does not matched.");

        ! empty(static::$rules[$path]->getMethods()) &&
        ! in_array(strtoupper($method), static::$rules[$path]->getMethods()) &&
        throw new HttpException("Failed to parse route rule, $method is restricted.");

        return [static::$rules[$path]->getTarget(), static::$rules[$path]->getMiddlewares()];
    }
}
