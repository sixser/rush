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
     * Group Middlewares
     * @var array
     */
    protected static array $global_middlewares = [];

    /**
     * Group Class
     * @var string
     */
    protected static string $global_class = '';

    /**
     * Group Name
     * @var string
     */
    protected static string $global_prefix = '';

    /**
     * Current Middlewares
     * @var array
     */
    protected array $current_middlewares;

    /**
     * Current Target
     * @var string|Closure
     */
    protected string|Closure $current_method;

    /**
     * Current Http Methods
     * @var array
     */
    protected array $current_methods;

    /**
     * Current Path
     * @var string
     */
    protected string $current_path;

    /**
     * Set group middlewares
     * @param array $global_middlewares All middlewares class full name.
     * @return void
     */
    public static function setGlobalMiddlewares(array $global_middlewares): void
    {
        self::$global_middlewares = $global_middlewares;
    }

    /**
     * Set group class
     * @param string $global_class Class name.
     * @return void
     */
    public static function setGlobalClass(string $global_class): void
    {
        self::$global_class = $global_class;
    }

    /**
     * Set group name
     * @param string $global_prefix Path prefix.
     * @return void
     */
    public static function setGlobalPrefix(string $global_prefix): void
    {
        self::$global_prefix = $global_prefix;
    }

    /**
     * Set current middlewares
     * @param array $current_middlewares All middlewares class full name.
     * @return void
     */
    public function setCurrentMiddlewares(array $current_middlewares): void
    {
        $this->current_middlewares = $current_middlewares;
    }

    /**
     * Set current target
     * @param string|Closure $current_method Class method name and anonymous function.
     * @return void
     */
    public function setCurrentMethod(string|Closure $current_method): void
    {
        $this->current_method = $current_method;
    }

    /**
     * Set current methods
     * @param array $current_methods Http methods that allowed.
     * @return void
     */
    public function setCurrentMethods(array $current_methods): void
    {
        $this->current_methods = $current_methods;
    }

    /**
     * Set current path
     * @param string $current_path Access path.
     * @return void
     */
    public function setCurrentPath(string $current_path): void
    {
        $this->current_path = $current_path;
    }

    /**
     * Register a rule
     * @return void
     * @throws HttpException
     */
    public function execute(): void
    {
        $rule = new Rule(
            static::$global_prefix . $this->current_path,
            is_string($this->current_method) ? static::$global_class.'@'.$this->current_method : $this->current_method,
            $this->current_methods,
            array_merge(static::$global_middlewares, $this->current_middlewares)
        );

        Router::register($rule);
    }
}
