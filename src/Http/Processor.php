<?php

declare(strict_types = 1);

namespace Rush\Http;

use Rush\Http\Message\Request;
use Rush\Http\Message\Response;

/**
 * Class Processor
 * @package Rush\Http
 */
class Processor
{
    /**
     * Middleware process request
     * @param Request $request Http request.
     * @param Handler $handler Http handler.
     * @return Response
     * @throws HttpException
     */
    public function process(Request $request, Handler $handler): Response
    {
        return $handler->handle($request);
    }
}
