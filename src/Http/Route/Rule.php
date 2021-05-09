<?php

declare(strict_types = 1);

namespace Rush\Http\Route;

use Closure;
use Rush\Http\HttpException;

/**
 * Class Rule
 * @package Rush\Http\Route
 */
class Rule
{
    /**
     * Target Access Path
     * @var string
     */
    protected string $path;

    /**
     * Target
     * @var string|Closure
     */
    protected string|Closure $target;

    /**
     * All The Methods That Allowed
     * @var array|string[]
     */
    protected array $methods;

    /**
     * All The Middlewares That Passes Through
     * @var array
     */
    protected array $middlewares;

    /**
     * Rule constructor.
     * @param string $path Request Path.
     * @param string|Closure $target Request Target.
     * @param array $methods Http methods that allowed.
     * @param array $middlewares All middlewares.
     * @throws HttpException
     */
    public function __construct(string $path, string|Closure $target, array $methods = [], array $middlewares = [])
    {
        ! ($target instanceof Closure || str_contains($target, '@')) &&
        throw new HttpException("Fail to make route rule, target is not accessible.");

        $this->path = trim($path);
        $this->target = $target;
        $this->methods = array_map(fn ($method) => strtoupper($method), $methods);
        $this->middlewares = $middlewares;
    }

    /**
     * Get path
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get target
     * @return Closure|string
     */
    public function getTarget(): Closure|string
    {
        return $this->target;
    }

    /**
     * Get methods
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * Get middlewares
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
