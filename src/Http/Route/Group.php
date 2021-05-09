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
     * @param string $class Class name.
     * @param string $prefix Access path prefix.
     * @param array $middlewares Group middlewares.
     */
    public function __construct(string $class, string $prefix = '', array $middlewares = [])
    {
        Register::setGroupClass($class);
        Register::setGroupPrefix($prefix);
        Register::setGroupMiddlewares($middlewares);
    }
}