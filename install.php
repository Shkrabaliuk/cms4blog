<?php
// install.php - One-Click Installer for Logos CMS

// Prevent access if installed
if (file_exists(__DIR__ . '/src/Config/db.php')) {
    header('Location: /');
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = $_POST['db_host'] ?? 'localhost';
    $dbName = $_POST['db_name'] ?? '';
    $dbUser = $_POST['db_user'] ?? '';
    $dbPass = $_POST['db_pass'] ?? '';

    $adminEmail = $_POST['admin_email'] ?? '';
    $adminPass = $_POST['admin_pass'] ?? '';
    $siteTitle = $_POST['site_title'] ?? 'My Blog';

    try {
        // 1. Check Connection
        $dsn = "mysql:host=$dbHost;charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        // Create Database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbName`");

        // 2. Import SQL Logic
        $sqlPath = __DIR__ . '/storage/database.sql';
        if (!file_exists($sqlPath)) {
            throw new Exception("Schema file not found: $sqlPath");
        }
        $sql = file_get_contents($sqlPath);

        // Disable foreign keys for import
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $queries = explode(';', $sql);
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                $pdo->exec($query);
            }
        }
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

        // 3. Create Admin User
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, role) VALUES (?, ?, 'admin')");
        $hash = password_hash($adminPass, PASSWORD_DEFAULT);
        $stmt->execute([$adminEmail, $hash]);

        // 4. Update Settings
        $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
        $stmt->execute(['site_title', $siteTitle]);

        // 5. Write Config File
        $configDir = __DIR__ . '/src/Config';
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }

        $configContent = "<?php\n\nreturn [\n" .
            "    'host' => '" . addslashes($dbHost) . "',\n" .
            "    'dbname' => '" . addslashes($dbName) . "',\n" .
            "    'user' => '" . addslashes($dbUser) . "',\n" .
            "    'pass' => '" . addslashes($dbPass) . "',\n" .
            "];\n";

        if (!file_put_contents(__DIR__ . '/src/Config/db.php', $configContent)) {
            throw new Exception("Could not write src/Config/db.php");
        }

        // Success redirect
        header("Location: /?installed=true");
        exit;

    } catch (Exception $e) {
        $message = "Помилка встановлення: " . $e->getMessage();
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="uk">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Встановлення /\ogos</title>
    <link rel="stylesheet" href="/assets/css/base.css">
    <style>
        body {
            max-width: 600px;
        }

        .install-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .install-header h1 {
            margin-top: 0;
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }

        .install-header p {
            color: #999;
            font-size: 1.1rem;
        }

        .section-intro {
            background: var(--sheetBackgroundColor);
            padding: 1.5rem;
            border-radius: var(--borderRadius);
            margin-bottom: 2rem;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .step-group {
            margin-bottom: 3rem;
        }

        .step-group h2 {
            margin-top: 0;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .step-group > p {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        label {
            font-weight: 600;
        }

        small {
            display: block;
            margin-top: 0.3rem;
            color: #999;
        }

        .error-message {
            background: #fff0f0;
            color: #d00;
            padding: 1rem;
            border-radius: var(--borderRadius);
            border: 1px solid #ffc0c0;
            margin-bottom: 2rem;
        }
    </style>
</head>

<body>
    <div class="install-header">
        <h1>/\ogos</h1>
        <p>Мінімалістична CMS</p>
    </div>

    <div class="section-intro">
        <p><strong>Вітаємо!</strong> Цей майстер допоможе вам налаштувати блог за кілька хвилин.</p>
        <p>Вам знадобиться:</p>
        <ul>
            <li>MySQL база даних (або MariaDB)</li>
            <li>Дані для підключення (хост, користувач, пароль)</li>
            <li>Email та пароль для адміністратора</li>
        </ul>
    </div>

    <?php if ($message): ?>
        <div class="error-message">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="step-group">
            <h2>1. База даних</h2>
            <p>Введіть дані для підключення до вашої MySQL бази даних. Якщо база не існує, вона буде створена автоматично.</p>

            <label for="db_host">
                Хост
                <input type="text" id="db_host" name="db_host" value="localhost" required>
                <small>Зазвичай <code>localhost</code> або <code>127.0.0.1</code></small>
            </label>

            <label for="db_name">
                Назва бази даних
                <input type="text" id="db_name" name="db_name" required placeholder="logos_blog">
                <small>Буде створена, якщо не існує</small>
            </label>

            <label for="db_user">
                Користувач
                <input type="text" id="db_user" name="db_user" required placeholder="root">
            </label>

            <label for="db_pass">
                Пароль
                <input type="password" id="db_pass" name="db_pass" placeholder="Залиште порожнім, якщо немає">
            </label>
        </div>

        <div class="step-group">
            <h2>2. Адміністратор</h2>
            <p>Створіть обліковий запис адміністратора. Ви зможете використовувати ці дані для входу в панель керування.</p>

            <label for="admin_email">
                Email
                <input type="email" id="admin_email" name="admin_email" required placeholder="admin@example.com">
                <small>Використовується для входу в систему</small>
            </label>

            <label for="admin_pass">
                Пароль
                <input type="password" id="admin_pass" name="admin_pass" required minlength="3">
                <small>Мінімум 3 символи (краще використовувати складний пароль)</small>
            </label>
        </div>

        <div class="step-group">
            <h2>3. Налаштування сайту</h2>
            <p>Оберіть назву для вашого блогу. Ви зможете змінити її пізніше в налаштуваннях.</p>

            <label for="site_title">
                Назва блогу
                <input type="text" id="site_title" name="site_title" value="/\ogos" required>
            </label>
        </div>

        <button type="submit">Встановити</button>
    </form>
</body>

</html>