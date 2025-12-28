<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Мій Блог' ?></title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
</head>
<body>

<div class="container">
    <header>
        <div class="logo">
            <a href="index.php">Мій Блог</a>
        </div>
        <div class="header-right">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            
            <?php if (is_admin()): ?>
                <a href="post-editor.php" class="btn btn-primary" style="margin-left: 15px;">+ Написати</a>
                <a href="login.php?logout=1" class="btn btn-outline" style="color: #d00;">Вийти</a>
            <?php else: ?>
                <button onclick="openLoginModal()" class="btn btn-outline" style="margin-left: 15px;">Увійти</button>
            <?php endif; ?>
        </div>
    </header>