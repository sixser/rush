<?php

declare(strict_types = 1);

namespace Rush\Log;

/**
 * Trait LoggerAwareTrait   
 * @package Rush\Log
 */
trait LoggerAwareTrait
{
    /**
     * The Logger Instance
     * @var LoggerInterface|null
     */
    protected LoggerInterface|null $logger = null;

    /**
     * Sets a logger.
     * @param LoggerInterface $logger
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
