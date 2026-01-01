<?php
/**
 * Міграційний скрипт для додавання поля status до таблиці comments
 * Запускати один раз після оновлення системи
 */

require '../config.php';
require '../includes/db.php';

try {
    // Перевірка чи поле вже існує
    $stmt = $pdo->query("SHOW COLUMNS FROM comments LIKE 'status'");
    if ($stmt->rowCount() > 0) {
        die("Поле 'status' вже існує в таблиці comments. Міграція не потрібна.");
    }
    
    // Додати поле status
    $pdo->exec("ALTER TABLE comments ADD COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'approved' AFTER content");
    
    // Оновити всі існуючі коментарі до статусу 'approved'
    $pdo->exec("UPDATE comments SET status = 'approved' WHERE status IS NULL");
    
    echo "✅ Міграцію виконано успішно!<br>";
    echo "Поле 'status' додано до таблиці comments.<br>";
    echo "Всі існуючі коментарі отримали статус 'approved'.<br>";
    echo "<br><a href='../admin/comments.php'>Перейти до модерації коментарів</a>";
    
} catch (Exception $e) {
    die("❌ Помилка під час міграції: " . htmlspecialchars($e->getMessage()));
}
?>
