<?php
session_start();

if (file_exists('../config.php')) {
    header("Location: ../index.php");
    exit;
}

// Перевірка версії PHP
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die('Помилка: Потрібна версія PHP 7.4 або вище. Поточна версія: ' . PHP_VERSION);
}

// Перевірка необхідних розширень
$required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'fileinfo'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    die('Помилка: Відсутні необхідні PHP розширення: ' . implode(', ', $missing_extensions));
}

$error = '';
$success = '';
$databases = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['host'] ?? 'localhost';
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $dbname = $_POST['dbname'] ?? '';
    $blog_password = $_POST['blog_password'] ?? '';
    $drop_existing = isset($_POST['drop_existing']);

    if (empty($user) || empty($dbname) || empty($blog_password)) {
        $error = 'Заповніть всі поля';
    } else {
        try {
            $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // Перевірка версії MySQL
            $version = $pdo->query('SELECT VERSION()')->fetchColumn();
            if (version_compare($version, '5.7.0', '<')) {
                throw new Exception("Потрібна версія MySQL 5.7+ або MariaDB 10.2+. Поточна версія: $version");
            }

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$dbname`");

            // Видалення існуючих таблиць якщо вибрано
            if ($drop_existing) {
                $pdo->exec("DROP TABLE IF EXISTS `comments`");
                $pdo->exec("DROP TABLE IF EXISTS `posts`");
                $pdo->exec("DROP TABLE IF EXISTS `settings`");
                $pdo->exec("DROP TABLE IF EXISTS `users`");
            }

            // Таблиця користувачів
            $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `password` varchar(255) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Таблиця постів з тегами
            $pdo->exec("CREATE TABLE IF NOT EXISTS `posts` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL,
                `content` text NOT NULL,
                `tags` text,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Таблиця налаштувань
            $pdo->exec("CREATE TABLE IF NOT EXISTS `settings` (
                `key` varchar(100) NOT NULL,
                `value` text,
                PRIMARY KEY (`key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Таблиця коментарів
            $pdo->exec("CREATE TABLE IF NOT EXISTS `comments` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `post_id` int(11) NOT NULL,
                `author` varchar(100) NOT NULL,
                `content` text NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `post_id` (`post_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Створення користувача
            $hash = password_hash($blog_password, PASSWORD_DEFAULT);
            
            // Перевіряємо чи існує користувач
            $userExists = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            
            if ($userExists > 0) {
                // Оновлюємо пароль існуючого користувача
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = 1");
                $stmt->execute([$hash]);
            } else {
                // Створюємо нового користувача
                $stmt = $pdo->prepare("INSERT INTO users (password) VALUES (?)");
                $stmt->execute([$hash]);
            }

            // Дефолтні налаштування
            $defaults = [
                'blog_name' => 'Мій Блог',
                'blog_subtitle' => 'Підзаголовок',
                'author_name' => '',
                'blog_description' => '',
                'posts_per_page' => '10',
                'show_view_counts' => '0',
                'footer_text' => '© Автор блогу',
                'footer_engine' => 'Рушій — Мій',
                'avatar' => ''
            ];
            
            foreach ($defaults as $key => $value) {
                $stmt = $pdo->prepare("REPLACE INTO settings (`key`, value) VALUES (?, ?)");
                $stmt->execute([$key, $value]);
            }

            // Створення config.php
            $config = "<?php\ndefine('DB_HOST', '$host');\ndefine('DB_NAME', '$dbname');\ndefine('DB_USER', '$user');\ndefine('DB_PASS', '$pass');\n";
            file_put_contents('../config.php', $config);

            $success = 'Встановлення завершено! Перенаправлення...';
            header("refresh:2;url=../admin/login.php");
        } catch (Exception $e) {
            $error = 'Помилка: ' . $e->getMessage();
        }
    }
}

// Отримання списку БД для dropdown
if (isset($_POST['get_databases'])) {
    $host = $_POST['host'] ?? 'localhost';
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    
    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
        $stmt = $pdo->query("SHOW DATABASES");
        $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode(['success' => true, 'databases' => $databases]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Встановлення блогу</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<div class="install-container">
    <div class="install-icon">
        <svg width="80" height="80" viewBox="0 0 80 80" fill="none">
            <circle cx="40" cy="40" r="40" fill="#F4B942"/>
            <path d="M40 20 L45 35 L60 35 L48 45 L53 60 L40 50 L27 60 L32 45 L20 35 L35 35 Z" fill="white"/>
        </svg>
    </div>

    <h1>Встановлення</h1>

    <?php if ($error): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success-message"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" id="installForm">
        <div class="install-section">
            <h2>Database parameters that your hosting provider has given you:</h2>
            
            <div class="form-group">
                <label>Server</label>
                <input type="text" name="host" value="localhost" readonly>
                <div class="form-hint">Зазвичай це localhost, не змінюйте</div>
            </div>

            <div class="form-group">
                <label>User name and password</label>
                <input type="text" name="user" placeholder="root" required>
                <input type="password" name="pass" placeholder="Пароль (може бути порожнім)" style="margin-top: 8px;">
                <div class="form-hint">Отримайте ці дані у вашого хостинг-провайдера</div>
            </div>

            <div class="form-group">
                <label>Database name</label>
                <div class="db-selector">
                    <input type="text" name="dbname" id="dbnameInput" placeholder="Натисніть щоб вибрати..." onclick="loadDatabases()" required>
                    <div class="db-dropdown" id="dbDropdown"></div>
                </div>
                <div class="form-hint">Виберіть існуючу БД або введіть нову назву (створить автоматично)</div>
            </div>
        </div>

        <div class="install-section">
            <h2>Password you'd like to use to access your blog:</h2>
            
            <div class="form-group">
                <input type="password" name="blog_password" placeholder="Придумайте надійний пароль" required minlength="6">
                <div class="form-hint">Мінімум 6 символів. Запам'ятайте його!</div>
            </div>

            <div class="form-group">
                <label class="e2-switch" style="margin-top: 16px;">
                    <input type="checkbox" name="drop_existing" class="checkbox">
                    <i></i> Видалити існуючі дані (якщо база не порожня)
                </label>
                <div class="form-hint" style="color: #d32f2f; margin-top: 8px;">⚠️ Увага! Це видалить всі пости, коментарі та налаштування з бази даних</div>
            </div>
        </div>

        <button type="submit" class="install-button" id="submitBtn">
            <span>Start blogging</span>
            <span style="font-size: 12px; opacity: 0.7;">Ctrl + Enter</span>
        </button>
    </form>
</div>

<script>
let databases = [];

async function loadDatabases() {
    const host = document.querySelector('input[name="host"]').value;
    const user = document.querySelector('input[name="user"]').value;
    const pass = document.querySelector('input[name="pass"]').value;
    
    if (!user) {
        alert('Спочатку введіть User name');
        return;
    }
    
    const formData = new FormData();
    formData.append('get_databases', '1');
    formData.append('host', host);
    formData.append('user', user);
    formData.append('pass', pass);
    
    try {
        const response = await fetch('', { method: 'POST', body: formData });
        const data = await response.json();
        
        if (data.success) {
            databases = data.databases;
            showDropdown();
        } else {
            alert('Помилка підключення: ' + data.error);
        }
    } catch (e) {
        alert('Помилка: ' + e.message);
    }
}

function showDropdown() {
    const dropdown = document.getElementById('dbDropdown');
    dropdown.innerHTML = '';
    
    if (databases.length === 0) {
        dropdown.innerHTML = '<div class="db-option" style="color: #999;">Баз даних не знайдено</div>';
    } else {
        databases.forEach(db => {
            if (!['information_schema', 'mysql', 'performance_schema', 'sys'].includes(db)) {
                const option = document.createElement('div');
                option.className = 'db-option';
                option.textContent = db;
                option.onclick = () => selectDatabase(db);
                dropdown.appendChild(option);
            }
        });
    }
    
    dropdown.classList.add('active');
}

function selectDatabase(dbname) {
    document.getElementById('dbnameInput').value = dbname;
    document.getElementById('dbDropdown').classList.remove('active');
}

document.addEventListener('click', function(e) {
    const selector = document.querySelector('.db-selector');
    if (!selector.contains(e.target)) {
        document.getElementById('dbDropdown').classList.remove('active');
    }
});

document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        document.getElementById('installForm').submit();
    }
});
</script>

</body>
</html>
