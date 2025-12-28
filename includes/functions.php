<?php
// includes/functions.php

// Запускаємо сесію, якщо вона ще не запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Перевірка: чи авторизований користувач
 */
function is_admin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Отримати всі пости
 */
function get_posts() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

/**
 * Отримати один пост
 */
function get_post($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Час читання
 */
function estimate_reading_time($text) {
    $words = str_word_count(strip_tags($text));
    $minutes = ceil($words / 200);
    return $minutes > 0 ? $minutes : 1;
}