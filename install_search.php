<?php
// install_search.php

// 1. ПІДКЛЮЧАЄМО АВТОЗАВАНТАЖУВАЧ
$loaderPath = __DIR__ . '/config/autoload.php';

if (!file_exists($loaderPath)) {
    die("❌ Помилка: Не знайдено файл автозавантаження: $loaderPath");
}
require_once $loaderPath;

// Використовуємо лише MysqlRepository напряму
use S2\Rose\Storage\Database\MysqlRepository;

// 2. ПІДКЛЮЧЕННЯ ДО БД (Ваші дані)
$host = 'localhost';
$db   = 'logos_db';
$user = 'root';
$pass = '5sk1#AAD1#b1bkk';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Підключаємось
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "✅ Підключення до БД успішне.<br>";

    // Перевірка наявності класу
    if (!class_exists('S2\Rose\Storage\Database\MysqlRepository')) {
        throw new Exception("Клас MysqlRepository не знайдено. Перевірте папку assets/libs/rose/");
    }

    // 3. СТВОРЕННЯ ТАБЛИЦЬ ПОШУКУ
    // ВИПРАВЛЕННЯ: Передаємо $pdo напряму, без PdoStorage
    $repository = new MysqlRepository($pdo, 'rose_');
    
    echo "⏳ Виконую erase() для створення таблиць...<br>";
    
    // Ця команда видалить старі (якщо є) і створить нові таблиці
    $repository->erase();

    echo "<h2 style='color:green'>🎉 Успіх!</h2>";
    echo "Таблиці Rose створено.<br>";
    echo "Тепер <b>видаліть цей файл</b> і оновіть блог.";

} catch (PDOException $e) {
    die("<h3 style='color:red'>❌ Помилка БД:</h3>" . $e->getMessage());
} catch (TypeError $e) {
    die("<h3 style='color:red'>❌ Помилка типів:</h3>" . $e->getMessage());
} catch (Exception $e) {
    die("<h3 style='color:red'>❌ Помилка:</h3>" . $e->getMessage());
}