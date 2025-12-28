<?php
// includes/db.php

// Якщо ми ще не в корені, шукаємо config.php правильно
$configPath = __DIR__ . '/../config.php';

if (!file_exists($configPath)) {
    // Якщо конфігу немає, треба перенаправити на install.php
    // Визначаємо шлях до install.php відносно кореня сайту
    header("Location: install.php");
    exit;
}

require_once $configPath;

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("<h1>Помилка бази даних</h1><p>" . $e->getMessage() . "</p>");
}