<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /index.php");
    exit;
}

$post = get_post($id);
if (!$post) {
    header("Location: /404.php");
    exit;
}

$pageTitle = $post['title'];
require 'includes/templates/header.php';
?>

<div class="content">

<div class="e2-note">
  
  <?php if (is_admin()): ?>
    <span class="admin-links-sticky">
      <span class="admin-icon">
        <a href="/admin/post-editor.php?id=<?= $post['id'] ?>" class="nu">
          <span class="e2-svgi">
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
              <path stroke="none" fill-rule="evenodd" clip-rule="evenodd" d="m10.5 2.5-8 8L1 15l4.5-1.5 8-8-3-3zm-5.25 9.25-1-1L4 10.5l6.75-6.75 1.5 1.5L5.5 12l-.25-.25z"/>
              <path stroke="none" fill-rule="evenodd" clip-rule="evenodd" d="M13.999 2c-1.5-1.5-3 0-3 0l-1 1 3 3 1-1c.001 0 1.501-1.5 0-3zm-.749 2.25L13 4.5 11.5 3l.25-.25s.78-.719 1.499 0 .001 1.5.001 1.5z"/>
            </svg>
          </span>
        </a>
      </span>
    </span>
  <?php endif; ?>

  <article>
    <h1><?= htmlspecialchars($post['title']) ?></h1>

    <div class="e2-note-text">
      <?= markdown($post['content']) ?>
    </div>
  </article>

  <div class="e2-note-meta">
    <span><?= time_ago($post['created_at']) ?></span>
    <span><?= estimate_reading_time($post['content']) ?> хв читання</span>
    <?php if (!empty($post['tags'])): ?>
      <?php foreach (parse_tags($post['tags']) as $tag): ?>
        <a href="/?search=<?= urlencode($tag) ?>" class="e2-tag"><?= htmlspecialchars($tag) ?></a>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>

<div class="e2-pages" style="margin-top: 40px;">
  <a href="/index.php">← Назад до всіх постів</a>
</div>

</div>

<?php require 'includes/templates/footer.php'; ?>
