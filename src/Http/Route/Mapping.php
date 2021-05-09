<?php

declare(strict_types = 1);

namespace Rush\Http\Route;

use Attribute;
use Closure;
use Rush\Http\HttpException;

/**
 * Class Mapping
 * @package Rush\Http\Route
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Mapping
{
    /**
     * Mapping constructor.
     * @param string|Closure $target Class method name or Closure.
     * @param string $path Access path.
     * @param array $methods Access methods.
     * @param array $middlewares Current middlewares.
     * @throws HttpException
     */
    public function __construct(string|Closure $target, string $path, array $methods = [], array $middlewares = [])
    {
        $register = new Register();
        $register->setTarget($target);
        $register->setPath($path);
        $register->setMethods($methods);
        $register->setMiddlewares($middlewares);
        $register->execute();
    }
}
