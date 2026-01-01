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

<!-- Коментарі -->
<div class="comments-section" style="margin-top: 60px; max-width: 720px;">
  <h2 style="font-size: 24px; margin-bottom: 30px;">Коментарі</h2>
  
  <?php
  $comments = get_comments($post['id']);
  if (!empty($comments)):
  ?>
    <div class="comments-list">
      <?php foreach ($comments as $comment): ?>
        <div class="comment" style="margin-bottom: 30px; padding-bottom: 30px; border-bottom: 1px solid var(--thinRuleColor);">
          <div style="font-weight: bold; margin-bottom: 8px;">
            <?= htmlspecialchars($comment['author']) ?>
          </div>
          <div style="font-size: 13px; opacity: 0.6; margin-bottom: 12px;">
            <?= time_ago($comment['created_at']) ?>
          </div>
          <div style="line-height: 24px;">
            <?= nl2br(htmlspecialchars($comment['content'])) ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p style="opacity: 0.6; margin-bottom: 30px;">Поки що коментарів немає. Будьте першим!</p>
  <?php endif; ?>
  
  <h3 style="font-size: 20px; margin: 40px 0 20px;">Додати коментар</h3>
  <form action="/add-comment.php" method="POST" class="comment-form">
    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
    
    <div style="margin-bottom: 16px;">
      <label style="display: block; margin-bottom: 8px; font-weight: 500;">Ім'я:</label>
      <input type="text" name="author" required style="width: 100%; padding: 10px; border: 1px solid var(--thinRuleColor); border-radius: 6px; font-family: inherit; font-size: 16px;">
    </div>
    
    <div style="margin-bottom: 16px;">
      <label style="display: block; margin-bottom: 8px; font-weight: 500;">Коментар:</label>
      <textarea name="content" required rows="5" style="width: 100%; padding: 10px; border: 1px solid var(--thinRuleColor); border-radius: 6px; font-family: inherit; font-size: 16px; resize: vertical;"></textarea>
    </div>
    
    <button type="submit" class="e2-button">Відправити коментар</button>
  </form>
</div>

</div>

<?php require 'includes/templates/footer.php'; ?>
