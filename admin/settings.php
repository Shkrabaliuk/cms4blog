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
                $blogAuthor = trim($_POST['blog_author'] ?? '');
                $blogTagline = trim($_POST['blog_tagline'] ?? '');
                $authorAvatar = trim($_POST['author_avatar'] ?? '');
                $blogDescription = trim($_POST['blog_description'] ?? '');
                $postsPerPage = (int)($_POST['posts_per_page'] ?? 5);
                $googleAnalyticsId = trim($_POST['google_analytics_id'] ?? '');
                
                if (empty($blogTitle)) {
                    $error = 'Назва блогу не може бути порожньою';
                } else {
                    // Оновлюємо налаштування
                    $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
                    
                    $stmt->execute(['blog_title', $blogTitle, $blogTitle]);
                    $stmt->execute(['blog_author', $blogAuthor, $blogAuthor]);
                    $stmt->execute(['blog_tagline', $blogTagline, $blogTagline]);
                    $stmt->execute(['author_avatar', $authorAvatar, $authorAvatar]);
                    $stmt->execute(['blog_description', $blogDescription, $blogDescription]);
                    $stmt->execute(['posts_per_page', $postsPerPage, $postsPerPage]);
                    $stmt->execute(['google_analytics_id', $googleAnalyticsId, $googleAnalyticsId]);
                    
                    $settings['blog_title'] = $blogTitle;
                    $settings['blog_author'] = $blogAuthor;
                    $settings['blog_tagline'] = $blogTagline;
                    $settings['author_avatar'] = $authorAvatar;
                    $settings['blog_description'] = $blogDescription;
                    $settings['posts_per_page'] = $postsPerPage;
                    $settings['google_analytics_id'] = $googleAnalyticsId;
                    
                    $success = 'Налаштування збережено';
                }
            } catch (PDOException $e) {
                error_log("Settings update error: " . $e->getMessage());
                $error = 'Помилка збереження налаштувань';
            }
        }
    }
}

// Завантажуємо назву блогу для title
$stmt = $pdo->query("SELECT `value` FROM settings WHERE `key` = 'blog_title'");
$blogTitle = $stmt->fetchColumn() ?: '/\\ogos';

$pageTitle = "Налаштування — {$blogTitle}";
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
            <label for="blog_author">Автор блогу</label>
            <input 
                type="text" 
                id="blog_author" 
                name="blog_author" 
                value="<?= htmlspecialchars($settings['blog_author'] ?? '') ?>"
                class="form-input"
                placeholder="Ваше ім'я"
            >
            <small>Відображається в футері блогу</small>
        </div>
        
        <div class="form-group">
            <label for="blog_tagline">Дескрипція блогу</label>
            <input 
                type="text" 
                id="blog_tagline" 
                name="blog_tagline" 
                value="<?= htmlspecialchars($settings['blog_tagline'] ?? '') ?>"
                maxlength="100"
                class="form-input"
            >
            <small>Короткий опис під назвою блогу (максимум 100 символів)</small>
        </div>
        
        <div class="form-group">
            <label for="author_avatar">Аватарка автора</label>
            <input 
                type="url" 
                id="author_avatar" 
                name="author_avatar" 
                value="<?= htmlspecialchars($settings['author_avatar'] ?? '') ?>"
                class="form-input"
                placeholder="https://example.com/avatar.jpg"
            >
            <small>URL зображення аватарки автора (відображається в хедері)</small>
        </div>
        
        <div class="form-group">
            <label for="blog_description">Опис для SEO</label>
            <textarea 
                id="blog_description" 
                name="blog_description" 
                rows="3"
                class="form-textarea"
                maxlength="300"
            ><?= htmlspecialchars($settings['blog_description'] ?? '') ?></textarea>
            <small>Опис для meta тегів та RSS feed (максимум 300 символів)</small>
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
        
        <div class="form-group">
            <label for="google_analytics_id">Google Analytics ID</label>
            <input 
                type="text" 
                id="google_analytics_id" 
                name="google_analytics_id" 
                value="<?= htmlspecialchars($settings['google_analytics_id'] ?? '') ?>"
                placeholder="G-XXXXXXXXXX або UA-XXXXXXXXX-X"
                pattern="^(G-[A-Z0-9]+|UA-[0-9]+-[0-9]+)?$"
            >
            <small>Measurement ID з Google Analytics 4 (опціонально)</small>
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
    
    <div class="settings-section">
        <h2>Резервне копіювання</h2>
        <p style="margin-bottom: 16px; color: #999;">
            Створіть backup бази даних у форматі SQL. Файл буде автоматично завантажено на ваш комп'ютер.
        </p>
        <a href="/admin/backup.php" class="btn" style="display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-download"></i>
            Завантажити backup БД
        </a>
        <p style="margin-top: 12px; font-size: 13px; color: #999;">
            <i class="fas fa-info-circle"></i>
            Backup включає всі таблиці, дані та структуру бази даних
        </p>
    </div>
    
    <div class="settings-section">
        <h2>Логи помилок</h2>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <p style="margin: 0; color: #999;">
                Останні 100 записів з PHP error log
            </p>
            <a href="/admin/clear_logs.php" class="btn btn-ghost" onclick="return confirm('Очистити всі логи?')">
                <i class="fas fa-trash"></i>
                Очистити
            </a>
        </div>
        
        <?php
        $errorLog = ini_get('error_log');
        if (empty($errorLog) || $errorLog === 'syslog') {
            // Шукаємо стандартні локації
            $possiblePaths = [
                __DIR__ . '/../error_log',
                __DIR__ . '/../php_errors.log',
                $_SERVER['DOCUMENT_ROOT'] . '/error_log',
                '/var/log/php_errors.log'
            ];
            
            foreach ($possiblePaths as $path) {
                if (file_exists($path)) {
                    $errorLog = $path;
                    break;
                }
            }
        }
        
        if ($errorLog && file_exists($errorLog) && is_readable($errorLog)):
            $logs = file($errorLog, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $logs = array_reverse(array_slice($logs, -100));
        ?>
            <div class="log-container">
                <?php if (empty($logs)): ?>
                    <p style="color: #999; text-align: center; padding: 24px;">
                        <i class="fas fa-check-circle"></i>
                        Помилок не знайдено
                    </p>
                <?php else: ?>
                    <?php foreach ($logs as $line): ?>
                        <div class="log-line">
                            <?= htmlspecialchars($line) ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="log-container">
                <p style="color: #999; text-align: center; padding: 24px;">
                    <i class="fas fa-info-circle"></i>
                    Файл логів не знайдено або недоступний
                </p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="settings-section">
        <h2>Утиліти</h2>
        <p style="margin-bottom: 16px; color: #999;">
            Швидкий доступ до системних інструментів
        </p>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="/init_search_tables.php" target="_blank" class="btn">
                <i class="fas fa-database"></i>
                Створити таблиці пошуку
            </a>
            <a href="/reindex.php" target="_blank" class="btn">
                <i class="fas fa-sync"></i>
                Реіндексація пошуку
            </a>
            <a href="/sitemap.php" target="_blank" class="btn">
                <i class="fas fa-sitemap"></i>
                Генерація Sitemap
            </a>
            <a href="/rss.php" target="_blank" class="btn">
                <i class="fas fa-rss"></i>
                Переглянути RSS
            </a>
        </div>
    </div>
</div>

<?php
$childView = ob_get_clean();
require __DIR__ . '/../views/layout.php';
