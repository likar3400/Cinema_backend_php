<?php
declare(strict_types=1);
namespace App\Core;
use PDO; use PDOException; use PDOStatement; use RuntimeException;

final class Database
{
    private static ?self $instance = null;
    private readonly PDO $pdo;

    private function __construct()
    {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException('DB connection error: ' . $e->getMessage());
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    public function query(string $sql, array $p = []): PDOStatement
    { $s = $this->pdo->prepare($sql); $s->execute($p); return $s; }

    public function fetchAll(string $sql, array $p = []): array
    { return $this->query($sql, $p)->fetchAll(); }

    public function fetchOne(string $sql, array $p = []): array|false
    { return $this->query($sql, $p)->fetch(); }

    public function fetchColumn(string $sql, array $p = []): mixed
    { return $this->query($sql, $p)->fetchColumn(); }

    public function insert(string $sql, array $p = []): int
    { $this->query($sql, $p); return (int)$this->pdo->lastInsertId(); }

    public function execute(string $sql, array $p = []): int
    { return $this->query($sql, $p)->rowCount(); }

    public function beginTransaction(): void { $this->pdo->beginTransaction(); }
    public function commit(): void           { $this->pdo->commit(); }
    public function rollback(): void         { if ($this->pdo->inTransaction()) $this->pdo->rollBack(); }

    private function __clone() {}
    public function __wakeup(): never { throw new RuntimeException('No unserialize.'); }
}
