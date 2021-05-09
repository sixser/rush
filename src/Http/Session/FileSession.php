<?php

declare(strict_types = 1);

namespace Rush\Http\Session;

/**
 * Class FileSession
 * @package Rush\Http\Session
 */
class FileSession extends SessionAbstract
{
    /**
     * Session File Path
     * @var string
     */
    protected static string $path = '/tmp';

    /**
     * @inheritDoc
     */
    public static function getKey(string $id): string
    {
        return sprintf("%s/%s_%s", static::$path, static::$prefix, $id);
    }

    /**
     * @inheritDoc
     */
    public function read(string $id): void
    {
        $filename = static::getKey($id);
        if (
            empty($id) ||
            ! file_exists($filename) ||
            false === ($content = file_get_contents($filename))
        ) {
            $this->data = [];
            $this->id = static::generateIdentity();
            return;
        }

        [$data, $validTime] = unserialize($content);
        if ($validTime < time()) {
            unlink($filename);
            $this->id = static::generateIdentity();
            $this->data = [];
            return;
        }

        $this->data = $data;
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function write(): void
    {
        if (empty($this->id)) {
            $this->id = static::generateIdentity();
        }

        $filename = static::getKey($this->id);
        $mkTime = file_exists($filename) === true ? (filectime($filename) ?: time()) : time();
        $content = serialize([$this->data, $mkTime + static::$expire]);

        file_put_contents($filename, $content);
    }

    /**
     * @inheritDoc
     */
    public function destroy(): void
    {
        $filename = static::getKey($this->id);
        is_file($filename) && file_exists($filename) && unlink($filename);

        $this->id = static::generateIdentity();
        $this->data = [];
    }
}
