<?php

declare(strict_types = 1);

namespace Rush\Log;

/**
 * Class FileHandler
 * @package Rush\Log
 */
class FileLogger implements LoggerInterface
{
    /**
     * Logger Trait
     */
    use LoggerTrait;

    /**
     * Normal Mode
     * @var int
     */
    public const MODE_NORMAL = 0;

    /**
     * Debug Mode
     * @var int
     */
    public const MODE_DEBUG = 1;

    /**
     * Ignore Mode
     * @var int
     */
    public const MODE_IGNORE = 2;

    /**
     * Direction Path
     * @var string
     */
    protected string $path = '/tmp';

    /**
     * Max Message Number
     * @var int
     */
    protected int $size = 1;

    /**
     * Datetime Format
     * @var string
     */
    protected string $format = 'Y-m-d H:i:s';

    /**
     * Field Separator
     * @var string
     */
    protected string $separator = '|';

    /**
     * Log Buffer
     * @var array
     */
    protected array $buffer = [];

    /**
     * Record Mode
     * @var int
     */
    protected int $mode = 0;

    /**
     * Set log file path
     * @param string $path Storage directory.
     * @return static
     * @throws LogException
     */
    public function withPath(string $path): static
    {
        if (is_dir($path) === false && mkdir($path, 0777, true) === false) {
            throw new LogException("Fail to create directory({$path})");
        }

        $this->path = $path;
        
        return $this;
    }

    /**
     * Set log buffer size
     * @param int $size Max message size.
     * @return static
     */
    public function withSize(int $size): static
    {
        $this->size = $size;
        
        return $this;
    }

    /**
     * Set log message date format
     * @param string $format Date format, like function date().
     * @return static
     */
    public function withFormat(string $format): static
    {
        $this->format = $format;
        
        return $this;
    }

    /**
     * Set log message field separator
     * @param string $separator Field separator.
     * @return static
     */
    public function withSeparator(string $separator): static
    {
        $this->separator = $separator;
        
        return $this;
    }

    /**
     * Set record mode
     * @param int $model Record mode.
     * @return static
     * @throws LogException
     */
    public function withMode(int $model): static
    {
        if (in_array($model, [static::MODE_NORMAL, static::MODE_DEBUG, static::MODE_IGNORE]) === false) {
            throw new LogException("Mode is not supported");
        }

        $this->mode = $model;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function log(mixed $level, string $message, array $context = array()): void
    {
        $message = $this->parseContext($message, $context);

        $content = $this->prepare($level, $message);

        switch ($this->mode) {
            case static::MODE_NORMAL:
                $this->buffer[] = $content;
                if ($this->checkSize() === false) {
                    $this->flushBuffer();
                }
                break;
            case static::MODE_DEBUG:
                echo "{$content}\n";
                break;
            case static::MODE_IGNORE:
                break;
        }
    }

    /**
     * Parse placeholder in context
     * @param string $message
     * @param array $context
     * @return string
     */
    protected function parseContext(string $message, array $context): string
    {
        return str_replace(
            array_map(fn ($key) => "{{$key}}", array_keys($context)),
            array_values($context),
            $message
        );
    }

    /**
     * @param mixed $level
     * @param string $message
     * @return string
     */
    protected function prepare(mixed $level, string $message): string
    {
        return implode($this->separator, [
            date($this->format) , $level, $message
        ]);
    }

    /**
     * Check buffer size
     * @return bool
     */
    protected function checkSize(): bool
    {
        return $this->size > count($this->buffer);
    }

    /**
     * Get log buffer
     * @return array
     */
    public function getBuffer(): array
    {
        return $this->buffer;
    }

    /**
     * Flush buffer to file
     * @return void
     */
    public function flushBuffer(): void
    {
        if (empty($this->buffer) === true) {
            return;
        }

        $filename = $this->getFilename();

        $content = array_reduce($this->buffer, fn($front, $next) => $front . $next . PHP_EOL);

        file_put_contents($filename, $content, FILE_APPEND);

        $this->buffer = [];
    }

    /**
     * Generate log file name
     * @return string
     */
    protected function getFilename(): string
    {
        return "{$this->path}/" . date('Ymd') . '.log';
    }

    /**
     * FileLogger destructor
     */
    public function __destruct()
    {
        $this->flushBuffer();
    }
}
