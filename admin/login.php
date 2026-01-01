<?php
session_start();

if (!file_exists('../config.php')) {
    header("Location: ../install/install.php");
    exit;
}

require '../includes/db.php';
require '../includes/functions.php';

// Якщо вже залогінений - перенаправити
if (is_admin()) {
    header("Location: settings.php");
    exit;
}

$error = '';
$userExists = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Перевірка CSRF токена
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Невалідний запит";
    } else {
        $password = $_POST['password'];

    if (!$userExists) {
        // Створення першого адміна
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (password) VALUES (?)");
        $stmt->execute([$hash]);
        $_SESSION['is_admin'] = true;
        header("Location: settings.php");
        exit;
    } else {
        // Звичайний логін
        $stmt = $pdo->query("SELECT * FROM users LIMIT 1");
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['is_admin'] = true;
            header("Location: settings.php");
            exit;
        } else {
            $error = "Невірний пароль";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Вхід</title>
<link rel="stylesheet" href="/assets/css/style.css">
<link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
<div class="login-wrapper">
  <div class="login-form">
    <h1><?= $userExists ? 'Вхід в адмінку' : 'Створення адміна' ?></h1>
    <?php if ($error): ?>
      <div class="error-message"><?= $error ?></div>
    <?php endif; ?>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      <input type="password" name="password" placeholder="Пароль" required autofocus>
      <button type="submit" class="e2-submit-button"><?= $userExists ? 'Увійти' : 'Створити' ?></button>
    </form>
    <p class="text-center mt-20"><a href="../index.php">← На головну</a></p>
  </div>
</div>
</body>
</html>
