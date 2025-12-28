<?php

declare(strict_types=1);

namespace App\Core;

class Migration
{
    private const MIGRATIONS_TABLE = 'migrations';

    /**
     * Create migrations table if not exists
     */
    public static function createMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS " . self::MIGRATIONS_TABLE . " (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        Database::execute($sql);
    }

    /**
     * Run all pending migrations
     */
    public static function runAll(string $migrationsPath): array
    {
        self::createMigrationsTable();

        $executed = self::getExecutedMigrations();
        $migrationFiles = self::getMigrationFiles($migrationsPath);
        $ran = [];

        foreach ($migrationFiles as $file) {
            $migrationName = basename($file, '.php');

            if (in_array($migrationName, $executed)) {
                continue;
            }

            require_once $file;

            // Execute up() function if it exists
            $functionName = 'up';
            if (function_exists($functionName)) {
                call_user_func($functionName);
                self::markAsExecuted($migrationName);
                $ran[] = $migrationName;
            }
        }

        return $ran;
    }

    /**
     * Rollback last migration
     */
    public static function rollback(string $migrationsPath): ?string
    {
        $lastMigration = self::getLastMigration();

        if ($lastMigration === null) {
            return null;
        }

        $file = $migrationsPath . '/' . $lastMigration . '.php';

        if (!file_exists($file)) {
            throw new \RuntimeException("Migration file not found: {$file}");
        }

        require_once $file;

        // Execute down() function if it exists
        $functionName = 'down';
        if (function_exists($functionName)) {
            call_user_func($functionName);
            self::removeFromExecuted($lastMigration);
            return $lastMigration;
        }

        return null;
    }

    /**
     * Get executed migrations from database
     */
    private static function getExecutedMigrations(): array
    {
        if (!Database::tableExists(self::MIGRATIONS_TABLE)) {
            return [];
        }

        $sql = "SELECT migration FROM " . self::MIGRATIONS_TABLE . " ORDER BY id ASC";
        $results = Database::fetchAll($sql);

        return array_column($results, 'migration');
    }

    /**
     * Get all migration files
     */
    private static function getMigrationFiles(string $path): array
    {
        if (!is_dir($path)) {
            return [];
        }

        $files = glob($path . '/*.php');
        sort($files);

        return $files;
    }

    /**
     * Mark migration as executed
     */
    private static function markAsExecuted(string $migration): void
    {
        $sql = "INSERT INTO " . self::MIGRATIONS_TABLE . " (migration) VALUES (:migration)";
        Database::execute($sql, ['migration' => $migration]);
    }

    /**
     * Get last executed migration
     */
    private static function getLastMigration(): ?string
    {
        $sql = "SELECT migration FROM " . self::MIGRATIONS_TABLE . " ORDER BY id DESC LIMIT 1";
        $result = Database::fetchOne($sql);

        return $result['migration'] ?? null;
    }

    /**
     * Remove migration from executed list
     */
    private static function removeFromExecuted(string $migration): void
    {
        $sql = "DELETE FROM " . self::MIGRATIONS_TABLE . " WHERE migration = :migration";
        Database::execute($sql, ['migration' => $migration]);
    }

    /**
     * Get migration status
     */
    public static function status(string $migrationsPath): array
    {
        self::createMigrationsTable();

        $executed = self::getExecutedMigrations();
        $migrationFiles = self::getMigrationFiles($migrationsPath);
        $status = [];

        foreach ($migrationFiles as $file) {
            $migrationName = basename($file, '.php');
            $status[] = [
                'name' => $migrationName,
                'executed' => in_array($migrationName, $executed),
            ];
        }

        return $status;
    }
}
