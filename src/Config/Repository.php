<?php

declare(strict_types = 1);

namespace Rush\Config;

/**
 * Class Repository
 * @package Rush\Config
 */
class Repository
{
    /**
     * Supported Type Of Config File
     * @var array|string[]
     */
    protected static array $file_type = ['php', 'ini', 'yam', 'yaml'];

    /**
     * Config Set
     * @var ConfigItem
     */
    protected static ConfigItem $tree;

    /**
     * Manager constructor
     */
    public function __construct()
    {
        if (! isset(static::$tree)) {
            static::$tree = new ConfigItem();
        }
    }

    /**
     * Load the config
     * @param array|string $origin Array of config set or name of config file.
     * @param string $name
     * @return void
     * @throws ConfigException
     */
    public function load(string|array $origin, string $name = ''): void
    {
        if (is_string($origin)) {
            $name = pathinfo($origin, PATHINFO_FILENAME);
            $origin = $this->parseFile($origin);
        }

        empty($name) &&
        throw new ConfigException('Failed to load config, sub-set name cannot be empty.');

        static::$tree->set($name, static::build($origin));
    }

    /**
     * Parse config file
     * @param string $filename config file full name.
     * @return array
     * @throws ConfigException
     */
    protected function parseFile(string $filename): array
    {
        ! (is_file($filename) || file_exists($filename)) &&
        throw new ConfigException("Failed to parse config, $filename is not a valid file.");

        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        ! in_array($ext, static::$file_type) &&
        throw new ConfigException("Failed to parse config, $ext is not a supported format.");

        $content = match ($ext) {
            'php' => include $filename,
            'ini' => parse_ini_file($filename, true),
            'yam', 'yaml' => yaml_parse_file($filename)
        };

        ! is_array($content) &&
        throw new ConfigException("Failed to parse config, $filename content is not a valid format.");

        return $content;
    }

    /**
     * Build config tree
     * @param array $content Raw config set.
     * @return ConfigItem
     */
    protected static function build(array $content): ConfigItem
    {
        $item = new ConfigItem();
        foreach ($content as $name => $value) {
            if (is_array($value)) {
                $item->set($name, static::build($value));
            } else {
                $item->set($name, (new ConfigItem($value)));
            }
        }

        return $item;
    }

    /**
     * Determines whether the config option is exist
     * @param string $name Name of config option.
     * @return bool
     * @throws ConfigException
     */
    public function exist(string $name): bool
    {
        $item = static::$tree;

        do {
            $point = strpos($name, '.');
            if (false === $point) {
                return $item->exist($name);
            }

            $subName = substr($name, 0, $point);

            $item = $item->get($subName);

            $name = substr($name, ++$point);
        } while (true);
    }

    /**
     * Obtain config options from tree
     * @param string $name Name of config option.
     * @return ConfigItem
     * @throws ConfigException
     */
    public function get(string $name): ConfigItem
    {
        $item = static::$tree;

        do {
            $point = strpos($name, '.');
            if (false === $point) {
                return $item->get($name);
            }

            $subName = substr($name, 0, $point);

            $item = $item->get($subName);

            $name = substr($name, ++$point);
        } while (true);
    }
}
