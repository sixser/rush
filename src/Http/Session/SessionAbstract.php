<?php

declare(strict_types = 1);

namespace Rush\Http\Session;

use Rush\Http\HttpException;

/**
 * Class SessionAbstract
 * @package Rush\Http\Session
 */
abstract class SessionAbstract
{
    /**
     * Session Name
     * @var string
     */
    protected static string $name = 'PHPSESSID';

    /**
     * Session Prefix
     * @var string
     */
    protected static string $prefix = 'rush_session';

    /**
     * Session Expiration Time
     * @var int
     */
    protected static int $expire = 1800;

    /**
     * Session ID
     * @var string
     */
    protected string $id = '';

    /**
     * Session Data
     * @var array
     */
    protected array $data = [];

    /**
     * Get session storage path or key
     * @param string $id Session id.
     * @return string
     */
    abstract public static function getKey(string $id): string;

    /**
     * Read session data
     * @param string $id Session ID.
     * @return void
     */
    abstract public function read(string $id): void;

    /**
     * Save session data
     * @return void
     */
    abstract public function write(): void;

    /**
     * Destroy current session
     * @return void
     */
    abstract public function destroy(): void;

    /**
     * Get session name
     * @return string
     */
    public static function getName(): string
    {
        return static::$name;
    }

    /**
     * Set session name
     * @param string $name Session name.
     * @return void
     * @throws HttpException
     */
    public static function setName(string $name): void
    {
        empty($name) &&
        throw new HttpException("Failed to set session name, $name is not a valid value");

        static::$name = $name;
    }

    /**
     * Get session prefix
     * @return string
     */
    public static function getPrefix(): string
    {
        return static::$prefix;
    }

    /**
     * Get session prefix
     * @param string $name Session prefix.
     * @return void
     */
    public static function setPrefix(string $name): void
    {
        static::$prefix = $name;
    }

    /**
     * Get session id
     * @return false|string
     */
    public function getIdentity(): string|false
    {
        return $this->id ?: false;
    }

    /**
     * Create a new session id
     * @return string
     */
    public static function generateIdentity(): string
    {
        return session_create_id();
    }

    /**
     * Check if a key exists
     * @param string $name Name of value.
     * @return bool
     */
    public function exist(string $name): bool
    {
        return isset($this->data[$name]);
    }

    /**
     * Get session value of key
     * @param string $name Name of value.
     * @return mixed
     */
    public function get(string $name): mixed
    {
        return $this->data[$name] ?? false;
    }

    /**
     * Set session key-value
     * @param string $name Name of value.
     * @param mixed $value Value.
     * @return void
     */
    public function set(string $name, mixed $value): void
    {
        $this->data[$name] = $value;
    }

    /**
     * Delete session value of key
     * @param string $name Name of value.
     * @return void
     */
    public function del(string $name): void
    {
        unset($this->data[$name]);
    }
}
