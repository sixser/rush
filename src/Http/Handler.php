<?php

declare(strict_types = 1);

namespace Rush\Http;

use Closure;
use Rush\Http\Message\Request;
use Rush\Http\Message\Response;

/**
 * Class Handler
 * @package Rush\Http
 */
class Handler
{
    /**
     * All Middleware
     * @var array
     */
    protected array $middlewares = [];

    /**
     * Http Router
     * @var Router|null
     */
    protected Router|null $router = null;

    /**
     * Route Target
     * @var Closure|null
     */
    protected Closure|null $target = null;

    /**
     * Set http router
     * @param Router $router Http router.
     * @return static
     */
    public function withRouter(Router $router): static
    {
        $this->router = $router;

        return $this;
    }

    /**
     * Set base http middleware
     * @param string ...$middleware Http middleware.
     * @return static
     */
    public function withMiddlewares(string ...$middleware): static
    {
        array_push($this->middlewares, ...$middleware);

        return $this;
    }

    /**
     * Handle http request
     * @param Request $request Http request.
     * @return Response
     * @throws HttpException
     */
    public function handle(Request $request): Response
    {
        if (is_callable($this->target) === false) {
            $this->dispatch($request->getMethod(), $request->getUri());
        }

        if (empty($this->middlewares) === true) {
            if (is_callable($this->target) === true) {
                $response = call_user_func($this->target, $request);
                $this->target = null;
            }

            if (isset($response) === true && $response instanceof Response) {
                return $response;
            } else {
                return (new Response())
                    ->withStatus(200, 'OK')
                    ->withHeader('Content-Type', 'text/html;charset=utf8')
                    ->withHeader('Connection', 'keep-alive')
                    ->withHeader('Content-Length', '14')
                    ->withContent('Hello, World! ');
            }
        }

        $middleware = array_shift($this->middlewares);

        return (new $middleware())->process($request, $this);
    }

    /**
     * Dispatch http route
     * @param string $method Http request method.
     * @param string $uri Http request uri.
     * @return void
     * @throws HttpException
     */
    protected function dispatch(string $method, string $uri): void
    {
        if ($this->router instanceof Router === false) {
            return;
        }

        $path = (string) parse_url($uri, PHP_URL_PATH);

        $rule = $this->router->parse($method, $path);

        $this->target = $rule['target'];

        if (empty($rule['middleware']) === false) {
            $this->middlewares[] = $rule['middleware'];
        }
    }
}
