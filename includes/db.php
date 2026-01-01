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
} catch (PDOException $e) {
    // Логування помилки (в продакшені краще використовувати error_log)
    error_log("Database connection error: " . $e->getMessage());
    
    // Показуємо загальне повідомлення без деталей
    die("Помилка підключення до бази даних. Спробуйте пізніше.");
}
