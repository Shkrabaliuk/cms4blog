<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../functions.php';

$blog_name = get_setting('blog_name', '/\\ogos');
$logo_path = get_setting('logo_path');
$is_logged = is_admin();
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? $blog_name) ?></title>
    <link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<header>
    <?php if ($logo_path): ?>
    <div class="site-logo">
        <a href="/"><img src="<?= htmlspecialchars($logo_path) ?>" alt="<?= htmlspecialchars($blog_name) ?>"></a>
    </div>
    <?php endif; ?>
    
    <h1><a href="/"><?= htmlspecialchars($blog_name) ?></a></h1>
    
    <nav>
        <?php if ($is_logged): ?>
            <a href="/admin/admin.php">Адмін</a>
            <a href="/admin/post-editor.php">Новий пост</a>
            <a href="/admin/admin.php?logout=1">Вийти</a>
        <?php else: ?>
            <a href="/admin/login.php">Увійти</a>
        <?php endif; ?>
    </nav>
</header>

<main>
