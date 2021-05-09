<?php

declare(strict_types = 1);

namespace Rush\Http\Route;

use Closure;
use Rush\Http\HttpException;

/**
 * Class Register
 * @package Rush\Http\Route
 */
class Register
{
    /**
     * Group Class
     * @var string
     */
    protected static string $group_class = '';

    /**
     * Group Name
     * @var string
     */
    protected static string $group_prefix = '';

    /**
     * Group Middlewares
     * @var array
     */
    protected static array $group_middlewares = [];

    /**
     * Current Target
     * @var string|Closure
     */
    protected string|Closure $target;

    /**
     * Current Path
     * @var string
     */
    protected string $path;

    /**
     * Current Http Methods
     * @var array
     */
    protected array $methods;

    /**
     * Current Middlewares
     * @var array
     */
    protected array $middlewares;

    /**
     * Set group class
     * @param string $class Class name.
     * @return void
     */
    public static function setGroupClass(string $class): void
    {
        self::$group_class = $class;
    }

    /**
     * Set group name
     * @param string $prefix Path prefix.
     * @return void
     */
    public static function setGroupPrefix(string $prefix): void
    {
        self::$group_prefix = $prefix;
    }

    /**
     * Set group middlewares
     * @param array $middlewares All middlewares class name of the group.
     * @return void
     */
    public static function setGroupMiddlewares(array $middlewares): void
    {
        self::$group_middlewares = $middlewares;
    }

    /**
     * Set current target
     * @param string|Closure $target Class method name and anonymous function.
     * @return void
     */
    public function setTarget(string|Closure $target): void
    {
        $this->target = $target;
    }

    /**
     * Set current path
     * @param string $path Access path.
     * @return void
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * Set current methods
     * @param array $methods Http methods that allowed.
     * @return void
     */
    public function setMethods(array $methods): void
    {
        $this->methods = $methods;
    }

    /**
     * Set current middlewares
     * @param array $middlewares All middlewares class name of the rule.
     * @return void
     */
    public function setMiddlewares(array $middlewares): void
    {
        $this->middlewares = $middlewares;
    }

    /**
     * Register a rule
     * @return void
     * @throws HttpException
     */
    public function execute(): void
    {
        $rule = new Rule(
            static::$group_prefix . $this->path,
            is_string($this->target) ? static::$group_class.'@'.$this->target : $this->target,
            $this->methods,
            array_merge(static::$group_middlewares, $this->middlewares)
        );

        Router::register($rule);
    }
}
