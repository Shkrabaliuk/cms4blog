<?php
session_start();
require '../includes/db.php';
require '../includes/functions.php';

if (!is_admin()) {
    header("Location: admin.php");
    exit;
}

$id = $_GET['id'] ?? null;
$post = ['title' => '', 'content' => '', 'tags' => ''];

if ($id) {
    $post = get_post($id);
    if (!$post) {
        header("Location: admin.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $tags = trim($_POST['tags']);

    if ($id) {
        $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, tags = ? WHERE id = ?");
        $stmt->execute([$title, $content, $tags, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO posts (title, content, tags) VALUES (?, ?, ?)");
        $stmt->execute([$title, $content, $tags]);
    }

    header("Location: admin.php");
    exit;
}

$blog_name = get_setting('blog_name', 'Блог');
?>
<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8">
<title><?= $id ? 'Редагування' : 'Новий пост' ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="common">
  <div class="flag">
    <div class="header-content">
      <div class="header-description">
        <div class="title">
          <h1><a href="../index.php"><?= htmlspecialchars($blog_name) ?></a></h1>
          <p><?= $id ? 'Редагування' : 'Новий пост' ?></p>
        </div>
      </div>
      <div class="spotlight">
        <span class="admin-links-floating">
          <span class="admin-menu admin-links">
            <span class="admin-icon"><a href="admin.php" class="nu">← Скасувати</a></span>
          </span>
        </span>
      </div>
    </div>
  </div>

  <div class="content">
    <form method="POST" class="form">
      <div class="form-control">
        <div class="form-label"><label>Заголовок</label></div>
        <div class="form-element">
          <input type="text" 
                 name="title" 
                 value="<?= htmlspecialchars($post['title']) ?>" 
                 placeholder="Заголовок поста..."
                 required
                 autofocus
                 class="width-4"
                 style="font-size: 20px;">
        </div>
      </div>

      <div class="form-control">
        <div class="form-label"><label>Теги</label></div>
        <div class="form-element">
          <input type="text" 
                 name="tags" 
                 value="<?= htmlspecialchars($post['tags']) ?>" 
                 placeholder="тег1, тег2, тег3"
                 class="width-4">
          <p style="font-size: 13px; color: var(--foregroundColor); opacity: 0.6; margin-top: 4px;">Розділяйте теги комами</p>
        </div>
      </div>

      <div class="form-control">
        <div class="form-label"><label>Контент</label></div>
        <div class="form-element">
          <textarea name="content" 
                    placeholder="Текст вашого поста..." 
                    required
                    class="height-8 width-4"><?= htmlspecialchars($post['content']) ?></textarea>
          <p style="font-size: 13px; color: var(--foregroundColor); opacity: 0.6; margin-top: 4px;">Markdown: **жирний**, *курсив*, # заголовок, [текст](url)</p>
        </div>
      </div>

      <div class="form-control">
        <div class="form-label"></div>
        <div class="form-element">
          <button type="submit" class="e2-submit-button">Зберегти</button>
          <a href="admin.php" class="e2-button" style="margin-left: 12px;">Скасувати</a>
        </div>
      </div>
    </form>
  </div>
</div>

</body>
</html>
