<?php

declare(strict_types=1);

final class Database
{
    private PDO $pdo;
    private ?PDOStatement $stmt = null;

    public function __construct(
        ?string $host = null,
        ?string $user = null,
        ?string $pass = null,
        ?string $dbname = null,
        ?string $port = null,
    ) {
        $envHost = getenv('DB_HOST') ?: null;
        $envUser = getenv('DB_USER') ?: null;
        $envPass = getenv('DB_PASS') ?: null;
        $envName = getenv('DB_NAME') ?: null;
        $envPort = getenv('DB_PORT') ?: null;

        $constHost = defined('DB_HOST') ? (string) DB_HOST : null;
        $constUser = defined('DB_USER') ? (string) DB_USER : null;
        $constPass = defined('DB_PASS') ? (string) DB_PASS : null;
        $constName = defined('DB_NAME') ? (string) DB_NAME : null;

        $host   = $host   ?? $envHost ?? $constHost ?? '127.0.0.1';
        $user   = $user   ?? $envUser ?? $constUser ?? 'root';
        $pass   = $pass   ?? $envPass ?? $constPass ?? '';
        $dbname = $dbname ?? $envName ?? $constName ?? '';
        $port   = $port   ?? $envPort ?? '3306';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            die("DB ERROR: " . $e->getMessage());
        }
    }

    /** Prepare statement */
    public function query(string $sql): self
    {
        $this->stmt = $this->pdo->prepare($sql);
        return $this;
    }

    /** Bind one param */
    public function bind(string $param, mixed $value, ?int $type = null): self
    {
        if ($this->stmt === null) {
            throw new LogicException("No prepared statement. Call query() before bind().");
        }

        if ($type === null) {
            $type = match (true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                $value === null => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };
        }

        $this->stmt->bindValue($param, $value, $type);
        return $this;
    }

    /** Bind multiple params: [':id' => 1, ':name' => 'x'] */
    public function bindAll(array $params): self
    {
        foreach ($params as $k => $v) {
            $this->bind((string)$k, $v);
        }
        return $this;
    }

    /** Execute current statement */
    public function execute(): bool
    {
        if ($this->stmt === null) {
            throw new LogicException("No prepared statement. Call query() before execute().");
        }
        return $this->stmt->execute();
    }

    /** Execute + fetchAll */
    public function fetchAll(): array
    {
        $this->execute();
        return $this->stmt?->fetchAll() ?? [];
    }

    /** Execute + fetch single row */
    public function fetch(): array|false
    {
        $this->execute();
        return $this->stmt?->fetch() ?? false;
    }

    public function rowCount(): int
    {
        return $this->stmt?->rowCount() ?? 0;
    }

    public function lastInsertedID(): string
    {
        return $this->pdo->lastInsertId();
    }

    /** Quick helper: prepare + bindAll + execute */
    public function run(string $sql, array $params = []): bool
    {
        $this->query($sql);
        if ($params) {
            // params keys should include ":" ideally
            foreach ($params as $k => $v) {
                $key = str_starts_with((string)$k, ':') ? (string)$k : ':' . (string)$k;
                $this->bind($key, $v);
            }
        }
        return $this->execute();
    }

    /** Quick helper: run + fetchAll */
    public function all(string $sql, array $params = []): array
    {
        $this->query($sql);
        if ($params) {
            foreach ($params as $k => $v) {
                $key = str_starts_with((string)$k, ':') ? (string)$k : ':' . (string)$k;
                $this->bind($key, $v);
            }
        }
        return $this->fetchAll();
    }

    /** Quick helper: run + fetch */
    public function one(string $sql, array $params = []): array|false
    {
        $this->query($sql);
        if ($params) {
            foreach ($params as $k => $v) {
                $key = str_starts_with((string)$k, ':') ? (string)$k : ':' . (string)$k;
                $this->bind($key, $v);
            }
        }
        return $this->fetch();
    }

    // Transactions
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }

    /** Transaction helper */
    public function transaction(callable $fn): mixed
    {
        $this->beginTransaction();
        try {
            $result = $fn($this);
            $this->commit();
            return $result;
        } catch (Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    /** Return PDO (FIXED, recursion yo‘q) */
    public function pdo(): PDO
    {
        return $this->pdo;
    }
}
