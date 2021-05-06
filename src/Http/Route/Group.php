<?php

declare(strict_types = 1);

namespace Rush\Http\Route;

use Attribute;

/**
 * Class Group
 * @package Rush\Http\Route
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Group
{
    /**
     * Group constructor.
     * @param string $class Full class name.
     * @param string $prefix Access path prefix.
     * @param array $middlewares Group middlewares.
     */
    public function __construct(string $class, string $prefix = '', array $middlewares = [])
    {
        Register::setGlobalClass($class);
        Register::setGlobalPrefix($prefix);
        Register::setGlobalMiddlewares($middlewares);
    }
}