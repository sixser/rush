<?php

declare(strict_types = 1);

namespace Rush\Http;

use Closure;

/**
 * Class Router
 * @package Rush\Http
 */
class Router
{
    /**
     * Router Rules
     * @var array
     */
    protected array $rules = [];

    /**
     * Rule Path Alias
     * @var array
     */
    protected array $maps = [];

    /**
     * Route Path Prefix
     * @var string
     */
    protected string $prefix = '';

    /**
     * Route Middleware
     * @var string
     */
    protected string $middleware = '';

    /**
     * Filter '/' at the begin and end of the path
     * @param string $path Route path.
     * @return string
     */
    protected static function filter(string $path): string
    {
        if (str_starts_with($path, '/') === true) {
            $path = (string) substr($path, 1);
        }

        if (str_ends_with($path, '/') === true) {
            $path = (string) substr($path, 0, -1);
        }

        return $path;
    }

    /**
     * Register route rule
     * @param string $method Http method, include GET, POST.
     * @param string $path Router path.
     * @param Closure|string $target The string include class name and method name or closure.
     * @return void
     * @throws HttpException
     */
    public function register(string $method, string $path, Closure|string $target): void
    {
        $path = static::filter($path);
        if (empty($this->prefix) === false) {
            $path .= $this->prefix . '/';
        }

        $this->rules[$path][strtoupper($method)] = [
            'target' => $this->process($target),
            'middleware' => $this->middleware
        ];
    }

    /**
     * Process a rule target to be a closure
     * @param Closure|string $target
     * @return Closure
     * @throws HttpException
     */
    protected function process(Closure|string $target): Closure
    {
        if ($target instanceof Closure) {
            return $target;
        }

        if (str_contains($target, '@') === false) {
            throw new HttpException('Router target must be a string like class@method');
        }

        [$class, $method] = explode('@', $target);

        return function ($request) use ($class, $method) {
            return (new $class())->$method($request);
        };
    }

    /**
     * Fetch a rule
     * @param string $method Access method.
     * @param string $path Request path.
     * @return array
     * @throws HttpException
     */
    public function parse(string $method, string $path): array
    {
        $method = strtoupper($method);
        $path = $this->real(static::filter($path));

        if (isset($this->rules[$path]) === false) {
            throw new HttpException('There are no matching routing rules');
        }

        if (isset($this->rules[$path][$method]) === false) {
            throw new HttpException('Current access is restricted');
        }

        return $this->rules[$path][$method];
    }

    /**
     * Set a alias of rule
     * @param string $name Router rule name.
     * @param string $alias Alias of the route rule.
     * @return void
     * @throws HttpException
     */
    public function alias(string $name, string $alias): void
    {
        $alias = static::filter($alias);

        if (isset($this->maps[$name]) === false) {
            $this->maps[$alias] = $name;
        }

        $path = static::filter($name);
        if (empty($this->prefix) === false) {
            $path .= $this->prefix . '/';
        }

        if (isset($this->rules[$path]) === true) {
            $this->maps[$alias] = $path;
        }

        throw new HttpException("Route is not exist({$name})");
    }

    /**
     * Fetch rule real name by alias
     * @param string $key The alias.
     * @return string
     */
    public function real(string $key): string
    {
        if (isset($this->maps[$key]) === true) {
            return $this->real($this->maps[$key]);
        }

        return $key;
    }

    /**
     * Register rules using prefix
     * @param string $name Prefix name.
     * @param Closure $routes A closure including register route.
     * @return void
     */
    public function prefix(string $name, Closure $routes): void
    {
        $this->prefix = $name;

        call_user_func($routes);

        $this->prefix = '';
    }

    /**
     * Register rules using middleware
     * @param string $name Middleware name.
     * @param Closure $routes
     * @return void
     * @throws HttpException
     */
    public function middleware(string $name, Closure $routes): void
    {
        if (class_exists($name) === false) {
            throw new HttpException("Cannot find class {$name}");
        }

        $this->middleware = $name;

        call_user_func($routes);

        $this->middleware = '';
    }

    /**
     * Register a rule of get method
     * @param string $path Router path.
     * @param Closure|string $target The string include class name and method name or closure.
     * @return void
     * @throws HttpException
     */
    public function get(string $path, Closure|string $target): void
    {
        $this->register('GET', $path, $target);
    }

    /**
     * Register a rule of post method
     * @param string $path Router path.
     * @param Closure|string $target The string include class name and method name or closure.
     * @return void
     * @throws HttpException
     */
    public function post(string $path, Closure|string $target): void
    {
        $this->register('POST', $path, $target);
    }

    /**
     * Register a rule of put method
     * @param string $path Router path.
     * @param Closure|string $target The string include class name and method name or closure.
     * @return void
     * @throws HttpException
     */
    public function put(string $path, Closure|string $target): void
    {
        $this->register('PUT', $path, $target);
    }

    /**
     * Register a rule of delete method
     * @param string $path Router path.
     * @param Closure|string $target The string include class name and method name or closure.
     * @return void
     * @throws HttpException
     */
    public function delete(string $path, Closure|string $target): void
    {
        $this->register('DELETE', $path, $target);
    }
}
