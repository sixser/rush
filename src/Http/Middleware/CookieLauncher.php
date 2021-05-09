<?php

declare(strict_types = 1);

namespace Rush\Http\Middleware;

use Rush\Http\Cookie\CookieMaker;
use Rush\Http\Handler;
use Rush\Http\Message\Request;
use Rush\Http\Message\Response;
use Rush\Http\Processor;
use Rush\Ioc\Container;
use Rush\Ioc\IocException;

/**
 * Class CookieLauncher
 * @package Rush\Http\Middleware
 */
class CookieLauncher extends Processor
{
    /**
     * @inheritDoc
     * @throws IocException
     */
    public function process(Request $request, Handler $handler): Response
    {
        /**
         * @var CookieMaker $cookie
         */
        $cookie = Container::getInstance()->make(CookieMaker::class);

        $response =  parent::process($request, $handler);

        foreach ($cookie->getQueue() as $cookie) {
            $response->withHeader('Set-Cookie', $cookie->getContent());
        }

        return $response;
    }
}
