<?php

declare(strict_types = 1);

namespace Rush\Database\Collection;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;

/**
 * Class CookieElement
 * @package Sixser\Database\Collection
 */
class Record implements ArrayAccess, IteratorAggregate
{
    /**
     * CookieElement constructor
     * @param array $record A record of result set.
     */
    public function __construct(array $record)
    {
        foreach ($record as $key => $val) {
            $this->$key = $val;
        }
    }

    /**
     * Determines whether an field is exist
     * @param string $key Field name.
     * @return bool
     */
    public function exist(string $key): bool
    {
        return isset($this->$key) === true;
    }

    /**
     * Get a field value
     * @param string $key Field name.
     * @return false|int|string
     */
    public function get(string $key): false|int|string
    {
        return $this->$key ?? false;
    }

    /**
     * Set a field value
     * @param string $key Field name.
     * @param int|string $val Field value.
     * @return void
     */
    public function set(string $key, int|string $val): void
    {
        $this->$key = $val;
    }

    /**
     * Delete a field
     * @param string $key Field name.
     * @return void
     */
    public function del(string $key): void
    {
        unset($this->$key);
    }

    /**
     * Convert object to array
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return $this->exist($offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset): false|int|string
    {
        return $this->get($offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        $this->del($offset);
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this);
    }
}
