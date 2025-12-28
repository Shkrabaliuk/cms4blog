<?php
session_start();
require 'config.php';

// 1. ПІДКЛЮЧЕННЯ ДО БД
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Помилка БД: " . $e->getMessage());
}

// 2. АВТОМАТИЧНЕ СТВОРЕННЯ ТАБЛИЦІ КОРИСТУВАЧІВ (якщо її немає)
$pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL,
    `password` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// 3. ЛОГІКА: ВИХІД
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// 4. ЛОГІКА: РЕЄСТРАЦІЯ (якщо база порожня) АБО ВХІД
$userExists = $pdo->query("SELECT count(*) FROM users")->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!$userExists) {
        // Реєстрація першого адміна
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hash]);
        $_SESSION['user_id'] = $pdo->lastInsertId();
        header("Location: admin.php");
        exit;
    } else {
        // Вхід
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: admin.php");
            exit;
        } else {
            $error = "Невірний логін або пароль";
        }
    }
}

// 5. ЯКЩО НЕ ЗАЛОГІНЕНИЙ — ПОКАЗУЄМО ФОРМУ ВХОДУ/РЕЄСТРАЦІЇ
if (!isset($_SESSION['user_id'])) {
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Вхід в адмінку</title>
    <link rel="stylesheet" href="assets/css/install.css">
</head>
<body>
    <div class="container">
        <h1><?= $userExists ? 'Вхід' : 'Створення адміна' ?></h1>
        
        <?php if (!$userExists): ?>
            <p class="subtitle">Це перший запуск. Придумайте логін і пароль для доступу.</p>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error-message show"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Логін</label>
                <input type="text" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit"><?= $userExists ? 'Увійти' : 'Створити акаунт' ?></button>
        </form>
        <p style="margin-top: 20px; text-align: center;">
            <a href="index.php" style="color: #888; text-decoration: none;">← На головну</a>
        </p>
    </div>
</body>
</html>
<?php
    exit; // Зупиняємо виконання, щоб не показати адмінку
}

// ---------------------------------------------------------
// ТУТ ПОЧИНАЄТЬСЯ АДМІНКА (ТІЛЬКИ ДЛЯ АВТОРИЗОВАНИХ)
// ---------------------------------------------------------

// Видалення поста
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: admin.php");
    exit;
}

// Отримуємо список постів
$posts = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Адмін-панель</title>
    <link rel="stylesheet" href="assets/css/style.css"> <style>
        /* Додаткові стилі для адмінки */
        .admin-bar {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 40px; padding-bottom: 20px; border-bottom: 1px solid #eee;
        }
        .btn {
            background: #e67e45; color: white; padding: 10px 20px; 
            text-decoration: none; border-radius: 4px; font-weight: 500;
        }
        .btn:hover { background: #d66e35; }
        .btn-small { padding: 4px 8px; font-size: 12px; margin-left: 10px; color: #d00; }
        .post-item { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .post-title { font-size: 18px; font-weight: bold; text-decoration: none; color: #000; }
        .post-date { color: #999; font-size: 13px; margin-right: 15px; }
    </style>
</head>
<body>

<header>
    <div class="admin-bar" style="width: 100%;">
        <div>
            <span style="font-weight: bold;">Адмін-панель</span>
        </div>
        <div>
            <a href="index.php" style="margin-right: 15px; text-decoration: none; color: #2a7ae2;">Перейти на сайт</a>
            <a href="?logout=1" style="color: #999; text-decoration: none;">Вийти</a>
        </div>
    </div>
</header>

<main>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 style="margin: 0;">Ваші пости</h1>
        <a href="edit.php" class="btn">Написати пост</a>
    </div>

    <?php foreach ($posts as $post): ?>
        <div class="post-item">
            <div>
                <a href="#" class="post-title"><?= htmlspecialchars($post['title']) ?></a>
                <div class="meta"><?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></div>
            </div>
            <div>
                <a href="edit.php?id=<?= $post['id'] ?>" style="color: #2a7ae2; margin-right: 10px;">Ред.</a>
                <a href="?delete=<?= $post['id'] ?>" onclick="return confirm('Видалити цей пост?')" style="color: #d00;">Вид.</a>
            </div>
        </div>
        <hr style="border: 0; border-top: 1px solid #f5f5f5; margin: 15px 0;">
    <?php endforeach; ?>
    
    <?php if (empty($posts)): ?>
        <p style="text-align: center; color: #999; margin-top: 50px;">У вас ще немає постів.</p>
    <?php endif; ?>
</main>

</body>
</html>