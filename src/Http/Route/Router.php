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
        $path = static::filter($rule->getPath());

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
        $path = static::filter($path);

        if (isset(static::$rules[$path]) === false) {
            throw new HttpException('There are no matching routing rules');
        }

        if (
            empty(static::$rules[$path]->getMethods()) === false &&
            in_array(strtoupper($method), static::$rules[$path]->getMethods(), true) === false
        ) {
            throw new HttpException('Current path is restricted');
        }

        return [static::$rules[$path]->getTarget(), static::$rules[$path]->getMiddlewares()];
    }

    /**
     * Filter '/' at the begin and end of the path
     * @param string $path Route path.
     * @return string
     */
    protected static function filter(string $path): string
    {
        if (str_starts_with($path, '/') === true) {
            $path = substr($path, 1);
        }

        if (str_ends_with($path, '/') === true) {
            $path = substr($path, 0, -1);
        }

        return $path;
    }
}
