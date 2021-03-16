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
        if (isset(static::$tree) === false) {
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
        if (is_string($origin) === true) {
            $name = pathinfo($origin, PATHINFO_FILENAME);
            $origin = $this->parseFile($origin);
        }

        if (empty($name) === true) {
            throw new ConfigException('Config item name cannot be empty');
        }

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
        if (is_file($filename) === false) {
            throw new ConfigException('$filename must be the full name of the file');
        }

        if (file_exists($filename) === false) {
            throw new ConfigException("{$filename} is not exist");
        }

        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (in_array($ext, static::$file_type) === false) {
            throw new ConfigException('For file type, .php .ini .yam and .yaml are supported');
        }

        $content = match ($ext) {
            'php' => include $filename,
            'ini' => parse_ini_file($filename, true),
            'yam', 'yaml' => yaml_parse_file($filename)
        };

        if (is_array($content) === false) {
            throw new ConfigException("Fail to parse file({$filename})");
        }

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

        $list = explode('.', $name);

        do {
            $name = array_shift($list);
            if ($item->exist($name) === false) {
                return false;
            }

            if (count($list) === 0) {
                return true;
            }

            $item = $item->get($name);
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

        $list = explode('.', $name);

        do {
            $name = array_shift($list);

            $item = $item->get($name);

            if (count($list) === 0) {
                return $item;
            }
        } while (true);
    }
}
