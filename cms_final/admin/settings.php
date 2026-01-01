<?php
session_start();
require '../includes/db.php';
require '../includes/functions.php';

if (!is_admin()) {
    header("Location: admin.php");
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $blog_name = trim($_POST['blog_name'] ?? '');
    $blog_subtitle = trim($_POST['blog_subtitle'] ?? '');
    $posts_per_page = intval($_POST['posts_per_page'] ?? 10);
    $footer_text = trim($_POST['footer_text'] ?? '');
    $footer_engine = trim($_POST['footer_engine'] ?? '');

    if (empty($blog_name)) {
        $error = 'Назва блогу обов\'язкова';
    } else {
        set_setting('blog_name', $blog_name);
        set_setting('blog_subtitle', $blog_subtitle);
        set_setting('posts_per_page', $posts_per_page);
        set_setting('footer_text', $footer_text);
        set_setting('footer_engine', $footer_engine);

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['avatar']['type'];
            
            if (in_array($file_type, $allowed)) {
                $upload_dir = '../assets/images';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $filename = 'avatar_' . time() . '.' . $ext;
                $upload_path = $upload_dir . '/' . $filename;
                
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                    $old_avatar = get_setting('avatar');
                    if ($old_avatar && file_exists('..' . $old_avatar)) {
                        unlink('..' . $old_avatar);
                    }
                    set_setting('avatar', '/assets/images/' . $filename);
                } else {
                    $error = 'Помилка завантаження';
                }
            } else {
                $error = 'Тільки зображення';
            }
        }

        if (empty($error)) {
            $success = 'Збережено!';
        }
    }
}

$blog_name = get_setting('blog_name', 'Мій Блог');
$blog_subtitle = get_setting('blog_subtitle', '');
$posts_per_page = get_setting('posts_per_page', 10);
$footer_text = get_setting('footer_text', 'Автор блогу');
$footer_engine = get_setting('footer_engine', 'Рушій — Егея');
$avatar = get_setting('avatar', '');
?>
<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8">
<title>Налаштування</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.success { background: #e8f5e9; color: #2e7d32; padding: 12px; border-radius: 6px; margin-bottom: 20px; }
.error { background: #ffebee; color: #c62828; padding: 12px; border-radius: 6px; margin-bottom: 20px; }
.avatar-preview { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; background: var(--inputBackgroundColor); margin-bottom: 12px; }
</style>
</head>
<body>

<div class="common">
  <div class="flag">
    <div class="header-content">
      <div class="header-description">
        <div class="title">
          <h1><a href="../index.php"><?= htmlspecialchars($blog_name) ?></a></h1>
          <p>Налаштування</p>
        </div>
      </div>
      <div class="spotlight">
        <span class="admin-links-floating">
          <span class="admin-menu admin-links">
            <span class="admin-icon"><a href="admin.php" class="nu">← Назад</a></span>
          </span>
        </span>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="e2-heading">
      <h2>Налаштування блогу</h2>
    </div>

    <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="form">
      
      <div class="form-control">
        <div class="form-label"><label>Аватарка</label></div>
        <div class="form-element">
          <?php if ($avatar): ?>
            <img src="<?= htmlspecialchars($avatar) ?>" class="avatar-preview" id="avatarPrev">
          <?php else: ?>
            <div class="avatar-preview" id="avatarPrev"></div>
          <?php endif; ?>
          <input type="file" name="avatar" accept="image/*" id="avatarInput">
        </div>
      </div>

      <div class="form-control">
        <div class="form-label"><label>Назва блогу *</label></div>
        <div class="form-element">
          <input type="text" name="blog_name" value="<?= htmlspecialchars($blog_name) ?>" required class="width-4">
        </div>
      </div>

      <div class="form-control">
        <div class="form-label"><label>Підзаголовок</label></div>
        <div class="form-element">
          <input type="text" name="blog_subtitle" value="<?= htmlspecialchars($blog_subtitle) ?>" class="width-4">
        </div>
      </div>

      <div class="form-control">
        <div class="form-label"><label>Постів на сторінку</label></div>
        <div class="form-element">
          <select name="posts_per_page">
            <option value="5" <?= $posts_per_page == 5 ? 'selected' : '' ?>>5</option>
            <option value="10" <?= $posts_per_page == 10 ? 'selected' : '' ?>>10</option>
            <option value="15" <?= $posts_per_page == 15 ? 'selected' : '' ?>>15</option>
            <option value="20" <?= $posts_per_page == 20 ? 'selected' : '' ?>>20</option>
          </select>
        </div>
      </div>

      <div class="form-control">
        <div class="form-label"><label>Текст футера</label></div>
        <div class="form-element">
          <input type="text" name="footer_text" value="<?= htmlspecialchars($footer_text) ?>" class="width-4">
        </div>
      </div>

      <div class="form-control">
        <div class="form-label"><label>Назва рушія</label></div>
        <div class="form-element">
          <input type="text" name="footer_engine" value="<?= htmlspecialchars($footer_engine) ?>" class="width-4">
        </div>
      </div>

      <div class="form-control">
        <div class="form-label"></div>
        <div class="form-element">
          <button type="submit" class="e2-submit-button">Зберегти</button>
        </div>
      </div>

    </form>
  </div>
</div>

<script>
document.getElementById('avatarInput').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const prev = document.getElementById('avatarPrev');
            if (prev.tagName === 'IMG') {
                prev.src = e.target.result;
            } else {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'avatar-preview';
                img.id = 'avatarPrev';
                prev.replaceWith(img);
            }
        }
        reader.readAsDataURL(e.target.files[0]);
    }
});
</script>

</body>
</html>
