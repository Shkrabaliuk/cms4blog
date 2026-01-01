<?php
session_start();

if (!file_exists('../config.php')) {
    header("Location: ../install/install.php");
    exit;
}

require '../includes/db.php';
require '../includes/functions.php';

// Перевірка авторизації
if (!is_admin()) {
    header("Location: login.php");
    exit;
}

// Вихід
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}

$success = '';
$error_msg = '';

// Обробка налаштувань
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Перевірка CSRF токена
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error_msg = 'Невалідний запит';
    } else {
        $blog_name = trim($_POST['blog_name'] ?? '');
        $blog_subtitle = trim($_POST['blog_subtitle'] ?? '');
        $author_name = trim($_POST['author_name'] ?? '');
        $blog_description = trim($_POST['blog_description'] ?? '');
        $posts_per_page = intval($_POST['posts_per_page'] ?? 10);
        $show_view_counts = isset($_POST['show_view_counts']) ? 1 : 0;
        $footer_text = trim($_POST['footer_text'] ?? '');
        $footer_engine = trim($_POST['footer_engine'] ?? '');

    if (empty($blog_name)) {
        $error_msg = 'Назва блогу обов\'язкова';
    } else {
        set_setting('blog_name', $blog_name);
        set_setting('blog_subtitle', $blog_subtitle);
        set_setting('author_name', $author_name);
        set_setting('blog_description', $blog_description);
        set_setting('posts_per_page', $posts_per_page);
        set_setting('show_view_counts', $show_view_counts);
        set_setting('footer_text', $footer_text);
        set_setting('footer_engine', $footer_engine);

        // Завантаження аватара
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
                    $error_msg = 'Помилка завантаження';
                }
            } else {
                $error_msg = 'Тільки зображення';
            }
        }

        if (empty($error_msg)) {
            $success = 'Налаштування збережено!';
        }
    }
}

$blog_name = get_setting('blog_name', 'Мій Блог');
$blog_subtitle = get_setting('blog_subtitle', '');
$author_name = get_setting('author_name', '');
$blog_description = get_setting('blog_description', '');
$posts_per_page = get_setting('posts_per_page', 10);
$show_view_counts = get_setting('show_view_counts', 0);
$footer_text = get_setting('footer_text', 'Автор блогу');
$footer_engine = get_setting('footer_engine', 'Рушій — Егея');
$avatar = get_setting('avatar', '');
$pageTitle = 'Налаштування';
require '../includes/templates/header.php';
?>
<link rel="stylesheet" href="/assets/css/admin.css">
<div class="content">
    <div class="e2-heading">
      <span class="admin-links admin-links-floating">
        <span class="admin-icon">
          <a href="?logout=1" class="nu e2-admin-link" style="text-decoration: underline;">Вийти</a>
        </span>
      </span>
      <h2>Налаштування</h2>
    </div>

    <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
    <?php if ($error_msg): ?><div class="error"><?= $error_msg ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="form">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      <div class="form-part">
        <div class="form-control form-control-big">
          <div class="form-label input-label"><label>Назва блогу</label></div>
          <div class="form-element">
            <input type="text" name="blog_name" value="<?= htmlspecialchars($blog_name) ?>" required class="text width-4" autofocus>
          </div>
        </div>

        <div class="form-control">
          <div class="form-label input-label"><label>Підзаголовок</label></div>
          <div class="form-element">
            <textarea name="blog_subtitle" class="width-4 height-2 e2-textarea-autosize"><?= htmlspecialchars($blog_subtitle) ?></textarea>
          </div>
        </div>

        <div class="form-control form-control-big">
          <div class="form-label input-label"><label>Фото та ім'я автора</label></div>
          <div class="form-element">
            <?php if ($avatar): ?>
              <img src="<?= htmlspecialchars($avatar) ?>" class="avatar-preview" id="avatarPrev">
            <?php endif; ?>
            <input type="file" name="avatar" accept="image/*" id="avatarInput">
          </div>
        </div>

        <div class="form-control">
          <div class="form-element">
            <input type="text" name="author_name" value="<?= htmlspecialchars($author_name) ?>" class="text width-2" placeholder="Ім'я автора">
          </div>
        </div>

        <div class="form-control">
          <div class="form-label input-label"><label>Опис блогу</label></div>
          <div class="form-element">
            <textarea name="blog_description" class="width-4 height-2 e2-textarea-autosize" placeholder="Короткий опис для пошукових систем та соцмереж (SEO)"><?= htmlspecialchars($blog_description) ?></textarea>
            <div class="form-control-sublabel">Для пошукових систем, соцмереж і агрегаторів</div>
          </div>
        </div>
      </div>

      <div class="form-part">
        <div class="form-control">
          <div class="form-label input-label"><label>Кількість дописів на сторінку</label></div>
          <div class="form-element">
            <input type="number" name="posts_per_page" value="<?= $posts_per_page ?>" min="3" max="100" class="text" style="width: 80px;" pattern="[0-9]*" inputmode="numeric">
          </div>
          <div class="form-element">
            <label class="e2-switch">
              <input type="checkbox" name="show_view_counts" class="checkbox" <?= $show_view_counts ? 'checked' : '' ?>>
              <i></i> Показувати лічильники переглядів
            </label>
          </div>
        </div>
      </div>

      <div class="form-control">
        <div class="form-element">
          <button type="submit" class="e2-button e2-submit-button">Зберегти зміни</button>
          <span class="e2-keyboard-shortcut">Ctrl + Enter</span>
        </div>
      </div>
    </form>
  </div>

  <div class="footer">
    <a href="../index.php">← Повернутись на сайт</a>
  </div>
</div>

<script>
const avatarInput = document.getElementById('avatarInput');
if (avatarInput) {
  avatarInput.addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        let prev = document.getElementById('avatarPrev');
        if (!prev) {
          prev = document.createElement('img');
          prev.className = 'avatar-preview';
          prev.id = 'avatarPrev';
          avatarInput.parentElement.insertBefore(prev, avatarInput);
        }
        prev.src = e.target.result;
      }
      reader.readAsDataURL(e.target.files[0]);
    }
  });
}
</script>

<?php require '../includes/templates/footer.php'; ?>
