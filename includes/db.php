<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!file_exists(__DIR__ . '/../config.php')) {
    if (!strpos($_SERVER['REQUEST_URI'], 'install.php')) {
        header("Location: /install/install.php");
        exit;
    }
    return;
}

require_once __DIR__ . '/../config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Автоматична міграція: додавання колонки status в comments якщо відсутня
    try {
        $columns = $pdo->query("SHOW COLUMNS FROM comments LIKE 'status'")->fetchAll();
        if (empty($columns)) {
            $pdo->exec("ALTER TABLE `comments` ADD COLUMN `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'approved' AFTER `content`");
            $pdo->exec("ALTER TABLE `comments` ADD KEY `status` (`status`)");
        }
    } catch (PDOException $e) {
        // Ігноруємо помилки міграції (наприклад, якщо таблиця ще не створена)
    }
    
    // Автоматична міграція: додавання колонки view_count в posts якщо відсутня
    try {
        $columns = $pdo->query("SHOW COLUMNS FROM posts LIKE 'view_count'")->fetchAll();
        if (empty($columns)) {
            $pdo->exec("ALTER TABLE `posts` ADD COLUMN `view_count` int(11) NOT NULL DEFAULT 0 AFTER `created_at`");
            $pdo->exec("ALTER TABLE `posts` ADD KEY `view_count` (`view_count`)");
        }
    } catch (PDOException $e) {
        // Ігноруємо помилки міграції
    }
} catch (PDOException $e) {
    // Логування помилки (в продакшені краще використовувати error_log)
    error_log("Database connection error: " . $e->getMessage());
    
    // Показуємо загальне повідомлення без деталей
    die("Помилка підключення до бази даних. Спробуйте пізніше.");
}
