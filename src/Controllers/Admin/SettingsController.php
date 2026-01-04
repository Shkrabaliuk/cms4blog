<?php
namespace App\Controllers\Admin;

use App\Config\Database;
use App\Services\Auth;
use App\Services\View;
use App\Services\Csrf;

class SettingsController
{
    private $pdo;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        Auth::require();
        $this->pdo = Database::connect();
    }

    public function index()
    {
        require __DIR__ . '/../../../templates/pages/admin_settings.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/settings');
            exit;
        }

        // Verify CSRF
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            $_SESSION['settings_error'] = 'Невірний CSRF токен';
            header('Location: /admin/settings');
            exit;
        }

        // Check which form was submitted
        if (isset($_POST['change_password'])) {
            $this->changePassword();
        } else {
            $this->saveSettings();
        }
    }

    private function changePassword()
    {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['settings_error'] = 'Заповніть всі поля';
        } elseif ($newPassword !== $confirmPassword) {
            $_SESSION['settings_error'] = 'Нові паролі не збігаються';
        } elseif (strlen($newPassword) < 3) {
            $_SESSION['settings_error'] = 'Пароль занадто короткий (мінімум 3 символи)';
        } else {
            try {
                // Check current password
                $stmt = $this->pdo->prepare("SELECT password_hash FROM users WHERE id = ? AND role = 'admin'");
                $stmt->execute([$_SESSION['admin_id']]);
                $user = $stmt->fetch(\PDO::FETCH_ASSOC);

                if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
                    $_SESSION['settings_error'] = 'Невірний поточний пароль';
                } else {
                    // Update password
                    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $this->pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    $stmt->execute([$newHash, $_SESSION['admin_id']]);

                    $_SESSION['settings_success'] = 'Пароль успішно змінено';
                }
            } catch (\PDOException $e) {
                error_log("Password change error: " . $e->getMessage());
                $_SESSION['settings_error'] = 'Помилка зміни пароля';
            }
        }

        header('Location: /admin/settings');
        exit;
    }

    private function saveSettings()
    {
        error_log("saveSettings called - POST data: " . print_r($_POST, true));

        try {
            $blogTitle = trim($_POST['blog_title'] ?? '');
            $blogAuthor = trim($_POST['blog_author'] ?? '');
            $blogTagline = trim($_POST['blog_tagline'] ?? '');
            $authorAvatar = trim($_POST['author_avatar'] ?? '');
            $blogDescription = trim($_POST['blog_description'] ?? '');
            $postsPerPage = (int) ($_POST['posts_per_page'] ?? 5);
            $googleAnalyticsId = trim($_POST['google_analytics_id'] ?? '');

            error_log("Blog title to save: " . $blogTitle);

            if (empty($blogTitle)) {
                $_SESSION['settings_error'] = 'Назва блогу не може бути порожньою';
            } else {
                // Update settings
                $stmt = $this->pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");

                $stmt->execute(['site_title', $blogTitle, $blogTitle]);
                error_log("site_title updated to: " . $blogTitle);

                $stmt->execute(['blog_author', $blogAuthor, $blogAuthor]);
                $stmt->execute(['blog_tagline', $blogTagline, $blogTagline]);
                $stmt->execute(['author_avatar', $authorAvatar, $authorAvatar]);
                $stmt->execute(['site_description', $blogDescription, $blogDescription]);
                $stmt->execute(['posts_per_page', $postsPerPage, $postsPerPage]);
                $stmt->execute(['google_analytics_id', $googleAnalyticsId, $googleAnalyticsId]);

                $_SESSION['settings_success'] = 'Налаштування збережено';
                error_log("Settings saved successfully");
            }
        } catch (\PDOException $e) {
            error_log("Settings update error: " . $e->getMessage());
            $_SESSION['settings_error'] = 'Помилка збереження налаштувань';
        }

        header('Location: /admin/settings');
        exit;
    }
}
