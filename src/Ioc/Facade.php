<?php

declare(strict_types = 1);

namespace Rush\Ioc;

/**
 * Class Facade
 * @package Rush\Ioc
 */
abstract class Facade
{
    /**
     * Create the instance of actually invoking method
     * @param array $vars
     * @param bool $isNew
     * @return object
     * @throws IocException
     */
    public static function run(array $vars = [], bool $isNew = false): object
    {
        $name = static::getCallName();

        if (Container::getInstance()->look($name) === false) {
            Container::getInstance()->inject($name, static::getCallClass());
        }

        return Container::getInstance()->make($name, $vars, $isNew);
    }

    /**
     * Get the name of invoking method
     * @return string
     */
    abstract public static function getCallName(): string;

    /**
     * Get the class full name of actually invoking method
     * @return string
     */
    abstract public static function getCallClass(): string;

    /**
     * Call the normal method statically
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws IocException
     */
    public static function __callStatic(string $method, array $args = []): mixed
    {
        return call_user_func_array([static::run(), $method], $args);
    }
}
