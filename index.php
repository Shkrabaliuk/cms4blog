<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));

$posts = get_posts($search, 'DESC', $page);
$total = get_total_posts($search);
$per_page = (int)get_setting('posts_per_page', 10);
$total_pages = ceil($total / $per_page);

$pageTitle = $search ? "Пошук: $search" : "";
require 'includes/templates/header.php';
?>

<div class="content">

<?php if ($page > 1): ?>
  <div class="e2-pages">
    <a href="?<?= $search ? 'search=' . urlencode($search) . '&' : '' ?>page=<?= $page - 1 ?>">Пізніше</a>
  </div>
<?php endif; ?>

<?php if (count($posts) > 0): ?>
  <?php foreach ($posts as $post): ?>
    <div class="e2-note">

      <article>
        <h1><a href="/post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h1>

        <div class="e2-note-text">
          <?= markdown_excerpt($post['content'], 300) ?>
        </div>
      </article>

      <div class="e2-note-meta">
        <span><?= time_ago($post['created_at']) ?></span>
        <!-- <span><?= estimate_reading_time($post['content']) ?> хв</span> -->
        <?php if (!empty($post['tags'])): ?>
          <?php foreach (parse_tags($post['tags']) as $tag): ?>
            <a href="?search=<?= urlencode($tag) ?>" class="e2-tag"><?= htmlspecialchars($tag) ?></a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </div>
  <?php endforeach; ?>

  <?php if ($page < $total_pages): ?>
    <div class="e2-pages">
      <a href="?<?= $search ? 'search=' . urlencode($search) . '&' : '' ?>page=<?= $page + 1 ?>">Раніше</a>
    </div>
  <?php endif; ?>

<?php else: ?>
  <div class="empty-state">
    <?php if ($search): ?>
      <p>Нічого не знайдено за запитом "<?= htmlspecialchars($search) ?>"</p>
      <p><a href="/index.php">Показати всі пости</a></p>
    <?php else: ?>
      <p>Тут поки порожньо</p>
    <?php endif; ?>
  </div>
<?php endif; ?>

</div>

<?php require 'includes/templates/footer.php'; ?>
