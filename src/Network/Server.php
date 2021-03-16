<?php

declare(strict_types = 1);

namespace Rush\Network;

use Closure;
use Rush\Log\LoggerAwareTrait;

const OS_TYPE_LINUX = 1;
const OS_TYPE_WINDOWS = 2;

/**
 * Class Server
 * @package Rush\Network
 */
class Server
{
    use LoggerAwareTrait;

    /**
     * Operate System Type
     * @var int
     */
    protected static int $os = OS_TYPE_LINUX;

    /**
     * Main Process Id
     * @var int
     */
    protected static int $main_pid = 0;

    /**
     * Worker Instance
     * @var static[]
     */
    protected static array $workers = [];

    /**
     * Process Id Map
     * @var array
     */
    protected static array $pid_map = [];

    /**
     * Server Id
     * @var int
     */
    protected int $id = 0;

    /**
     * Server Title
     * @var string
     */
    protected string $title = '';

    /**
     * Number Of Server Process
     * @var int
     */
    protected int $num = 1;

    /**
     * Transport Protocol
     * @var string
     */
    protected string $transport = '';

    /**
     * Application Protocol
     * @var string
     */
    protected string $protocol = '';

    /**
     * Listening Address
     * @var string
     */
    protected string $ip = '';

    /**
     * Listening Port
     * @var int
     */
    protected int $port = 0;

    /**
     * Socket Context
     * @var resource|null
     */
    protected $context = null;

    /**
     * Listening socket
     * @var mixed
     */
    protected mixed $socket = null;

    /**
     * Emitted When A Socket Connection Is Successfully Established
     * @var Closure|null
     */
    protected Closure|null $on_connect = null;

    /**
     * Emitted When Data Is Received
     * @var Closure|null
     */
    protected Closure|null $on_message = null;

    /**
     * Emitted When A Socket Connection Sends A FIN packet
     * @var Closure|null
     */
    protected Closure|null $on_close = null;

    /**
     * Emitted When An Error Occurs With connection
     * @var Closure|null
     */
    protected Closure|null $on_error = null;

    /**
     * Server constructor
     * @param string $ip Listening address.
     * @param int $port Listening port.
     * @param string $transport Transport protocol.
     * @param array $context_options Socket context.
     */
    public function __construct(string $ip, int $port, string $transport = 'tcp', array $context_options = [])
    {
        $this->id = spl_object_id($this);
        static::$workers[$this->id] = $this;

        $this->ip = $ip;
        $this->port = $port;
        $this->transport = $transport;
        $this->context = stream_context_create($context_options);
    }

    /**
     * Start to listen
     * @return void
     * @throws NetworkException
     */
    public function listen(): void
    {
        if (static::$os === OS_TYPE_LINUX) {
            stream_context_set_option($this->context, 'socket', 'so_reuseport', 1);
        }

        $address = sprintf("%s://%s:%d", $this->transport, $this->ip, $this->port);
        $flags = STREAM_SERVER_BIND | STREAM_SERVER_LISTEN;
        $this->socket = stream_socket_server($address, $errno, $msg, $flags, $this->context);
        if (is_resource($this->socket) === false) {
            throw new NetworkException($msg);
        }

        stream_set_blocking($this->socket, false);

        $this->resumeAccept();
    }

    /**
     * Resume to accept socket connection
     * @return void
     * @throws NetworkException
     */
    public function resumeAccept(): void
    {
        Reactor::getInstance()->add($this->socket, Reactor::READ, Closure::fromCallable([$this, 'acceptTcp']));
    }

    /**
     * Pause to accept socket connection
     * @return void
     * @throws NetworkException
     */
    public function pauseAccept(): void
    {
        Reactor::getInstance()->del($this->socket, Reactor::READ);
    }

    /**
     * Establish a socket connection
     * @param mixed $socket
     * @return void
     * @throws NetworkException
     */
    public function acceptTcp(mixed $socket): void
    {
        $new_socket = stream_socket_accept($socket, 0);
        stream_set_blocking($new_socket, false);

        (new Tcp($new_socket, $this->protocol))
            ->withAction('connect', $this->on_connect)
            ->withAction('message', $this->on_message)
            ->withAction('close', $this->on_close)
            ->withAction('error', $this->on_error)
            ->withLogger($this->logger)
            ->establish();
    }

    /**
     * Start all server
     * @return void
     * @throws NetworkException
     */
    public static function start(): void
    {
        static::init();
        static::work();
        static::installSignal();
        static::watch();
    }

    /**
     * Init server environment
     * @return void
     */
    protected static function init(): void
    {
        cli_set_process_title('rush');
        static::$main_pid = posix_getpid();

        $operator = php_uname('s');
        static::$os = str_contains($operator, 'Windows') ? OS_TYPE_WINDOWS : OS_TYPE_LINUX;
    }

    /**
     * Fork worker and listen
     * @return void
     * @throws NetworkException
     */
    protected static function work(): void
    {
        foreach (static::$workers as $id => $worker) {
            for ($i = 0; $i < $worker->getNum(); $i++) {
                $pid = pcntl_fork();
                if ($pid > 0) {
                    static::$pid_map[$pid] = $id;
                } elseif ($pid == 0) {
                    $title = $worker->getTitle();
                    cli_set_process_title($title);
                    static::installSignal();

                    $worker->listen();
                    Reactor::getInstance()->loop();
                    exit(-1);
                } else {
                    exit(-1);
                }
            }
        }
    }

    /**
     * Register signal
     * @return void
     * @throws NetworkException
     */
    protected static function installSignal(): void
    {
        if (static::$os !== OS_TYPE_LINUX) {
            return;
        }

        $signalHandler = Closure::fromCallable([Server::class, 'signalHandler']);

        if (posix_getpid() === static::$main_pid) {
            pcntl_signal(SIGUSR1, $signalHandler, false);
        } else {
            Reactor::getInstance()->add(SIGUSR1, Reactor::SIGNAL, $signalHandler);
        }
     }

    /**
     * Register signal and wait
     * @return void
     */
    protected static function watch(): void
    {
        while (count(static::$pid_map) > 0) {
            pcntl_signal_dispatch();
            $pid = pcntl_wait($status, WUNTRACED);
            if ($pid > 0) {
                unset(static::$pid_map[$pid]);
            }
        }
    }

    /**
     * Signal handler
     * @param int $signal
     * @return void
     * @throws NetworkException
     */
    protected static function signalHandler(int $signal): void
    {
        switch ($signal) {
            case SIGUSR1:
                static::stop();
                break;
        }
    }

    /**
     * Stop all server
     * @return void
     * @throws NetworkException
     */
    public static function stop(): void
    {
        if (posix_getpid() === static::$main_pid) {
            foreach (static::$pid_map as $pid => $id) {
                posix_kill($pid, SIGUSR1);
            }
        } else {
            foreach (static::$workers as $id => $worker) {
                $worker->pauseAccept();
            }

            foreach (Tcp::getConnections() as $conn) {
                $conn->close();
            }

            Reactor::getInstance()->destroy(2);
            exit(0);
        }
    }

    /**
     * Get current server id
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set current server title
     * @param string $name Title.
     * @return void
     */
    public function setTitle(string $name): void
    {
        $this->title = $name;
    }

    /**
     * Get current server title
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title ?: sprintf("rush-%d", $this->id);
    }

    /**
     * Set number of current server process
     * @param int $num Number.
     * @return void
     */
    public function setNum(int $num): void
    {
        $this->num = $num;
    }

    /**
     * Get number of current server process
     * @return int
     */
    public function getNum(): int
    {
        return $this->num;
    }

    /**
     * Set application protocol for current server
     * @param string $protocol Application protocol.
     * @throws NetworkException
     * @return void
     */
    public function setProtocol(string $protocol): void
    {
        if (class_exists($protocol) === false) {
            throw new NetworkException("Protocol is not exist");
        }

        $this->protocol = $protocol;
    }

    /**
     * Register a callback function
     * @param string $action Connection action.
     * @param Closure $callBack Function that is emitted when action is triggered.
     * @return void
     */
    public function on(string $action, Closure $callBack): void
    {
        $action = match ($action) {
            'connect' => 'on_connect',
            'message' => 'on_message',
            'close' => 'on_close',
            'error' => 'on_error'
        };

        $this->$action = $callBack;
    }
}
