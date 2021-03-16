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
     * Create Mysql Tcp Connection Dsn Info
     * @param string $host Mysql server ip.
     * @param int $port Mysql server port.
     * @param string $dbname Database name.
     * @param string $charset Database charset.
     * @return string
     */
    public static function createMysqlTcpDsn(string $host, int $port, string $dbname, string $charset = 'utf8'): string
    {
        return sprintf(
                "mysql:host=%s;port=%d;dbname=%s;charset=%s",
                $host, $port, $dbname, $charset
            );
    }

    /**
     * Create Mysql Tcp Connection Dsn Info
     * @param string $sock Mysql sock file path.
     * @param string $dbname Database name.
     * @param string $charset Database charset.
     * @return string
     */
    public static function createMysqlSocketDsn(string $sock, string $dbname, string $charset = 'utf8'): string
    {
        return sprintf(
                "mysql:unix_socket=%s;dbname=%s;charset=%s",
                $sock, $dbname, $charset
            );
    }
}
