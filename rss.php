<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$blog_name = get_setting('blog_name', 'Мій Блог');
$blog_subtitle = get_setting('blog_subtitle', '');
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];

// Отримуємо останні 20 постів
$posts = get_posts('', 'DESC', 1);
$stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC LIMIT 20");
$posts = $stmt->fetchAll();

header('Content-Type: application/rss+xml; charset=utf-8');

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title><?= htmlspecialchars($blog_name) ?></title>
    <link><?= $base_url ?>/</link>
    <description><?= htmlspecialchars($blog_subtitle ?: 'Персональний блог') ?></description>
    <language>uk</language>
    <lastBuildDate><?= date('r') ?></lastBuildDate>
    <atom:link href="<?= $base_url ?>/rss.php" rel="self" type="application/rss+xml" />
    
    <?php foreach ($posts as $post): ?>
    <item>
      <title><?= htmlspecialchars($post['title']) ?></title>
      <link><?= $base_url ?>/post.php?id=<?= $post['id'] ?></link>
      <guid><?= $base_url ?>/post.php?id=<?= $post['id'] ?></guid>
      <pubDate><?= date('r', strtotime($post['created_at'])) ?></pubDate>
      <description><![CDATA[<?= nl2br(htmlspecialchars(excerpt($post['content'], 500))) ?>]]></description>
      <?php if (!empty($post['tags'])): ?>
        <?php foreach (parse_tags($post['tags']) as $tag): ?>
      <category><?= htmlspecialchars($tag) ?></category>
        <?php endforeach; ?>
      <?php endif; ?>
    </item>
    <?php endforeach; ?>
    
  </channel>
</rss>
