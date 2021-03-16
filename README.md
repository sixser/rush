# Rush PHP
Rush PHP - Building For A Light-Weight And High-Performance PHP Server Framework

---
## The Regulation Of Development
1. Based on PSR-0, PSR-1, PSR-2
2. Customer function, class property must be declared in all lower case with underscore separators.
3. Class Name must be declared in lower Camel-Case.
4. Class method must be declared in upper Camel-Case.

---
## Start Rush
### How To Start A HTTP Server ?
```
<?php

$router = Container::getInstance()->make(Route::getCallClass());
$middlewares = [
    \Rush\Http\Middleware\FrameGuard::class,
    \Rush\Http\Middleware\CookieLauncher::class,
    \Rush\Http\Middleware\SessionLauncher::class
];

$server = new Server('0.0.0.0', 80);
$server->on('message', function (Tcp $conn, string $raw) use ($router, $middlewares) {
    $res = Protocol::check($conn, $raw);
    if ($res !== $conn::RUN_OK) return $res;

    $request = Protocol::decode($raw);

    $response = (new Handler())->withRouter($router)->withMiddlewares(...$middlewares)->handle($request);

    $conn->send(Protocol::encode($response));
});

Server::start();

?>
```

### How To Register Route ?
```
<?php

Route::get('index', 'Test\Controller\Index@index');

Route::middleware('Test\Middleware\Index::class', function () {
    Route::post('index', 'Test\Controller\Index@index');
});

Route::prefix('test', function () {
    Route::post('index', 'Test\Controller\Index@index');
});

?>
```