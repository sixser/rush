<?php

declare(strict_types = 1);

namespace Rush\Ioc\Facade;

use Rush\Ioc\Facade;

/**
 * Class Config
 * @package Rush\Ioc\Facade
 */
class Config extends Facade
{
    /**
     * @inheritDoc
     */
    public static function getCallName(): string
    {
        return 'Config';
    }

    /**
     * @inheritDoc
     */
    public static function getCallClass(): string
    {
        return \Rush\Config\Repository::class;
    }
}
