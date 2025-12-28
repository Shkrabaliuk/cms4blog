<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private static ?PDO $connection = null;
    private static array $config = [];

    /**
     * Initialize database configuration
     */
    public static function configure(array $config): void
    {
        self::$config = $config;
    }

    /**
     * Get PDO connection (singleton)
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            self::connect();
        }

        return self::$connection;
    }

    /**
     * Create new database connection
     */
    private static function connect(): void
    {
        $host = self::$config['host'] ?? 'localhost';
        $port = self::$config['port'] ?? 3306;
        $database = self::$config['database'] ?? '';
        $username = self::$config['username'] ?? 'root';
        $password = self::$config['password'] ?? '';
        $charset = self::$config['charset'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";

        try {
            self::$connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Check if database exists
     */
    public static function databaseExists(): bool
    {
        try {
            $connection = self::getConnection();
            return true;
        } catch (\RuntimeException $e) {
            return false;
        }
    }

    /**
     * Create database if not exists
     */
    public static function createDatabase(string $dbName): bool
    {
        try {
            $host = self::$config['host'] ?? 'localhost';
            $port = self::$config['port'] ?? 3306;
            $username = self::$config['username'] ?? 'root';
            $password = self::$config['password'] ?? '';
            $charset = self::$config['charset'] ?? 'utf8mb4';

            $dsn = "mysql:host={$host};port={$port};charset={$charset}";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $sql = "CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET {$charset} COLLATE {$charset}_unicode_ci";
            $pdo->exec($sql);

            // Update config and reconnect
            self::$config['database'] = $dbName;
            self::$connection = null;

            return true;
        } catch (PDOException $e) {
            throw new \RuntimeException('Failed to create database: ' . $e->getMessage());
        }
    }

    /**
     * Execute query and return statement
     */
    public static function query(string $sql, array $params = []): PDOStatement
    {
        $connection = self::getConnection();
        $statement = $connection->prepare($sql);
        $statement->execute($params);

        return $statement;
    }

    /**
     * Fetch all rows
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        $statement = self::query($sql, $params);
        return $statement->fetchAll();
    }

    /**
     * Fetch single row
     */
    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $statement = self::query($sql, $params);
        $result = $statement->fetch();

        return $result !== false ? $result : null;
    }

    /**
     * Execute statement and return affected rows
     */
    public static function execute(string $sql, array $params = []): int
    {
        $statement = self::query($sql, $params);
        return $statement->rowCount();
    }

    /**
     * Get last insert ID
     */
    public static function lastInsertId(): string
    {
        return self::getConnection()->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }

    /**
     * Rollback transaction
     */
    public static function rollback(): bool
    {
        return self::getConnection()->rollBack();
    }

    /**
     * Check if table exists
     */
    public static function tableExists(string $tableName): bool
    {
        try {
            $sql = "SHOW TABLES LIKE :table";
            $result = self::fetchOne($sql, ['table' => $tableName]);
            return $result !== null;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get all tables in database
     */
    public static function getTables(): array
    {
        $sql = "SHOW TABLES";
        $tables = self::fetchAll($sql);
        return array_map('current', $tables);
    }
}
