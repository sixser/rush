<?php

declare(strict_types = 1);

namespace Rush\Network;

/**
 * Interface ProtocolInterface
 * @package Rush\Network
 */
interface ProtocolInterface
{
    /**
     * Detect packets in the input buffer
     * @param string $input The data that the input buffer accepts.
     * @return int|string
     */
    public static function check(string $input): int|string;
}