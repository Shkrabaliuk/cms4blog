<?php
// install.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $server = $_POST['server'] ?? 'localhost';
    $user = $_POST['username'] ?? 'root';
    $password = $_POST['password'] ?? '';
    $database = $_POST['database'] ?? '';
    
    try {
        $dsn = "mysql:host=$server;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database`");
        $pdo->exec("USE `$database`");
        
        // Створюємо таблицю постів
        $sql = "CREATE TABLE IF NOT EXISTS `posts` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) NOT NULL,
            `content` text NOT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $pdo->exec($sql);

        // Створюємо файл конфігурації
        $configContent = "<?php
define('DB_HOST', '$server');
define('DB_NAME', '$database');
define('DB_USER', '$user');
define('DB_PASS', '$password');
";
        if (file_put_contents('config.php', $configContent) === false) {
             throw new Exception("Не вдалося створити файл config.php. Перевірте права доступу.");
        }
        echo json_encode(['success' => true]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}
$alreadyInstalled = file_exists('config.php');
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Встановлення</title>
    <link rel="stylesheet" href="assets/css/install.css">
</head>
<body>
    <div class="container">
        <?php if ($alreadyInstalled): ?>
            <h1>Вже встановлено</h1>
            <p>Блог налаштований. Видаліть файл <code>config.php</code>, якщо хочете почати заново.</p>
            <a href="index.php"><button>Перейти до блогу</button></a>
        <?php else: ?>
            
            <h1>Встановлення</h1>
            <p class="subtitle">Параметри бази даних від вашого хостинг-провайдера:</p>
            
            <div class="error-message" id="errorMessage"></div>
            <div class="success-message" id="successMessage">✓ Успішно! Переходимо на сайт...</div>
            
            <form id="installForm">
                <div class="form-group">
                    <label>Сервер (Host)</label>
                    <input type="text" name="server" value="localhost" required>
                </div>
                
                <div class="form-group">
                    <label>Користувач та пароль БД</label>
                    <div class="double-input">
                        <input type="text" name="username" placeholder="root" required>
                        <input type="password" name="password" placeholder="Пароль (якщо є)">
                    </div>
                </div>

                <div class="form-group">
                    <label>Ім'я бази даних</label>
                    <input type="text" name="database" placeholder="my_blog" required>
                    <div class="hint">Якщо бази немає, ми спробуємо створити її автоматично.</div>
                </div>
                
                <div class="form-group" style="margin-top: 40px;">
                    <label>Придумайте пароль для входу в адмінку:</label>
                    <input type="password" name="admin_password" required>
                </div>
                
                <button type="submit" id="submitBtn">Почати блог</button>
                <span class="keyboard-hint">Ctrl + Enter</span>
            </form>
            
            <div class="loading" id="loading">Налаштування...</div>
        <?php endif; ?>
    </div>

    <script>
        const form = document.getElementById('installForm');
        if(form) {
            const submitBtn = document.getElementById('submitBtn');
            const errorMessage = document.getElementById('errorMessage');
            const successMessage = document.getElementById('successMessage');
            const loading = document.getElementById('loading');
            
            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    e.preventDefault();
                    form.requestSubmit();
                }
            });
            
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                errorMessage.classList.remove('show');
                loading.classList.add('show');
                submitBtn.disabled = true;
                
                const formData = new FormData(form);
                try {
                    const response = await fetch(window.location.href, { method: 'POST', body: formData });
                    const result = await response.json();
                    
                    loading.classList.remove('show');
                    if (result.success) {
                        successMessage.classList.add('show');
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        submitBtn.disabled = false;
                        errorMessage.textContent = result.error;
                        errorMessage.classList.add('show');
                    }
                } catch (error) {
                    loading.classList.remove('show');
                    submitBtn.disabled = false;
                    errorMessage.textContent = 'Помилка з\'єднання';
                    errorMessage.classList.add('show');
                }
            });
        }
    </script>
</body>
</html>