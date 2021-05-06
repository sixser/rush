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
     * @param string|Closure $method Class method name or Closure.
     * @param string $path Access path.
     * @param array $methods Access methods.
     * @param array $middlewares Current middlewares.
     * @throws HttpException
     */
    public function __construct(string|Closure $method, string $path, array $methods = [], array $middlewares = [])
    {
        $register = new Register();
        $register->setCurrentMethod($method);
        $register->setCurrentPath($path);
        $register->setCurrentMethods($methods);
        $register->setCurrentMiddlewares($middlewares);
        $register->execute();
    }
}
