# Rush PHP
Rush PHP - Building For A Light-Weight And High-Performance PHP Server Framework

---
## The Regulation Of Development
1. Based on PSR-0, PSR-1, PSR-2
2. Customer function, class property must be declared in all lower case with underscore separators.
3. Class Name must be declared in lower Camel-Case.
4. Class method must be declared in upper Camel-Case.

---

## Install

#### How to install rush ?

```sh
composer require sixser/rush
```

---

## Container

#### How to new a object ?

```php
$class = Container::getInstance()->make(Class::class);
```

#### How to make a pre-new object ?

```php
Container::getInstance()->inject('Class', function () {
    return new Class();
});
```

#### How to get a pre-new object ?

```php
$class = Container::getInstance()->get('Class');
```

---

## Log

#### How to make a logger ?

```php
$logger = LoggerFactory::createFileLogger('/logs');
$logger->info('This is a info log');
```

---

## Server

#### How to launch a HTTP server ?

```php
$server = (new Server('0.0.0.0', 80))->withLogger(
    LoggerFactory::createFileLogger(__DIR__.'/logs')
);
$server->on('message', function (Tcp $conn, string $raw) {
    // Accept the message.
    echo $raw;
    
    // Send the message.
    $conn->close("Hello Rush!");
});

Server::start();
```
#### How to launch a HTTP server with an application protocol

```php
class ApplicationProtocol implements ProtocolInterface {}

$server = (new Server('0.0.0.0', 80))->withLogger(
    LoggerFactory::createFileLogger(__DIR__.'/logs')
);
$server->setProtocol(ApplicationProtocol::class);
$server->on('message', function (Tcp $conn, string $raw) {
    // Accept the message.
    echo $raw;
    
    // Send the message.
    $conn->close("Hello Rush!");
});

Server::start();
```

---

## HTTP

#### How to Register a route rule ?

```php
$router = new Router();

$router->get('index', 'Devel\Controller\Index@index');
$router->post('index', 'Devel\Controller\Index@index');
```

#### How to add a route group ?

```php
$router = new Router();

$router->prefix('devel', function () use ($router) {
    $router->get('index', 'Devel\Controller\Index@index');
    $router->post('index', 'Devel\Controller\Index@index');
});
```

#### How to add a route middleware ?

```php
$router = new Router();

$router->middleware(\Rush\Http\Middleware\FrameGuard::class, function () use ($router) {
    $router->get('index', 'Devel\Controller\Index@index');
    $router->post('index', 'Devel\Controller\Index@index');
});
```

#### How to capture a request and deal with the router and middlewares ?

```php
$router      = Container::getInstance()->get('Route');
$logger      = Container::getInstance()->get('FileLogger');
$middlewares = [
    \Rush\Http\Middleware\FrameGuard::class,
//    \Rush\Http\Middleware\CookieLauncher::class,
//    \Rush\Http\Middleware\SessionLauncher::class
];

$server = (new Server('0.0.0.0', 80))->withLogger($logger);
$server->setProtocol(\Rush\Http\Protocol::class);
$server->on('message', function (Tcp $conn, string $raw) use ($logger, $middlewares) {
    $request  = Protocol::decode($raw);

    // Accept http message and make response.
    $response    = (new Handler())->withRouter($router)->withMiddlewares(...$middlewares)->handle($request);

    $conn->close(Protocol::encode($response));
});

Server::start();
```
#### How to set a cookie ?

```php
$cookie = (new CookieMaker())->make('name', 'value');
```

#### How to use session variables ?

```php
$session = Container::getInstance()->get(FileSession::class);

$session->set('name', 'value');

var_dump($session->get('name'));

$session->del('name');

var_dump($session->exist('name'));
```

---

## Database

#### How to make a database connection ?

```php
$logger = LoggerFactory::createFileLogger(__DIR__.'/logs');
$dsn = DsnFactory::createMysqlTcpDsn('127.0.0.1', 3306, 'devel');
$connection = (new Connection())->withDsn($dsn)->withUser('username', 'password')->withLogger($logger);

$connection->begin();
try {
    $affectRows = $connection->execute("UPDATE table SET field=value WHERE id=?", [1]);
    $affectRows = $connection->execute("UPDATE table SET field=value WHERE id=?", [2]);
} catch (Exception) {
    $connection->rollback();
}

$connection->commit();

$result = $connection->query("SELECT * FROM table WHERE id = :id", ['id' => 1]);
```

---

## Configuration

#### How to load a configuration file ?

```php
$config = new Repository();
$config->load(__DIR__ . '/config/app1.php');

var_dump($config->exist('app1'));
var_dump($config->get('app1.name')->toString());
var_dump($config->get('app1')->get('name')->count());
var_dump($config->get('app1')->toArray());
```

#### How to load a configuration array ?

```php
$app = [
    'level-1-1' => 'level-1-1-val',
    'level-1-2' => [
        'level-2-1' => 'level-2-1-val',
        'level-2-2' => 'level-2-2-val'
    ]
];

$config = new Repository();
$config->load($app, 'app');

var_dump($config->exist('app'));
var_dump($config->get('app.level-1-1')->toString());
var_dump($config->get('app')->get('level-1-2')->count());
var_dump($config->get('app')->toArray());
foreach ($config->get('app') as $child) {
    if (count($child) == 0) {
        var_dump($child->toString());
        continue;
    }

    foreach ($child as $item) {
        if (count($item) == 0) {
            var_dump($item->toString());
            continue;
        }
    }
}
```





