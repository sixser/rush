<?php

declare(strict_types = 1);

namespace Rush\Database;

use PDO;
use PDOStatement;
use Rush\Database\Collection\Collector;
use Rush\Log\LogException;
use Rush\Log\LoggerAwareTrait;

/**
 * Trait Connection
 * @package Rush\Database
 */
class Connection implements ConnectionInterface
{
    use LoggerAwareTrait;

    /**
     * Database Dsn
     * @var string
     */
    protected string $dsn = '';

    /**
     * Database Username
     * @var string
     */
    protected string $username = '';

    /**
     * Database Username
     * @var string
     */
    protected string $password = '';

    /**
     * Database Attribute
     * @var array
     */
    protected array $option = [];

    /**
     * Pdo Instance
     * @var PDO
     */
    protected PDO $instance;

    /**
     * Set pdo dsn
     * @param string $dsn Pdo dsn.
     * @return static
     */
    public function withDsn(string $dsn): static
    {
        $this->dsn = $dsn;

        return $this;
    }

    /**
     * Set mysql userinfo.
     * @param string $username User name.
     * @param string $password User password.
     * @return static
     */
    public function withUser(string $username, string $password = ''): static
    {
        $this->username = $username;
        $this->password = $password;

        return $this;
    }

    /**
     * Set pdo attribute
     * @param int $name Attribute name.
     * @param string $value Attribute value.
     * @return static
     */
    public function withOption(int $name, string $value): static
    {
        $this->option[$name] = $value;

        return $this;
    }

    /**
     * Connect Database
     * @return static
     */
    public function connect(): static
    {
        $this->instance = new PDO(
            $this->dsn, $this->username, $this->password, $this->option
        );

        return $this;
    }

    /**
     * @inheritDoc
     * @throws DatabaseException
     * @throws LogException
     */
    public function execute(string $sql, array $bindings = []): int
    {
        $statement = $this->instance->prepare($sql);

        $this->bindValues($statement, $bindings);

        $start = microtime(true);

        $this->runStatement($statement);

        $this->record($sql, $bindings, $start);

        return $statement->rowCount();
    }

    /**
     * @inheritDoc
     * @throws DatabaseException
     * @throws LogException
     */
    public function query(string $sql, array $bindings = []): Collector
    {
        $statement = $this->instance->prepare($sql);

        $this->bindValues($statement, $bindings);

        $statement->setFetchMode(PDO::FETCH_ASSOC);

        $start = microtime(true);

        $this->runStatement($statement);

        $this->record($sql, $bindings, $start);

        return (new Collector($statement->fetchAll()));
    }

    /**
     * Binds a value to a parameter
     * @param PDOStatement $statement Statement.
     * @param array $bindings Parameters.
     * @return void
     */
    protected function bindValues(PDOStatement $statement, array $bindings): void
    {
        foreach ($bindings as $key => $val) {
            $statement->bindValue(
                is_numeric($key) ? $key + 1 : $key,
                $val,
                is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }

    /**
     * Executes a prepared statement
     * @param PDOStatement $statement Statement.
     * @return void
     * @throws DatabaseException
     */
    protected function runStatement(PDOStatement $statement): void
    {
        $statement->execute();

        '00000' !== $statement->errorCode() &&
        throw new DatabaseException("Fail to execute SQL statement, ". implode(' ', $statement->errorInfo()));
    }

    /**
     * Log the information of sql execution
     * @param string $query SQL statement.
     * @param array $binding Parameters.
     * @param float $start Timestamp of beginning.
     * @return void
     * @throws LogException
     */
    protected function record(string $query, array $binding, float $start): void
    {
        if (is_null($this->logger)) {
            return;
        }

        $this->logger->info(json_encode([
            'time' => round((microtime(true) - $start), 5),
            'query' => $query,
            'bind' => $binding
        ], JSON_UNESCAPED_UNICODE));
    }

    /**
     * Checks if inside a transaction
     * @return bool
     */
    public function transaction(): bool
    {
        return $this->instance->inTransaction();
    }

    /**
     * Initiates a transaction
     * @return bool
     */
    public function begin(): bool
    {
        return $this->instance->beginTransaction();
    }

    /**
     * Rolls back a transaction
     * @return bool
     */
    public function rollback(): bool
    {
        return $this->instance->rollBack();
    }

    /**
     * Commits a transaction
     * @return bool
     */
    public function commit(): bool
    {
        return $this->getPdo()->commit();
    }

    /**
     * Get Pdo instance
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->instance;
    }
}
