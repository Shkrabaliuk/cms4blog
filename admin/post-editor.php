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

$id = $_GET['id'] ?? null;
$success = '';
$error = '';

// Отримання поста для редагування
if ($id) {
    $post = get_post($id);
    if (!$post) {
        header("Location: posts.php");
        exit;
    }
} else {
    $post = ['id' => null, 'title' => '', 'content' => '', 'tags' => ''];
}

// Обробка форми
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Невалідний запит';
    } else {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $tags = trim($_POST['tags'] ?? '');
        
        if (empty($title) || empty($content)) {
            $error = 'Заповніть всі обов\'язкові поля';
        } else {
            if ($id) {
                // Оновлення
                if (update_post($id, $title, $content, $tags)) {
                    $success = 'Пост оновлено!';
                    $post = get_post($id); // Оновити дані
                } else {
                    $error = 'Помилка оновлення';
                }
            } else {
                // Створення
                if (create_post($title, $content, $tags)) {
                    $success = 'Пост створено!';
                    $post = ['id' => null, 'title' => '', 'content' => '', 'tags' => ''];
                } else {
                    $error = 'Помилка створення';
                }
            }
        }
    }
}

$pageTitle = $id ? 'Редагувати пост' : 'Новий пост';
require '../includes/templates/header.php';
?>
<link rel="stylesheet" href="/assets/css/admin.css">
<div class="content">
    <div class="e2-heading">
      <h2><?= $id ? 'Редагувати пост' : 'Створити пост' ?></h2>
    </div>

    <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>

    <form method="POST" class="form">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      
      <div class="form-part">
        <div class="form-control form-control-big">
          <div class="form-label input-label"><label>Назва поста *</label></div>
          <div class="form-element">
            <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" required class="text width-4" autofocus>
          </div>
        </div>

        <div class="form-control">
          <div class="form-label input-label"><label>Контент * (підтримується Markdown)</label></div>
          <div class="form-element">
            <textarea name="content" required class="width-4"><?= htmlspecialchars($post['content']) ?></textarea>
          </div>
        </div>

        <div class="form-control">
          <div class="form-label input-label"><label>Теги (через кому)</label></div>
          <div class="form-element">
            <input type="text" name="tags" value="<?= htmlspecialchars($post['tags']) ?>" class="text width-4" placeholder="php, веб-розробка, cms">
          </div>
        </div>
      </div>

      <div class="form-control">
        <div class="form-element">
          <button type="submit" class="e2-button e2-submit-button"><?= $id ? 'Оновити пост' : 'Створити пост' ?></button>
          <a href="posts.php" class="ml-16">Скасувати</a>
        </div>
      </div>
    </form>
  </div>

<?php require '../includes/templates/footer.php'; ?>
