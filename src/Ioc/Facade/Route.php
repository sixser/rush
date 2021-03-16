<?php

declare(strict_types = 1);

namespace Rush\Ioc\Facade;

use Rush\Ioc\Facade;

/**
 * Class Router
 * @package Rush\Ioc\Facade
 */
class Route extends Facade
{
    /**
     * @inheritDoc
     */
    public static function getCallName(): string
    {
        return 'Route';
    }

    /**
     * @inheritDoc
     */
    public static function getCallClass(): string
    {
        return \Rush\Http\Router::class;
    }
}
