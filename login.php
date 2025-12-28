<?php
// login.php (лежить в корені)
require_once 'includes/db.php';
require_once 'includes/functions.php';

session_start();

// Вихід
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Вхід
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    // Перевіряємо чи є таблиця users (для першого запуску)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `password` varchar(255) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $adminExists = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    if (!$adminExists) {
        // РЕЄСТРАЦІЯ (якщо база порожня)
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (password) VALUES (?)");
        $stmt->execute([$hash]);
        $_SESSION['is_admin'] = true;
        header("Location: index.php"); // Успіх
        exit;
    } else {
        // ПЕРЕВІРКА ПАРОЛЯ
        $stmt = $pdo->query("SELECT * FROM users LIMIT 1");
        $user = $stmt->fetch();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['is_admin'] = true;
            header("Location: index.php"); // Успіх
        } else {
            header("Location: index.php?login_error=1"); // Помилка (відкриє попап знову)
        }
        exit;
    }
}

// Якщо відкрили файл просто так — кидаємо на головну
header("Location: index.php");