<?php

declare(strict_types = 1);

namespace Rush\Network;

use Closure;

/**
 * Class ConnectionAbstract
 * @package Rush\Network
 */
abstract class  ConnectionAbstract
{
    /**
     * Connection Socket
     * @var mixed
     */
    protected mixed $socket = null;

    /**
     * Local Address
     * @var string
     */
    protected string $local_address = '';

    /**
     * Remote Address
     * @var string
     */
    protected string $remote_address = '';

    /**
     * Emitted When A Socket Connection Sends A ACK packet
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
     * Get local address
     * @return string
     */
    function getLocalAddress(): string
    {
        return $this->local_address;
    }

    /**
     * Get local IP
     * @return string
     */
    public function getLocalIp(): string
    {
        return (string) strchr($this->local_address, ':', true);
    }

    /**
     * Get local port
     * @return int
     */
    public function getLocalPort(): int
    {
        return (int) substr((string) strrchr($this->local_address, ':'), 1);
    }

    /**
     * Get remote address
     * @return string
     */
    public function getRemoteAddress(): string
    {
        return $this->remote_address;
    }

    /**
     * Get remote IP
     * @return string
     */
    public function getRemoteIp(): string
    {
        return (string) strchr($this->remote_address, ':', true);
    }

    /**
     * Get remote port
     * @return int
     */
    public function getRemotePort(): int
    {
        return (int) substr((string) strrchr($this->remote_address, ':'), 1);
    }
}
