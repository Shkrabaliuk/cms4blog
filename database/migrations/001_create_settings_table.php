<?php

declare(strict_types=1);

/**
 * Migration: Create settings table
 * Created: 2024-01-01
 */

use App\Core\Database;

function up(): void
{
    $sql = "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        `key` VARCHAR(255) NOT NULL UNIQUE,
        value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    Database::execute($sql);

    // Insert default settings
    $defaultSettings = [
        ['key' => 'site_name', 'value' => 'CMS4Blog'],
        ['key' => 'site_description', 'value' => 'Lightweight PHP CMS for blogging'],
        ['key' => 'posts_per_page', 'value' => '10'],
        ['key' => 'theme', 'value' => 'default'],
    ];

    foreach ($defaultSettings as $setting) {
        $sql = "INSERT IGNORE INTO settings (`key`, value) VALUES (:key, :value)";
        Database::execute($sql, $setting);
    }
}

function down(): void
{
    $sql = "DROP TABLE IF EXISTS settings";
    Database::execute($sql);
}
