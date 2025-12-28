<?php
// index.php

// 1. Перевірка: чи встановлений блог?
if (!file_exists('config.php')) {
    header("Location: install.php");
    exit;
}

// 2. Підключаємо налаштування
require 'config.php';

// 3. Підключаємося до БД
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Помилка підключення до бази даних: " . $e->getMessage());
}

// 4. Отримуємо пости
// (Поки що просто всі пости, від нових до старих)
$stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мій Блог</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header>
    <div class="avatar">
        </div>
    <div>
        <h1 class="blog-title">Назва блогу</h1>
        <div class="subtitle">Підзаголовок</div>
    </div>
</header>

<main>
    <?php if (count($posts) > 0): ?>
        
        <div class="meta-top">
            <a href="#" class="nav-link">Пізніше</a> 
            <span class="hotkey">Ctrl + ↑</span>
        </div>

        <?php foreach ($posts as $post): ?>
            <article>
                <h2><a href="#"><?= htmlspecialchars($post['title']) ?></a></h2>
                
                <div class="content">
                    <?= nl2br(htmlspecialchars($post['content'])) ?>
                </div>
                
                <div class="meta">
                    <?= date('d.m.Y', strtotime($post['created_at'])) ?>
                </div>
            </article>
        <?php endforeach; ?>

    <?php else: ?>
        <article>
            <h2>Ще немає жодного запису</h2>
            <p>Вітаю з успішним встановленням! Саме час написати щось у базу даних.</p>
        </article>
    <?php endif; ?>
</main>

<footer>
    <div>
        © Автор блогу, <?= date('Y'); ?> 
        <span class="rss-badge">RSS</span>
    </div>
    <div style="margin-top: 10px; color: #999;">
        Рушій — <a href="#" style="color: #999; text-decoration: none; border-bottom: 1px solid #ddd;">CMS4Blog</a>
    </div>
</footer>

</body>
</html>