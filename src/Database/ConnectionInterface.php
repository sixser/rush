<?php

declare(strict_types = 1);

namespace Rush\Database;

use Rush\Database\Collection\Collector;

/**
 * Interface ConnectionAbstract
 * @package Rush\Database
 */
interface ConnectionInterface
{
    /**
     * Execute an SQL statement and return the number of affected rows
     * @param string $sql SQL statement.
     * @param array $bindings Parameters.
     * @return int
     */
    public function execute(string $sql, array $bindings = []): int;

    /**
     * Executes an SQL statement and return a result set as a Collector object
     * @param string $sql SQL statement.
     * @param array $bindings Parameters.
     * @return Collector
     */
    public function query(string $sql, array $bindings = []): Collector;
}
