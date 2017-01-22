<?php
declare(strict_types=1);

namespace WoohooLabs\Worm\Connection;

use PDO;
use PDOStatement;
use Traversable;
use WoohooLabs\Worm\Logger\Logger;

abstract class AbstractPdoConnection implements ConnectionInterface
{
    /**
     * @var \PDO
     */
    private $pdo;

    private $fetchStyle = PDO::FETCH_ASSOC;

    /**
     * @var Logger
     */
    private $logger;

    protected function __construct(
        string $dsn,
        string $username,
        string $password,
        array $options,
        bool $isLogging
    ) {
        $this->pdo = new PDO($dsn, $username, $password, $options);

        foreach ($options as $key => $option) {
            $this->pdo->setAttribute($key, $option);
        }

        $this->logger = new Logger($isLogging);
    }

    public function queryAll(string $sql, array $params = []): array
    {
        $statement = $this->pdo->prepare($sql);
        $this->executePreparedStatement($statement, $sql, $params);

        return $statement->fetchAll($this->fetchStyle);
    }

    public function query(string $sql, array $params = []): Traversable
    {
        $statement = $this->pdo->prepare($sql);
        $this->executePreparedStatement($statement, $sql, $params);

        if ($statement->nextRowset() === false) {
            return [];
        }

        while ($statement->nextRowset()) {
            yield $statement->fetch($this->fetchStyle);
        }
    }

    public function execute(string $sql, array $params = []): bool
    {
        $statement = $this->pdo->prepare($sql);

        return $this->executePreparedStatement($statement, $sql, $params);
    }

    public function beginTransaction(): bool
    {
        $result = $this->pdo->beginTransaction();

        $this->logger->log("BEGIN", $result);

        return $result;
    }

    public function commit(): bool
    {
        $result = $this->pdo->commit();

        $this->logger->log("COMMIT", $result);

        return $result;
    }

    public function rollback(): bool
    {
        $result = $this->pdo->rollBack();

        $this->logger->log("ROLLBACK", $result);

        return $result;
    }

    public function getLastInsertedId($name = null)
    {
        return $this->pdo->lastInsertId($name);
    }

    public function getLog(): array
    {
        return $this->logger->getLog();
    }

    protected function getDefaultAttributes(): array
    {
        return [
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
    }

    private function executePreparedStatement(PDOStatement $statement, string $sql, array $params): bool
    {
        foreach ($params as $key => $value) {
            if ($value === null) {
                $bindType = PDO::PARAM_NULL;
            } elseif (is_int($value) || is_float($value)) {
                $bindType = PDO::PARAM_INT;
            } else {
                $bindType = PDO::PARAM_STR;
            }

            $bindKey = is_string($key) ? $key : $key + 1;

            $statement->bindValue($bindKey, $value, $bindType);
        }

        $result = $statement->execute();

        $this->logger->log($sql, $result, $params);

        return $result;
    }
}
