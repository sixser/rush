<?php

declare(strict_types = 1);

namespace Rush\Config;

use ArrayIterator;
use IteratorAggregate;

/**
 * Class ConfigItem
 * @package Rush\Config
 */
class ConfigItem implements IteratorAggregate
{
    /**
     * Item Data
     * @var bool|int|string|null
     */
    protected bool|int|string|null $data;

    /**
     * Subset
     * @var ConfigItem[]
     */
    protected array $subset = [];

    /**
     * ConfigItem constructor
     * @param bool|int|string|null $data
     */
    public function __construct(bool|int|string|null $data = null)
    {
        $this->data = $data;
    }

    /**
     * Convert current data to bool
     * @return bool
     */
    public function bool(): bool
    {
        return (bool) $this->data;
    }

    /**
     * Convert current data to int
     * @return int
     */
    public function int(): int
    {
        return (int) $this->data;
    }

    /**
     * Convert current data to string
     * @return string
     */
    public function string(): string
    {
        return (string) $this->data;
    }

    /**
     * Determines whether an option is present in the cache
     * @param string $name Name of sub-item.
     * @return bool
     */
    public function exist(string $name): bool
    {
        return isset($this->subset[$name]);
    }

    /**
     * Obtain configuration sub-item from the item
     * @param string $name Name of sub-item.
     * @return ConfigItem
     * @throws ConfigException
     */
    public function get(string $name): ConfigItem
    {
        ! isset($this->subset[$name]) &&
        throw new ConfigException("Failed to obtain config, $name is not exist.");

        return $this->subset[$name];
    }

    /**
     * Set subset of the item
     * @param int|string $name Name of sub-item.
     * @param ConfigItem $item The sub-item.
     * @return void
     */
    public function set(int|string $name, ConfigItem $item): void
    {
        $this->subset[$name] = $item;
    }

    /**
     * Get current subset
     * @return ConfigItem[]
     */
    public function getSubset(): array
    {
        return $this->subset;
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->subset);
    }
}
