<?php
/**
 * Admin Settings View
 * Grid Layout - Compact & Visible
 */

use App\Services\View;
use App\Services\Csrf;

// Get success/error messages from session
$success = $_SESSION['settings_success'] ?? null;
$error = $_SESSION['settings_error'] ?? null;
unset($_SESSION['settings_success'], $_SESSION['settings_error']);

// Load current settings
$stmt = $this->pdo->query("SELECT `key`, `value` FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['key']] = $row['value'];
}

$blogTitle = $settings['site_title'] ?? '/\\ogos';
$pageTitle = "Налаштування — {$blogTitle}";
$isAdmin = true;

ob_start();
?>


<div class="settings-container settings-wide">
    <h1>Налаштування</h1>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="settings-grid">

        <!-- Column 1: General & Author -->
        <div>
            <form method="POST" action="/admin/settings" class="settings-form">
                <input type="hidden" name="csrf_token" value="<?= Csrf::generate() ?>">

                <!-- General Settings Card -->
                <div class="settings-card">
                    <h2>Загальні</h2>

                    <div class="form-group">
                        <label for="blog_title">Назва блогу</label>
                        <input type="text" id="blog_title" name="blog_title"
                            value="<?= htmlspecialchars($settings['site_title'] ?? '/\\ogos') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="blog_tagline">Підзаголовок</label>
                        <input type="text" id="blog_tagline" name="blog_tagline"
                            value="<?= htmlspecialchars($settings['blog_tagline'] ?? '') ?>">
                        <small>Короткий опис під логотипом</small>
                    </div>

                    <div class="form-group">
                        <label for="posts_per_page">Постів на сторінку</label>
                        <div class="flex-row">
                            <input type="number" id="posts_per_page" name="posts_per_page"
                                value="<?= (int) ($settings['posts_per_page'] ?? 10) ?>" min="1" max="50" required
                                style="width: 80px;">
                            <small>постів</small>
                        </div>
                    </div>
                </div>

                <!-- Author & SEO Card -->
                <div class="settings-card">
                    <h2>Автор та SEO</h2>

                    <div class="form-group">
                        <label for="blog_author">Ім'я автора</label>
                        <input type="text" id="blog_author" name="blog_author"
                            value="<?= htmlspecialchars($settings['blog_author'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="author_avatar">Аватар</label>
                        <input type="hidden" id="author_avatar" name="author_avatar"
                            value="<?= htmlspecialchars($settings['author_avatar'] ?? '') ?>">

                        <div class="flex-center">
                            <!-- Preview -->
                            <div class="avatar-preview">
                                <?php if (!empty($settings['author_avatar'])): ?>
                                    <img src="<?= htmlspecialchars($settings['author_avatar']) ?>" id="avatarPreviewImg">
                                <?php else: ?>
                                    <img src="" id="avatarPreviewImg" style="display: none;">
                                    <span id="avatarPlaceholder" class="avatar-placeholder">👤</span>
                                <?php endif; ?>
                            </div>

                            <!-- Controls -->
                            <div class="avatar-controls">
                                <input type="file" id="avatarUploadInput" accept="image/*" hidden>
                                <button type="button" class="btn-secondary" id="btnUploadAvatar">
                                    Завантажити фото
                                </button>
                                <small id="uploadStatus"></small>
                            </div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            const btnUpload = document.getElementById('btnUploadAvatar');
                            const fileInput = document.getElementById('avatarUploadInput');
                            const hiddenInput = document.getElementById('author_avatar');
                            const previewImg = document.getElementById('avatarPreviewImg');
                            const placeholder = document.getElementById('avatarPlaceholder');
                            const status = document.getElementById('uploadStatus');

                            btnUpload.addEventListener('click', () => fileInput.click());

                            fileInput.addEventListener('change', () => {
                                if (fileInput.files.length === 0) return;

                                const file = fileInput.files[0];
                                const formData = new FormData();
                                formData.append('image', file);

                                status.textContent = 'Завантаження...';
                                btnUpload.disabled = true;

                                fetch('/admin/upload_image', {
                                    method: 'POST',
                                    body: formData
                                })
                                    .then(r => r.json())
                                    .then(data => {
                                        if (data.success) {
                                            // Update Hidden Input
                                            hiddenInput.value = data.url;

                                            // Update Preview
                                            previewImg.src = data.url;
                                            previewImg.style.display = 'block';
                                            if (placeholder) placeholder.style.display = 'none';

                                            status.textContent = 'Готово!';
                                            status.style.color = 'green';
                                        } else {
                                            status.textContent = 'Помилка: ' + (data.error || 'Невідома');
                                            status.style.color = 'red';
                                        }
                                    })
                                    .catch(err => {
                                        console.error(err);
                                        status.textContent = 'Помилка мережі';
                                        status.style.color = 'red';
                                    })
                                    .finally(() => {
                                        btnUpload.disabled = false;
                                        fileInput.value = ''; // Reset input to allow re-upload same file
                                    });
                            });
                        });
                    </script>

                    <div class="form-group">
                        <label for="blog_description">Meta Description</label>
                        <textarea id="blog_description" name="blog_description" rows="3"
                            maxlength="300"><?= htmlspecialchars($settings['site_description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="google_analytics_id">Google Analytics ID</label>
                        <input type="text" id="google_analytics_id" name="google_analytics_id"
                            value="<?= htmlspecialchars($settings['google_analytics_id'] ?? '') ?>"
                            placeholder="G-XXXXXXXXXX">
                    </div>

                    <button type="submit" class="btn-submit">Зберегти налаштування</button>
                </div>
            </form>
        </div>

        <!-- Column 2: Security & System -->
        <div>
            <!-- Password Card -->
            <div class="settings-card">
                <h2>Безпека</h2>
                <form method="POST" action="/admin/settings">
                    <input type="hidden" name="csrf_token" value="<?= Csrf::generate() ?>">
                    <input type="hidden" name="change_password" value="1">

                    <div class="form-group">
                        <label for="current_password">Поточний пароль</label>
                        <input type="password" id="current_password" name="current_password" required
                            autocomplete="current-password">
                    </div>

                    <div class="grid-2col">
                        <div>
                            <label for="new_password">Новий пароль</label>
                            <input type="password" id="new_password" name="new_password" required minlength="3"
                                autocomplete="new-password">
                        </div>
                        <div>
                            <label for="confirm_password">Підтвердження</label>
                            <input type="password" id="confirm_password" name="confirm_password" required
                                autocomplete="new-password">
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">Змінити пароль</button>
                </form>
            </div>

            <!-- System Card -->
            <div class="settings-card">
                <h2>Система</h2>

                <div class="backup-item">
                    <div>
                        <strong>Резервна копія</strong>
                        <div><small>SQL дамп бази даних</small></div>
                    </div>
                    <a href="/admin/backup" class="btn">Завантажити</a>
                </div>

                <div>
                    <div>
                        <strong class="error-text">Перевстановлення CMS</strong>
                        <div><small>Видаляє config/db.php</small></div>
                    </div>
                    <form method="POST" action="/admin/reinstall"
                        onsubmit="return confirm('Ви впевнені? Це видалить конфігурацію БД!');">
                        <input type="hidden" name="csrf_token" value="<?= Csrf::generate() ?>">
                        <button type="submit" class="btn btn-danger">
                            Перевстановити
                        </button>
                    </form>
                </div>

                <div class="info-box">
                    <div><strong>CMS Version:</strong> 1.0.0</div>
                    <div><strong>PHP:</strong> <?= PHP_VERSION ?></div>
                    <div><strong>Admin:</strong> <?= htmlspecialchars($_SESSION['admin_email'] ?? 'admin') ?></div>
                </div>

                <!-- Logout Button -->
                <div>
                    <a href="/api/logout.php" class="btn">
                        <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2"
                            fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        Вийти з адмінки
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$childView = ob_get_clean();
require __DIR__ . '/../layouts/layout.php';
