<?php

declare(strict_types = 1);

namespace Rush\Http\Middleware;

use Rush\Http\Handler;
use Rush\Http\Message\Request;
use Rush\Http\Message\Response;
use Rush\Http\Processor;
use Rush\Http\Session\FileSession;
use Rush\Ioc\Container;
use Rush\Ioc\IocException;

/**
 * Class SessionLauncher
 * @package Sixser\Http\Middleware
 */
class SessionLauncher extends Processor
{
    /**
     * @inheritDoc
     * @throws IocException
     */
    public function process(Request $request, Handler $handler): Response
    {
        $sessionName = FileSession::getName();
        $oldSessionId = $request->getCookie($sessionName, '');

        /**
         * @var FileSession $session
         */
        $session = Container::getInstance()->make(FileSession::class);
        $session->read($oldSessionId);

        $response =  parent::process($request, $handler);

        $session->write();
        $newSessionId = $session->getIdentity();
        if (0 !== strcmp($oldSessionId, $newSessionId)) {
            $value = sprintf(
                "%s=%s;path=/;expires=%s",
                $sessionName, $newSessionId, gmstrftime("%A, %d-%b-%Y %H:%M:%S GMT",time()+9600)
            );
            $response->withHeader('Set-Cookie', $value);
        }

        return $response;
    }
}
