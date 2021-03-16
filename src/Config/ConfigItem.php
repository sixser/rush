<?php

declare(strict_types = 1);

namespace Rush\Config;

/**
 * Class ConfigItem
 * @package Rush\Config
 */
class ConfigItem
{
    /**
     * Current ConfigItem Value
     * @var bool|int|string|null
     */
    protected string|int|bool|null $value;

    /**
     * Child Options
     * @var array
     */
    protected array $options = [];

    /**
     * ConfigItem constructor
     * @param bool|int|string|null $value
     */
    public function __construct(bool|int|string|null $value = null)
    {
        $this->value = $value;
    }

    /**
     * Determines whether an option is present in the cache
     * @param string $name Name of configuration option.
     * @return bool
     */
    public function exist(string $name): bool
    {
        return isset($this->options[$name]) === true;
    }

    /**
     * Obtain configuration options from the item
     * @param string $name Name of configuration option.
     * @return ConfigItem
     * @throws ConfigException
     */
    public function get(string $name): ConfigItem
    {
        if (isset($this->options[$name]) === false) {
            throw new ConfigException('Current options is not exist');
        }

        return $this->options[$name];
    }

    /**
     * Set configuration options on the item
     * @param int|string $name Name of configuration option.
     * @param ConfigItem $item The child item.
     * @return void
     */
    public function set(int|string $name, ConfigItem $item): void
    {
        $this->options[$name] = $item;
    }

    /**
     * Convert current item value to bool
     * @return bool
     * @throws ConfigException
     */
    public function toBool(): bool
    {
        if (is_null($this->value) === true) {
            throw new ConfigException('Current options is not a leaf of the tree');
        }

        return (bool) $this->value;
    }

    /**
     * Convert current item value to int
     * @return int
     * @throws ConfigException
     */
    public function toInt(): int
    {
        if (is_null($this->value) === true) {
            throw new ConfigException('Current options is not a leaf of the tree');
        }

        return (int) $this->value;
    }

    /**
     * Convert current item value to string
     * @return string
     * @throws ConfigException
     */
    public function toString(): string
    {
        if (is_null($this->value) === true) {
            throw new ConfigException('Current options is not a leaf of the tree');
        }

        return (string) $this->value;
    }

    /**
     * Convert child options to array
     * @return array
     */
    public function toArray(): array
    {
        return $this->options;
    }
}
