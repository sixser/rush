<?php

declare(strict_types = 1);

namespace Rush\Log;

/**
 * Class LogLevel
 * @package Rush\Log
 */
class LogLevel
{
    /**
     * Emergency Log Information
     * @var string
     */
    public const EMERGENCY = 'EMERGENCY';

    /**
     * Alert Log Information
     * @var string
     */
    public const ALERT     = 'ALERT';

    /**
     * Critical Log Information
     * @var string
     */
    public const CRITICAL  = 'CRITICAL';

    /**
     * Error Log Information
     * @var string
     */
    public const ERROR     = 'ERROR';

    /**
     * Warning Log Information
     * @var string
     */
    public const WARNING   = 'WARNING';

    /**
     * Notice Log Information
     * @var string
     */
    public const NOTICE    = 'NOTICE';

    /**
     * Info Log Information
     * @var string
     */
    public const INFO      = 'INFO';

    /**
     * Debug Log Information
     * @var string
     */
    public const DEBUG     = 'DEBUG';
}
