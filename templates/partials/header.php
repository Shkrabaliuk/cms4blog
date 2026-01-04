<?php
use App\Services\Auth;
$isAdmin = Auth::check();

if (!isset($blogSettings)) {
    if (!isset($pdo)) {
        $pdo = \App\Config\Database::connect();
    }
    $stmt = $pdo->query("SELECT `key`, `value` FROM settings");
    $blogSettings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $blogSettings[$row['key']] = $row['value'];
    }
}
$blogTitle = $blogSettings['site_title'] ?? '/\\ogos';
?>
<!DOCTYPE html>
<html lang="uk">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? $blogTitle) ?></title>

    <?php
    // SEO & Social Media Logic
    $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $seoTitle = htmlspecialchars($pageTitle ?? $blogTitle);
    $seoDesc = htmlspecialchars(!empty($post) && !empty($post['content'])
        ? mb_substr(strip_tags($post['content']), 0, 160) . '...'
        : ($blogSettings['site_description'] ?? ''));
    $seoImage = !empty($settings['author_avatar'])
        ? (strpos($settings['author_avatar'], 'http') === 0 ? $settings['author_avatar'] : "https://$_SERVER[HTTP_HOST]" . $settings['author_avatar'])
        : ""; // Fallback image if needed
    
    // Determine type
    $ogType = isset($post) ? 'article' : 'website';
    ?>

    <?php if (!empty($blogSettings['site_description'])): ?>
        <meta name="description" content="<?= $seoDesc ?>">
    <?php endif; ?>

    <!-- Canonical -->
    <link rel="canonical" href="<?= htmlspecialchars(strtok($currentUrl, '?')) ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="<?= $ogType ?>">
    <meta property="og:url" content="<?= $currentUrl ?>">
    <meta property="og:title" content="<?= $seoTitle ?>">
    <meta property="og:description" content="<?= $seoDesc ?>">
    <?php if ($seoImage): ?>
        <meta property="og:image" content="<?= $seoImage ?>">
    <?php endif; ?>

    <!-- Twitter -->
    <meta property="twitter:card" content="summary">
    <meta property="twitter:url" content="<?= $currentUrl ?>">
    <meta property="twitter:title" content="<?= $seoTitle ?>">
    <meta property="twitter:description" content="<?= $seoDesc ?>">
    <?php if ($seoImage): ?>
        <meta property="twitter:image" content="<?= $seoImage ?>">
    <?php endif; ?>

    <link rel="alternate" type="application/rss+xml" title="<?= htmlspecialchars($blogTitle) ?> RSS Feed"
        href="/rss.php">
    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/theme.css">


    <?php if (!empty($blogSettings['google_analytics_id'])): ?>
        <script async
            src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($blogSettings['google_analytics_id']) ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag() { dataLayer.push(arguments); }
            gtag('js', new Date());
            gtag('config', '<?= htmlspecialchars($blogSettings['google_analytics_id']) ?>');
        </script>
    <?php endif; ?>
</head>

<body>

    <header>
        <div class="header-left-group">
            <?php if (!empty($blogSettings['author_avatar'])): ?>
                <a href="/" class="avatar-link">
                    <img src="<?= htmlspecialchars($blogSettings['author_avatar']) ?>"
                        alt="<?= htmlspecialchars($blogSettings['blog_author'] ?? 'Author') ?>" class="site-avatar">
                </a>
            <?php endif; ?>

            <div class="brand-container">
                <h1>
                    <?php if ($_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '/index.php'): ?>
                        <?= htmlspecialchars($blogTitle) ?>
                    <?php else: ?>
                        <a href="/"><?= htmlspecialchars($blogTitle) ?></a>
                    <?php endif; ?>
                </h1>

                <?php if (!empty($blogSettings['blog_tagline'])): ?>
                    <p class="tagline"><?= htmlspecialchars($blogSettings['blog_tagline']) ?></p>
                <?php endif; ?>

                <nav class="main-nav">
                    <a href="/about">Про мене</a>
                    <a href="/archive">Архів</a>
                </nav>
            </div>
        </div>


        <div class="header-controls" id="headerControls">
            <?php if ($isAdmin): ?>
                <a href="/admin/new-post" class="icon-btn admin-icon" title="Новий пост">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"
                        stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                </a>
                <a href="/admin/settings" class="icon-btn admin-icon" title="Налаштування">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path
                            d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z">
                        </path>
                    </svg>
                </a>
            <?php endif; ?>

            <form action="/search.php" method="get" id="searchForm">
                <input type="search" name="q" id="searchInput" placeholder="Пошук..." required autocomplete="off">
                <button type="button" class="icon-btn search-icon" id="searchToggle" title="Пошук">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
            </form>
        </div>
    </header>
    <main>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const searchToggle = document.getElementById('searchToggle');
                const searchInput = document.getElementById('searchInput');
                const searchForm = document.getElementById('searchForm');

                if (searchToggle && searchInput) {
                    searchToggle.addEventListener('click', (e) => {
                        e.preventDefault();

                        if (searchInput.classList.contains('active')) {
                            if (searchInput.value.trim().length > 0) {
                                searchForm.submit();
                            } else {
                                searchInput.classList.remove('active');
                            }
                        } else {
                            searchInput.classList.add('active');
                            searchInput.focus();
                        }
                    });

                    searchInput.addEventListener('blur', () => {
                        setTimeout(() => {
                            if (document.activeElement !== searchInput && searchInput.value.trim() === '') {
                                searchInput.classList.remove('active');
                            }
                        }, 200);
                    });
                }
            });
        </script>