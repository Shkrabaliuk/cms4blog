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

// Видалення поста
if (isset($_GET['delete']) && isset($_GET['csrf'])) {
    $id = intval($_GET['delete']);
    if (verify_csrf_token($_GET['csrf'])) {
        delete_post($id);
        header("Location: posts.php?deleted=1");
        exit;
    }
}

$page = max(1, intval($_GET['page'] ?? 1));
$search = $_GET['search'] ?? '';

// Отримати всі пости без ліміту з функції
$stmt = $pdo->prepare("
    SELECT * FROM posts 
    WHERE title LIKE ? OR content LIKE ? OR tags LIKE ?
    ORDER BY created_at DESC
");
$searchTerm = "%$search%";
$stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
$all_posts = $stmt->fetchAll();

$per_page = 20;
$total = count($all_posts);
$total_pages = ceil($total / $per_page);
$offset = ($page - 1) * $per_page;
$posts = array_slice($all_posts, $offset, $per_page);

$deleted = isset($_GET['deleted']);
$pageTitle = 'Керування постами';
require '../includes/templates/header.php';
?>
<link rel="stylesheet" href="/assets/css/admin.css">
<div class="content">
    <div class="e2-heading">
      <h2>Пости</h2>
    </div>

    <?php if ($deleted): ?>
      <div class="success">Пост видалено!</div>
    <?php endif; ?>

    <div class="mb-20">
      <a href="post-editor.php" class="e2-button">+ Створити новий пост</a>
    </div>

    <form method="GET" class="search-form">
      <input type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Пошук постів..." class="search-input">
      <button type="submit" class="e2-button">Знайти</button>
      <?php if ($search): ?>
        <a href="posts.php" class="ml-8">Очистити</a>
      <?php endif; ?>
    </form>

    <?php if (count($posts) > 0): ?>
      <table class="posts-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Назва</th>
            <th>Теги</th>
            <th>Дата</th>
            <th>Дії</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($posts as $post): ?>
            <tr>
              <td><?= $post['id'] ?></td>
              <td>
                <a href="../post.php?id=<?= $post['id'] ?>" target="_blank"><?= htmlspecialchars($post['title']) ?></a>
              </td>
              <td><?= htmlspecialchars($post['tags']) ?></td>
              <td><?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></td>
              <td>
                <div class="post-actions">
                  <a href="post-editor.php?id=<?= $post['id'] ?>">Редагувати</a>
                  <a href="?delete=<?= $post['id'] ?>&csrf=<?= generate_csrf_token() ?>" 
                     class="btn-delete" 
                     onclick="return confirm('Ви впевнені? Це також видалить всі коментарі до цього поста.')">Видалити</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <?php if ($total_pages > 1): ?>
        <div class="e2-pages mt-20">
          <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>">← Попередня</a>
          <?php endif; ?>
          
          <span>Сторінка <?= $page ?> з <?= $total_pages ?></span>
          
          <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>">Наступна →</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <p>Постів не знайдено. <a href="post-editor.php">Створити перший пост?</a></p>
    <?php endif; ?>
  </div>

<?php require '../includes/templates/footer.php'; ?>
