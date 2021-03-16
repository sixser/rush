<?php

declare(strict_types = 1);

namespace Rush\Database\Collection;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Class Collector
 * @package Sixser\Database\Collection
 */
class Collector implements Countable, IteratorAggregate
{
    /**
     * Result Set
     * @var Record[]
     */
    protected array $records;

    /**
     * Current Point Position
     * @var int
     */
    private int $ptr = 0;

    /**
     * Collector constructor
     * @param array $records Result set records.
     */
    public function __construct(array $records)
    {
        $this->records = array_map(fn ($record) => new Record($record), $records);
    }

    /**
     * Fetch one record
     * @return false|Record
     */
    public function fetchOne(): false|Record
    {
        return $this->records[$this->ptr++] ?? false;
    }

    /**
     * Fetch all records
     * @return array
     */
    public function fetchAll(): array
    {
        return $this->records;
    }

    /**
     * Rewind the records point
     */
    public function reset(): void
    {
        $this->ptr = 0;
    }

    /**
     * Convert object to array
     * @return array
     */
    public function toArray(): array
    {
        return array_map(fn ($record) => $record->toArray(), $this->records);
    }

    /**
     * Count records
     * @return int
     */
    public function count(): int
    {
        return count($this->records);
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->records);
    }
}
