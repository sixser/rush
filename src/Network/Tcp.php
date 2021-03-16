<?php

declare(strict_types = 1);

namespace Rush\Network;

use Closure;
use Rush\Log\LoggerAwareTrait;
use Throwable;

class Tcp extends ConnectionAbstract
{
    use LoggerAwareTrait;

    /**
     * Run Application Wait Data
     * @var int
     */
    public const RUN_WAIT = 1;

    /**
     * Run Application Ok
     * @var int
     */
    public const RUN_OK = 0;

    /**
     * Run Application Error
     * @var int
     */
    public const RUN_ERROR = -1;

    /**
     * Status Initial
     * @var int
     */
    public const STATUS_INITIAL = 0;

    /**
     * Status Connecting
     * @var int
     */
    public const STATUS_CONNECTING = 1;

    /**
     * Status Connection Established
     * @var int
     */
    public const STATUS_ESTABLISHED = 2;

    /**
     * Status Closing
     * @var int
     */
    public const STATUS_CLOSING = 4;

    /**
     * Status Closed.
     * @var int
     */
    public const STATUS_CLOSED = 8;

    /**
     * All connections
     * @var static[]
     */
    protected static array $connections = [];

    /**
     * ID Recorder
     * @var int
     */
    protected static int $recorder = 0;

    /**
     * Buffer Size
     * @var int
     */
    protected static int $buffer_size = 65535;

    /**
     * Connection ID
     * @var int
     */
    protected int $id = 0;

    /**
     * Connection Status
     * @var int
     */
    protected int $status = 0;

    /**
     * Status Of Receiving Data
     * @var bool
     */
    protected bool $pause = false;

    /**
     * Input Buffer
     * @var string
     */
    protected string $in_buffer = '';

    /**
     * Output Buffer
     * @var string
     */
    protected string $out_buffer = '';

    /**
     * Tcp constructor
     * @param mixed $socket Socket of connection.
     */
    public function __construct(mixed $socket)
    {
        $this->status = static::STATUS_INITIAL;

        $this->id = static::$recorder++;
        static::$connections[$this->id] = $this;

        $this->socket = $socket;
        $this->remote_address = stream_socket_get_name($this->socket, true);
        $this->local_address = stream_socket_get_name($this->socket, false);

        stream_set_blocking($this->socket, false);
        stream_set_read_buffer($this->socket, 0);
    }

    /**
     * Set connection action
     * @param string $action Connection action(message, close and error).
     * @param Closure|null $callBack Function that is emitted when action is triggered.
     * @return static
     */
    public function withAction(string $action, Closure|null $callBack): static
    {
        $action = match ($action) {
            'connect' => 'on_connect',
            'message' => 'on_message',
            'close' => 'on_close',
            'error' => 'on_error'
        };

        $this->$action = $callBack;

        return $this;
    }

    /**
     * Start to establish connection
     * @return void
     * @throws NetworkException
     */
    public function establish(): void
    {
        $this->status = static::STATUS_CONNECTING;

        $res = $this->trigger('connect');
        if ($res === static::RUN_ERROR) $this->close();

        Reactor::getInstance()->add($this->socket, Reactor::READ, Closure::fromCallable([$this, 'read']));

        $this->status = static::STATUS_ESTABLISHED;
    }

    /**
     * Pauses the reading of data
     * @return void
     * @throws NetworkException
     */
    public function pause(): void
    {
        if ($this->pause === true) return;

        Reactor::getInstance()->del($this->socket, Reactor::WRITE);
        $this->pause = true;
    }

    /**
     * Resume reading after a call to pauseRecv.
     * @return void
     * @throws NetworkException
     */
    public function resume(): void
    {
        if ($this->pause === false) return;

        Reactor::getInstance()->add($this->socket, Reactor::READ, Closure::fromCallable([$this, 'read']));
        $this->pause = false;
    }

    /**
     * Base read handler
     * @param mixed $socket Socket of connection.
     * @return void
     * @throws NetworkException
     */
    public function read(mixed $socket): void
    {
        $buffer = fread($socket, static::$buffer_size);
        if ($buffer === false) {
            if (is_resource($socket) === false || feof($socket) === true) {
                $this->close();
            }

            return;
        }

        $this->in_buffer .= $buffer;

        $res = $this->trigger('message', $this->in_buffer);
        if ($res === static::RUN_WAIT) return;
        if ($res === static::RUN_ERROR) $this->close();

        $this->in_buffer = '';
    }

    /**
     * Base write handler
     * @return void
     * @throws NetworkException
     */
    public function write(): void
    {
        $length = fwrite($this->socket, $this->out_buffer, 8192);
        if ($length === false) {
            $this->close();
            return;
        }

        $this->out_buffer = (string) substr($this->out_buffer, $length);
        if ($this->out_buffer === '') {
            Reactor::getInstance()->del($this->socket, Reactor::WRITE);
        }
    }

    /**
     * Send data
     * @param string $data The data to be send
     * @return void
     * @throws NetworkException
     */
    public function send(string $data): void
    {
        if ($this->status === static::STATUS_CLOSING || $this->status === static::STATUS_CLOSED) {
            return;
        }

        // Write event is added, add to the end
        if ($this->out_buffer !== '') {
            $this->out_buffer .= $data;
            return;
        }

        // Attempt send directly
        $length = fwrite($this->socket, $data);
        if ($length === false) {
            if (is_resource($this->socket) === false || feof($this->socket) === true) {
                $this->trigger('error');
                $this->close();
            }

            return;
        }

        $this->out_buffer = (string) substr($data, (int) $length);
        if ($this->out_buffer !== '') {
            Reactor::getInstance()->add($this->socket, Reactor::WRITE, Closure::fromCallable([$this, 'write']));
        }
    }

    /**
     * Get input buffer
     * @return string
     */
    public function receive(): string
    {
        return $this->in_buffer;
    }

    /**
     * Close connection with data
     * @param string $data The data to be send before close.
     * @return void
     * @throws NetworkException
     */
    public function close(string $data = ''): void
    {
        if ($this->status === self::STATUS_CONNECTING) {
            $this->destroy();
            return;
        }

        if ($this->status === self::STATUS_CLOSING || $this->status === self::STATUS_CLOSED) {
            return;
        }

        if ($data !== '') $this->send($data);

        $this->status = self::STATUS_CLOSING;

        if ($this->out_buffer === '') {
            $this->destroy();
        } else {
            $this->pause();
        }
    }

    /**
     * Destroy current connection
     * @return void
     * @throws NetworkException
     */
    public function destroy(): void
    {
        if ($this->status === self::STATUS_CLOSED) {
            return;
        }

        Reactor::getInstance()->del($this->socket, Reactor::READ);
        Reactor::getInstance()->del($this->socket, Reactor::WRITE);

        fclose($this->socket);

        $this->status = self::STATUS_CLOSED;

        $this->trigger('close');

        unset(static::$connections[$this->id]);
    }

    /**
     * Trigger the action
     * @param string $action Connection action.
     * @param mixed $param CallBack parameters.
     * @return mixed
     */
    protected function trigger(string $action, mixed ...$param): mixed
    {
        try {
            $action = match ($action) {
                'connect' => 'on_connect',
                'message' => 'on_message',
                'close' => 'on_close',
                'error' => 'on_error'
            };

            if (is_callable($this->$action) === false) {
                return static::RUN_OK;
            }
            
            return call_user_func($this->$action, $this, ...$param);
        } catch (Throwable $t) {
            $this->logger->error($t->getMessage());
            return static::RUN_ERROR;
        }
    }

    /**
     * Set buffer size
     * @param int $size Size of buffer.
     * @return void
     * @throws NetworkException
     */
    public static function setBufferSize(int $size): void
    {
        if ($size <= 0) {
            throw new NetworkException("Read buffer size must larger than 0");
        }

        static::$buffer_size = $size;
    }

    /**
     * @return static[]
     */
    public static function getConnections(): array
    {
        return static::$connections;
    }

    /**
     * Get connection id
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
