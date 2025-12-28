<?php

declare(strict_types=1);

/**
 * Migration: Create users table
 * Created: 2024-01-01
 */

use App\Core\Database;

function up(): void
{
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(255),
        role ENUM('admin', 'editor', 'author') DEFAULT 'author',
        status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        INDEX idx_username (username),
        INDEX idx_email (email),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    Database::execute($sql);

    // Create default admin user (password: admin123)
    $defaultPassword = password_hash('admin123', PASSWORD_BCRYPT);
    $sql = "INSERT IGNORE INTO users (username, email, password, full_name, role) 
            VALUES (:username, :email, :password, :full_name, :role)";
    
    Database::execute($sql, [
        'username' => 'admin',
        'email' => 'admin@cms4blog.local',
        'password' => $defaultPassword,
        'full_name' => 'Administrator',
        'role' => 'admin',
    ]);
}

function down(): void
{
    $sql = "DROP TABLE IF EXISTS users";
    Database::execute($sql);
}
