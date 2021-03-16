<?php

declare(strict_types = 1);

namespace Rush\Http\Session;

use Redis;

/**
 * Class RedisSession
 * @package Rush\Http\Session
 */
class RedisSession extends SessionAbstract
{
    /**
     * Session Storage Instance
     * @var Redis
     */
    protected Redis $instance;

    /**
     * Init session
     * @param string $host Redis server host.
     * @param int $port Redis server host.
     * @param string $password Redis server password.
     * @param int $index Redis database index.
     */
    public function __construct(string $host, int $port = 6379, string $password = '', int $index = 0)
    {
        $this->instance = new Redis();
        $this->instance->connect($host, $port);
        $this->instance->auth($password);
        $this->instance->select($index);
        $this->instance->setOption(Redis::OPT_PREFIX, static::$prefix);
    }

    /**
     * @inheritDoc
     */
    public static function getKey(string $id): string
    {
        return $id;
    }

    /**
     * @inheritDoc
     */
    public function read(string $id): void
    {
        $key = static::getKey($id);
        if (empty($id) === true ||
            is_string($content = $this->instance->get($key)) === false
        ) {
            $this->data = [];
            $this->id = static::generateIdentity();
            return;
        }

        $this->data = (array) unserialize($content);
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function write(): void
    {
        if (empty($this->id) === true) {
            $this->id = static::generateIdentity();
        }

        $this->instance->setex(
            static::getKey($this->id),
            static::$expire,
            serialize($this->data)
        );
    }

    /**
     * @inheritDoc
     */
    public function destroy(): void
    {
        $key = static::getKey($this->id);
        $this->instance->del($key);

        $this->id = static::generateIdentity();
        $this->data = [];
    }

    /**
     * Close session
     */
    public function __destruct()
    {
        $this->instance->close();
    }
}
