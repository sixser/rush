<?php

declare(strict_types = 1);

namespace Rush\Log;

/**
 * Class LoggerFactory   
 * @package Rush\Log
 */
Class LoggerFactory
{
    /**
     * Create file logger
     * @param string $path Direction path.
     * @param integer $size Buffer size.
     * @param string $format Date format.
     * @param string $separator Message field separator.
     * @return FileLogger
     * @throws LogException
     */
    public static function createFileLogger(string $path, int $size, string $format = 'Y-m-d H:i:s', string $separator = '|'): FileLogger
    {
        return (new FileLogger())
            ->withPath($path)
            ->withSize($size)
            ->withFormat($format)
            ->withSeparator($separator);
    }
}
