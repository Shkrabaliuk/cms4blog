<?php
session_start();

if (!file_exists('../config.php')) {
    header("Location: ../install/install.php");
    exit;
}

require '../includes/db.php';
require '../includes/functions.php';

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}

$userExists = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $password = $_POST['password'];

    if (!$userExists) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (password) VALUES (?)");
        $stmt->execute([$hash]);
        $_SESSION['is_admin'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $stmt = $pdo->query("SELECT * FROM users LIMIT 1");
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['is_admin'] = true;
            header("Location: admin.php");
            exit;
        } else {
            $error = "Невірний пароль";
        }
    }
}

if (!is_admin()) {
?>
<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8">
<title>Вхід</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
.login-wrapper { display: flex; align-items: center; justify-content: center; min-height: 100vh; }
.login-form { max-width: 400px; width: 100%; padding: 40px; }
.login-form h1 { margin-bottom: 24px; text-align: center; }
.login-form input { width: 100%; margin-bottom: 16px; }
.login-form .e2-submit-button { width: 100%; }
.error-message { background: #ffebee; color: #c62828; padding: 12px; border-radius: 6px; margin-bottom: 16px; }
</style>
</head>
<body>
<div class="login-wrapper">
  <div class="login-form">
    <h1><?= $userExists ? 'Вхід в адмінку' : 'Створення адміна' ?></h1>
    <?php if (isset($error)): ?>
      <div class="error-message"><?= $error ?></div>
    <?php endif; ?>
    <form method="POST">
      <input type="password" name="password" placeholder="Пароль" required autofocus>
      <button type="submit" class="e2-submit-button"><?= $userExists ? 'Увійти' : 'Створити' ?></button>
    </form>
    <p style="text-align: center; margin-top: 20px;"><a href="../index.php">← На головну</a></p>
  </div>
</div>
</body>
</html>
<?php
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: admin.php");
    exit;
}

$posts = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC")->fetchAll();
$blog_name = get_setting('blog_name', 'Адмін-панель');
$pageTitle = "Адмін-панель";
?>
<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8">
<title>Адмін-панель</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="common">
  <div class="flag">
    <div class="header-content">
      <div class="header-description">
        <div class="title">
          <h1><a href="../index.php"><?= htmlspecialchars($blog_name) ?></a></h1>
          <p>Адмін-панель</p>
        </div>
      </div>
      <div class="spotlight">
        <span class="admin-links-floating">
          <span class="admin-menu admin-links">
            <span class="admin-icon"><a href="post-editor.php" class="nu">Новий пост</a></span>
            <span class="admin-icon"><a href="settings.php" class="nu">Налаштування</a></span>
            <span class="admin-icon"><a href="?logout=1" class="nu">Вийти</a></span>
          </span>
        </span>
      </div>
    </div>
  </div>

  <div class="content">
    <div class="e2-heading">
      <h2>Всі пости (<?= count($posts) ?>)</h2>
    </div>

    <?php if (empty($posts)): ?>
      <div class="empty-state">
        <p>Немає постів</p>
        <p><a href="post-editor.php" class="e2-button" style="display: inline-block; margin-top: 20px;">Створити перший пост</a></p>
      </div>
    <?php else: ?>
      <?php foreach ($posts as $post): ?>
        <div class="e2-note">
          <span class="admin-links-sticky">
            <span class="admin-icon">
              <a href="post-editor.php?id=<?= $post['id'] ?>" class="nu" title="Редагувати">
                <span class="e2-svgi">
                  <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                    <path stroke="none" fill-rule="evenodd" clip-rule="evenodd" d="m10.5 2.5-8 8L1 15l4.5-1.5 8-8-3-3zm-5.25 9.25-1-1L4 10.5l6.75-6.75 1.5 1.5L5.5 12l-.25-.25z"/>
                    <path stroke="none" fill-rule="evenodd" clip-rule="evenodd" d="M13.999 2c-1.5-1.5-3 0-3 0l-1 1 3 3 1-1c.001 0 1.501-1.5 0-3zm-.749 2.25L13 4.5 11.5 3l.25-.25s.78-.719 1.499 0 .001 1.5.001 1.5z"/>
                  </svg>
                </span>
              </a>
            </span>
            <span class="admin-icon">
              <a href="?delete=<?= $post['id'] ?>" onclick="return confirm('Видалити пост?')" class="nu" title="Видалити" style="color: var(--errorColor);">
                <span class="e2-svgi">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                    <path stroke="none" d="M2 3h12v1H2zm2 2h8v9H4z"/>
                  </svg>
                </span>
              </a>
            </span>
          </span>

          <article>
            <h1><a href="../post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h1>
          </article>

          <div class="e2-note-meta">
            <span><?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></span>
            <?php if (!empty($post['tags'])): ?>
              <?php foreach (parse_tags($post['tags']) as $tag): ?>
                <a href="../?search=<?= urlencode($tag) ?>" class="e2-tag"><?= htmlspecialchars($tag) ?></a>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="footer">
    <a href="../index.php">← Повернутись на сайт</a>
  </div>
</div>

</body>
</html>
