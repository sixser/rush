<?php

declare(strict_types = 1);

namespace Rush\Database;

/**
 * Class DsnFactory
 * @package Rush\Database
 */
class DsnFactory
{
    /**
     * Create mysql tcp connection dsn info
     * @param string $host Mysql server ip.
     * @param int $port Mysql server port.
     * @param string $dbname Database name.
     * @param string $charset Database charset.
     * @return string
     */
    public static function createMysqlTcpDsn(string $host, int $port, string $dbname, string $charset = 'utf8mb4'): string
    {
        return sprintf(
                "mysql:host=%s;port=%d;dbname=%s;charset=%s",
                $host, $port, $dbname, $charset
            );
    }

    /**
     * Create mysql tcp connection dsn info
     * @param string $sock Mysql sock file path.
     * @param string $dbname Database name.
     * @param string $charset Database charset.
     * @return string
     */
    public static function createMysqlSocketDsn(string $sock, string $dbname, string $charset = 'utf8mb4'): string
    {
        return sprintf(
                "mysql:unix_socket=%s;dbname=%s;charset=%s",
                $sock, $dbname, $charset
            );
    }

    /**
     * Create postgresql tcp connection dsn info
     * @param string $host Postgresql server ip.
     * @param int $port Postgresql server port.
     * @param string $dbname Database name.
     * @return string
     */
    public static function createPgsqlDsn(string $host, int $port, string $dbname): string
    {
        return sprintf(
            "pgsql:host=%s;port=%d;dbname=%s",
            $host, $port, $dbname
        );
    }
}
