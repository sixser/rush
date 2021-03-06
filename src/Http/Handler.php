<?php

declare(strict_types = 1);

namespace Rush\Http;

use Closure;
use Rush\Http\Message\Request;
use Rush\Http\Message\Response;
use Rush\Http\Route\Router;
use Rush\Ioc\Container;
use Rush\Ioc\IocException;

/**
 * Class Handler
 * @package Rush\Http
 */
class Handler
{
    /**
     * All The Middlewares
     * @var string[]
     */
    protected array $middlewares = [];

    /**
     * Route Target
     * @var string|Closure|null
     */
    protected string|Closure|null $target = null;

    /**
     * Handle http request
     * @param Request $request Http request.
     * @return Response
     * @throws HttpException
     * @throws IocException
     */
    public function handle(Request $request): Response
    {
        if (is_null($this->target)) {
            $this->dispatch($request->getUri(), $request->getMethod());
        }

        if (empty($this->middlewares)) {
            return $this->call($request);
        }

        $middleware = array_pop($this->middlewares);

        return (new $middleware())->process($request, $this);
    }

    /**
     * Dispatch http route
     * @param string $uri Http request uri.
     * @param string $method Http request method.
     * @return void
     * @throws HttpException
     */
    protected function dispatch(string $uri, string $method): void
    {
        $path = (string) parse_url($uri, PHP_URL_PATH);

        [$this->target, $this->middlewares] = Router::parse($path, $method);
    }

    /**
     * Call the target
     * @param Request $request Http request.
     * @return Response
     * @throws IocException
     */
    protected function call(Request $request): Response
    {
        if (is_callable($this->target)) {
            $response =  call_user_func($this->target, $request);
        } elseif (is_string($this->target)) {
            [$class, $method] = explode('@', $this->target);
            $response = Container::getInstance()->make($class)->$method($request);
        } else {
            $response = (new Response())
                ->withStatus(200, 'OK')
                ->withHeader('Content-Type', 'text/html;charset=utf8')
                ->withHeader('Connection', 'keep-alive')
                ->withHeader('Content-Length', '14')
                ->withContent('Hello Rush! ');
        }

        $this->target = null;

        return $response;
    }
}
