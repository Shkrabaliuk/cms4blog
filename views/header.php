<!DOCTYPE html>
<html lang="uk" class="dark-mode">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? $blogTitle) ?></title>
    
    <?php if (!empty($blogSettings['blog_description'])): ?>
    <meta name="description" content="<?= htmlspecialchars($blogSettings['blog_description']) ?>">
    <?php endif; ?>
    
    <!-- RSS Feed -->
    <link rel="alternate" type="application/rss+xml" title="<?= htmlspecialchars($blogTitle) ?> RSS Feed" href="/rss.php">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    
    <!-- Tilda Sans Font -->
    <link rel="stylesheet" href="/assets/fonts/tildasans.css">
    
    <!-- Main CSS (minified) -->
    <?php if (defined('ENV') && ENV === 'development'): ?>
        <link rel="stylesheet" href="/assets/css/style.css">
    <?php else: ?>
        <link rel="stylesheet" href="/assets/minify.php?f=style.css&t=css&v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <?php endif; ?>
    
    <!-- FontAwesome icons (CDN) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <?php if (!empty($blogSettings['google_analytics_id'])): ?>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($blogSettings['google_analytics_id']) ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?= htmlspecialchars($blogSettings['google_analytics_id']) ?>');
    </script>
    <?php endif; ?>
</head>
<body>

<?php
// Підключаємо авторизацію
require_once __DIR__ . '/../includes/auth.php';
$isAdmin = isLoggedIn();

// Завантажуємо налаштування блогу
if (!isset($blogSettings)) {
    global $pdo;
    $stmt = $pdo->query("SELECT `key`, `value` FROM settings");
    $blogSettings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $blogSettings[$row['key']] = $row['value'];
    }
}
$blogTitle = $blogSettings['blog_title'] ?? '/\ogos';
?>

<div class="common">
    <div class="flag">
        <div class="header-content">
            <div class="header-description">
                <div class="title">
                    <div class="title-inner">
                        <?php if (!empty($blogSettings['author_avatar'])): ?>
                        <div class="logo-marginal">
                            <div class="user-picture-container">
                                <div class="user-picture-inner">
                                    <img src="<?= htmlspecialchars($blogSettings['author_avatar']) ?>" 
                                         class="user-picture-image" 
                                         alt="" />
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="logo">
                            <?php if (!empty($blogSettings['author_avatar'])): ?>
                            <div class="user-picture-container">
                                <div class="user-picture-inner">
                                    <img src="<?= htmlspecialchars($blogSettings['author_avatar']) ?>" 
                                         class="user-picture-image" 
                                         alt="" />
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <h1>
                            <a href="/"><?= htmlspecialchars($blogTitle) ?> <span>blog</span></a>
                        </h1>
                    </div>
                    
                    <?php if (!empty($blogSettings['blog_tagline'])): ?>
                    <div class="blog-description">
                        <p><?= htmlspecialchars($blogSettings['blog_tagline']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="spotlight">
                <span class="admin-links-floating">
                    <span class="admin-menu admin-links">
                        <?php if ($isAdmin): ?>
                        <span class="admin-icon" title="Новий пост">
                            <a href="#" onclick="toggleNewPostForm(); return false;" class="nu">
                                <i class="fas fa-plus"></i>
                            </a>
                        </span>
                        
                        <span class="admin-icon">
                            <a href="/admin/settings.php" class="nu" title="Налаштування">
                                <i class="fas fa-cog"></i>
                            </a>
                        </span>
                        <?php endif; ?>
                    </span>
                </span>
                
                <form class="search-box-nano" action="/search.php" method="get">
                    <label>
                        <input type="search" 
                               name="q" 
                               value="" 
                               placeholder="Пошук" 
                               required="required" />
                        <span class="search-icon">
                            <i class="fas fa-search"></i>
                        </span>
                    </label>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <main>
