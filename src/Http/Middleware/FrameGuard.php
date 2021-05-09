<?php

declare(strict_types = 1);

namespace Rush\Http\Middleware;

use Rush\Http\Handler;
use Rush\Http\Message\Request;
use Rush\Http\Message\Response;
use Rush\Http\Processor;

/**
 * Class FrameGuard
 * @package Rush\Http\Middleware
 */
class FrameGuard extends Processor
{
    /**
     * @inheritDoc
     */
    public function process(Request $request, Handler $handler): Response
    {
        $response = parent::process($request, $handler);

        $response->withHeader('X-Frame-Options', 'SAMEORIGIN');

        return $response;
    }
}
