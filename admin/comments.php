<?php
session_start();

if (!file_exists('../config.php')) {
    header("Location: ../install/install.php");
    exit;
}

require '../includes/db.php';
require '../includes/functions.php';

if (!is_admin()) {
    header("Location: login.php");
    exit;
}

// Обробка дій
if (isset($_POST['action']) && isset($_POST['comment_id']) && isset($_POST['csrf'])) {
    if (verify_csrf_token($_POST['csrf'])) {
        $comment_id = intval($_POST['comment_id']);
        $action = $_POST['action'];
        
        switch ($action) {
            case 'approve':
                moderate_comment($comment_id, 'approved');
                $message = 'Коментар схвалено!';
                break;
            case 'reject':
                moderate_comment($comment_id, 'rejected');
                $message = 'Коментар відхилено!';
                break;
            case 'delete':
                delete_comment($comment_id);
                $message = 'Коментар видалено!';
                break;
        }
    }
}

$filter = $_GET['filter'] ?? 'pending';
$allowed_filters = ['pending', 'approved', 'rejected', 'all'];
if (!in_array($filter, $allowed_filters)) {
    $filter = 'pending';
}

$comments = get_all_comments($filter === 'all' ? null : $filter);

// Отримати кількість коментарів по статусах
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM comments GROUP BY status");
$counts = [];
while ($row = $stmt->fetch()) {
    $counts[$row['status']] = $row['count'];
}

$pageTitle = 'Модерація коментарів';
require '../includes/templates/header.php';
?>
<link rel="stylesheet" href="/assets/css/admin.css">
<div class="content">
    <div class="e2-heading">
      <h2>Коментарі</h2>
    </div>

    <?php if (isset($message)): ?>
      <div class="success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="tabs">
      <a href="?filter=pending" class="<?= $filter === 'pending' ? 'active' : '' ?>">
        На модерації (<?= $counts['pending'] ?? 0 ?>)
      </a>
      <a href="?filter=approved" class="<?= $filter === 'approved' ? 'active' : '' ?>">
        Схвалені (<?= $counts['approved'] ?? 0 ?>)
      </a>
      <a href="?filter=rejected" class="<?= $filter === 'rejected' ? 'active' : '' ?>">
        Відхилені (<?= $counts['rejected'] ?? 0 ?>)
      </a>
      <a href="?filter=all" class="<?= $filter === 'all' ? 'active' : '' ?>">
        Всі
      </a>
    </div>

    <?php if (count($comments) > 0): ?>
      <?php foreach ($comments as $comment): ?>
        <div class="comment-item">
          <div class="comment-header">
            <div>
              <span class="comment-author"><?= htmlspecialchars($comment['author']) ?></span>
              <span class="comment-meta"> • <?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></span>
              <?php if ($filter === 'all'): ?>
                <span class="badge-<?= $comment['status'] ?>"><?= $comment['status'] ?></span>
              <?php endif; ?>
            </div>
          </div>
          
          <div class="comment-post">
            До поста: <a href="../post.php?id=<?= $comment['post_id'] ?>" target="_blank"><?= htmlspecialchars($comment['post_title'] ?? 'Без назви') ?></a>
          </div>
          
          <div class="comment-content">
            <?= nl2br(htmlspecialchars($comment['content'])) ?>
          </div>
          
          <div class="comment-actions">
            <?php if ($comment['status'] !== 'approved'): ?>
              <form method="POST" class="inline-form">
                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="csrf" value="<?= generate_csrf_token() ?>">
                <button type="submit" class="btn-approve">Схвалити</button>
              </form>
            <?php endif; ?>
            
            <?php if ($comment['status'] === 'pending'): ?>
              <form method="POST" class="inline-form">
                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="csrf" value="<?= generate_csrf_token() ?>">
                <button type="submit" class="btn-reject">Відхилити</button>
              </form>
            <?php endif; ?>
            
            <form method="POST" class="inline-form" onsubmit="return confirm('Видалити цей коментар назавжди?')">
              <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="csrf" value="<?= generate_csrf_token() ?>">
              <button type="submit" class="btn-delete">Видалити</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>Коментарів не знайдено.</p>
    <?php endif; ?>
  </div>

<?php require '../includes/templates/footer.php'; ?>
