<?php
/**
 * Сторінка налаштувань блогу
 * Доступна тільки для авторизованих адмінів
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

// Перевіряємо авторизацію
requireAuth();

$success = null;
$error = null;

// Завантажуємо поточні налаштування
$stmt = $pdo->query("SELECT `key`, `value` FROM settings");
$settings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['key']] = $row['value'];
}

// Обробка форми
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Перевірка CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Невірний CSRF токен';
    } else {
        // Перевіряємо, яка форма відправлена
        if (isset($_POST['change_password'])) {
            // Форма зміни пароля
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $error = 'Заповніть всі поля';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'Нові паролі не збігаються';
            } elseif (strlen($newPassword) < 3) {
                $error = 'Пароль занадто короткий (мінімум 3 символи)';
            } else {
                try {
                    // Перевіряємо поточний пароль
                    $stmt = $pdo->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
                    $stmt->execute([$_SESSION['admin_id']]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
                        $error = 'Невірний поточний пароль';
                    } else {
                        // Оновлюємо пароль
                        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
                        $stmt->execute([$newHash, $_SESSION['admin_id']]);
                        
                        $success = 'Пароль успішно змінено';
                    }
                } catch (PDOException $e) {
                    error_log("Password change error: " . $e->getMessage());
                    $error = 'Помилка зміни пароля';
                }
            }
        } else {
            // Форма налаштувань блогу
            try {
                $blogTitle = trim($_POST['blog_title'] ?? '');
                $postsPerPage = (int)($_POST['posts_per_page'] ?? 5);
                
                if (empty($blogTitle)) {
                    $error = 'Назва блогу не може бути порожньою';
                } else {
                    // Оновлюємо налаштування
                    $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
                    
                    $stmt->execute(['blog_title', $blogTitle, $blogTitle]);
                    $stmt->execute(['posts_per_page', $postsPerPage, $postsPerPage]);
                    
                    $settings['blog_title'] = $blogTitle;
                    $settings['posts_per_page'] = $postsPerPage;
                    
                    $success = 'Налаштування збережено';
                }
            } catch (PDOException $e) {
                error_log("Settings update error: " . $e->getMessage());
                $error = 'Помилка збереження налаштувань';
            }
        }
    }
}

$pageTitle = 'Налаштування';
$content = '';
ob_start();
?>

<div class="settings-container">
    <h1>Налаштування блогу</h1>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" class="settings-form">
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
        
        <div class="form-group">
            <label for="blog_title">Назва блогу</label>
            <input 
                type="text" 
                id="blog_title" 
                name="blog_title" 
                value="<?= htmlspecialchars($settings['blog_title'] ?? '/\\ogos') ?>"
                required
            >
            <small>Відображається в заголовку та логотипі</small>
        </div>
        
        <div class="form-group">
            <label for="posts_per_page">Постів на сторінку</label>
            <input 
                type="number" 
                id="posts_per_page" 
                name="posts_per_page" 
                value="<?= (int)($settings['posts_per_page'] ?? 10) ?>"
                min="1"
                max="50"
                required
            >
            <small>Кількість постів на одній сторінці</small>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-submit">
                <i class="fas fa-save"></i>
                Зберегти налаштування
            </button>
        </div>
    </form>
    
    <div class="settings-section">
        <h2>Зміна пароля</h2>
        <form method="POST" class="settings-form">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <input type="hidden" name="change_password" value="1">
            
            <div class="form-group">
                <label for="current_password">Поточний пароль</label>
                <input 
                    type="password" 
                    id="current_password" 
                    name="current_password" 
                    required
                    autocomplete="current-password"
                >
            </div>
            
            <div class="form-group">
                <label for="new_password">Новий пароль</label>
                <input 
                    type="password" 
                    id="new_password" 
                    name="new_password" 
                    required
                    autocomplete="new-password"
                    minlength="3"
                >
                <small>Мінімум 3 символи</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Підтвердження пароля</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    required
                    autocomplete="new-password"
                >
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-key"></i>
                    Змінити пароль
                </button>
            </div>
        </form>
    </div>
    
    <div class="settings-info">
        <h2>Інформація</h2>
        <ul>
            <li><strong>Версія:</strong> 1.0.0</li>
            <li><strong>PHP:</strong> <?= PHP_VERSION ?></li>
            <li><strong>Адміністратор:</strong> <?= htmlspecialchars($_SESSION['admin_username']) ?></li>
        </ul>
    </div>
</div>

<?php
$childView = ob_get_clean();
require __DIR__ . '/../views/layout.php';
