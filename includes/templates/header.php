<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../functions.php';

$blog_name = get_setting('blog_name', 'Мій Блог');
$blog_subtitle = get_setting('blog_subtitle', '');
$blog_description = get_setting('blog_description', '');
$avatar = get_setting('avatar', '');
?>
<!DOCTYPE html>
<html lang="uk">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?= htmlspecialchars(generate_page_title($pageTitle ?? '', $blog_name)) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php if ($blog_description): ?>
<meta name="description" content="<?= htmlspecialchars($blog_description) ?>" />
<meta property="og:description" content="<?= htmlspecialchars($blog_description) ?>" />
<?php endif; ?>
<link rel="stylesheet" type="text/css" href="/assets/css/style.css" />
<link rel="alternate" type="application/rss+xml" title="<?= htmlspecialchars($blog_name) ?> RSS" href="/rss.php" />
</head>
<body>

<div class="common">

<div class="flag">
  <div class="header-content">
    <div class="header-description">
      <div class="title">
        <div class="title-inner">
          
          <div class="logo-marginal">
            <?php if ($avatar): ?>
              <a href="/index.php"><img src="<?= htmlspecialchars($avatar) ?>" alt="" class="logo-avatar" /></a>
            <?php else: ?>
              <div class="logo-avatar"></div>
            <?php endif; ?>
          </div>

          <div class="logo">
            <?php if ($avatar): ?>
              <a href="/index.php"><img src="<?= htmlspecialchars($avatar) ?>" alt="" class="logo-avatar" /></a>
            <?php else: ?>
              <div class="logo-avatar"></div>
            <?php endif; ?>
          </div>

          <h1><a href="/index.php"><?= htmlspecialchars($blog_name) ?></a></h1>
          <?php if ($blog_subtitle): ?>
            <p><?= htmlspecialchars($blog_subtitle) ?></p>
          <?php endif; ?>

        </div>
      </div>
    </div>

    <div class="spotlight">
      <span class="admin-links-floating">
        <span class="admin-menu admin-links">
          
          <span class="admin-icon" title="Пошук по тегах">
            <a href="/tags.php" class="nu">Теги</a>
          </span>

          <?php if (is_admin()): ?>
            <span class="admin-icon" title="Керування постами">
              <a href="/admin/posts.php" class="nu">Пости</a>
            </span>
            <span class="admin-icon" title="Модерація коментарів">
              <a href="/admin/comments.php" class="nu">Коментарі</a>
            </span>
            <span class="admin-icon" title="Налаштування">
              <a href="/admin/settings.php" class="nu">
                <span class="e2-svgi">
                  <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                    <path stroke="none" fill-rule="evenodd" clip-rule="evenodd" d="M8.018 1.747a6.248 6.248 0 0 0-6.249 6.25 6.25 6.25 0 1 0 6.249-6.25zm0 11a4.75 4.75 0 0 1-4.75-4.75 4.75 4.75 0 1 1 4.75 4.75z"/>
                  </svg>
                </span>
              </a>
            </span>
          <?php endif; ?>

        </span>

        <form class="e2-search-box-nano" action="/index.php" method="get">
          <label>
            <input class="js-search-query" type="search" name="search" value="" placeholder="Пошук" />
            <span class="e2-search-icon">
              <span class="e2-svgi">
                <svg width="16" height="16" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="m15.213 13.799-4.005-4.005A5.72 5.72 0 0 0 12.249 6.5a5.75 5.75 0 1 0-11.5 0 5.75 5.75 0 0 0 5.75 5.75 5.72 5.72 0 0 0 3.294-1.041l4.005 4.005 1.415-1.415ZM2.25 6.501a4.251 4.251 0 1 1 8.502 0 4.251 4.251 0 0 1-8.502 0Z" stroke="none"/>
                </svg>
              </span>
            </span>
          </label>
        </form>

      </span>
    </div>
  </div>
</div>
