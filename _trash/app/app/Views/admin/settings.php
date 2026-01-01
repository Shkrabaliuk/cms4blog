<div class="admin-nav">
    <a href="/admin/admin.php"><i class="fa-solid fa-file-lines"></i> Пости</a>
    <a href="/admin/comments.php"><i class="fa-solid fa-comments"></i> Коментарі</a>
    <a href="/admin/settings.php" class="active"><i class="fa-solid fa-gear"></i> Налаштування</a>
</div>

<h2>Налаштування сайту</h2>

<?php if (isset($_GET['saved'])): ?>
<div class="message success"><i class="fa-solid fa-check"></i> Налаштування збережено</div>
<?php endif; ?>

<!-- СЕКЦІЯ ЛОГОТИПУ -->
<div class="settings-section">
    <h3><i class="fa-solid fa-image"></i> Логотип сайту</h3>
    
    <div class="logo-upload-container">
        <div id="logo-dropzone" class="dropzone <?= isset($logo) ? 'has-logo' : '' ?>">
            <?php if (isset($logo)): ?>
                <div class="logo-preview">
                    <img src="<?= htmlspecialchars($logo['filename']) ?>" alt="Логотип" id="logo-image">
                    <div class="logo-overlay">
                        <button type="button" class="btn-icon" id="delete-logo" title="Видалити">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div class="dropzone-placeholder">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <p>Перетягніть логотип сюди або клацніть для вибору</p>
                    <small>PNG, JPG, SVG, WEBP (макс. 5MB)</small>
                </div>
            <?php endif; ?>
            <input type="file" id="logo-input" accept="image/*" style="display: none;">
        </div>
        
        <div id="upload-progress" class="progress-bar" style="display: none;">
            <div class="progress-fill"></div>
        </div>
        
        <div id="upload-message" class="upload-message"></div>
    </div>
</div>

<!-- ЗАГАЛЬНІ НАЛАШТУВАННЯ -->
<form method="POST" action="/admin/settings.php?action=save">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
    
    <div class="settings-section">
        <h3><i class="fa-solid fa-globe"></i> Загальні</h3>
        
        <div class="form-group">
            <label>Назва сайту:</label>
            <input type="text" name="blog_name" value="<?= htmlspecialchars($settings['blog_name'] ?? 'Мій блог') ?>" required>
        </div>
        
        <div class="form-group">
            <label>Підзаголовок:</label>
            <input type="text" name="blog_subtitle" value="<?= htmlspecialchars($settings['blog_subtitle'] ?? '') ?>">
        </div>
        
        <div class="form-group">
            <label>Опис (для SEO):</label>
            <textarea name="blog_description" rows="3"><?= htmlspecialchars($settings['blog_description'] ?? '') ?></textarea>
        </div>
        
        <div class="form-group">
            <label>Автор:</label>
            <input type="text" name="author_name" value="<?= htmlspecialchars($settings['author_name'] ?? '') ?>">
        </div>
    </div>
    
    <div class="settings-section">
        <h3><i class="fa-solid fa-display"></i> Відображення</h3>
        
        <div class="form-group">
            <label>Постів на сторінку:</label>
            <input type="number" name="posts_per_page" value="<?= intval($settings['posts_per_page'] ?? 10) ?>" min="1" max="50">
        </div>
        
        <div class="form-group checkbox-group">
            <label>
                <input type="checkbox" name="show_view_counts" value="1" <?= ($settings['show_view_counts'] ?? '0') === '1' ? 'checked' : '' ?>>
                <span>Показувати лічильник переглядів</span>
            </label>
        </div>
        
        <div class="form-group checkbox-group">
            <label>
                <input type="checkbox" name="show_logo" value="1" <?= ($settings['show_logo'] ?? '1') === '1' ? 'checked' : '' ?>>
                <span>Показувати логотип у шапці</span>
            </label>
        </div>
    </div>
    
    <div class="settings-section">
        <h3><i class="fa-solid fa-comments"></i> Коментарі</h3>
        
        <div class="form-group checkbox-group">
            <label>
                <input type="checkbox" name="comments_require_moderation" value="1" <?= ($settings['comments_require_moderation'] ?? '1') === '1' ? 'checked' : '' ?>>
                <span>Коментарі потребують модерації</span>
            </label>
        </div>
        
        <div class="form-group">
            <label>Ліміт коментарів на годину (з одного IP):</label>
            <input type="number" name="comment_rate_limit" value="<?= intval($settings['comment_rate_limit'] ?? 5) ?>" min="1" max="100">
        </div>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="button primary">
            <i class="fa-solid fa-save"></i> Зберегти налаштування
        </button>
    </div>
</form>

<style>
/* Стилі для Drag & Drop логотипу */
.settings-section {
    background: #f9f9f9;
    padding: 30px;
    margin-bottom: 30px;
    border-radius: 8px;
}

.settings-section h3 {
    margin-bottom: 20px;
    color: #333;
    font-size: 18px;
}

.logo-upload-container {
    max-width: 600px;
}

.dropzone {
    border: 2px dashed #ccc;
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
    background: white;
}

.dropzone:hover {
    border-color: #0066cc;
    background: #f0f8ff;
}

.dropzone.dragover {
    border-color: #0066cc;
    background: #e6f2ff;
    transform: scale(1.02);
}

.dropzone.has-logo {
    padding: 20px;
    border-style: solid;
}

.dropzone-placeholder i {
    font-size: 48px;
    color: #999;
    margin-bottom: 10px;
}

.dropzone-placeholder p {
    font-size: 16px;
    margin: 10px 0;
    color: #666;
}

.dropzone-placeholder small {
    color: #999;
}

.logo-preview {
    position: relative;
    display: inline-block;
}

.logo-preview img {
    max-height: 200px;
    max-width: 100%;
    display: block;
    margin: 0 auto;
}

.logo-overlay {
    position: absolute;
    top: 0;
    right: 0;
    opacity: 0;
    transition: opacity 0.3s;
}

.logo-preview:hover .logo-overlay {
    opacity: 1;
}

.btn-icon {
    background: rgba(255, 0, 0, 0.9);
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-icon:hover {
    background: rgba(200, 0, 0, 1);
}

.progress-bar {
    width: 100%;
    height: 4px;
    background: #e0e0e0;
    border-radius: 2px;
    margin-top: 10px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: #0066cc;
    width: 0;
    transition: width 0.3s;
}

.upload-message {
    margin-top: 10px;
    padding: 10px;
    border-radius: 4px;
    display: none;
}

.upload-message.success {
    background: #d4edda;
    color: #155724;
    display: block;
}

.upload-message.error {
    background: #f8d7da;
    color: #721c24;
    display: block;
}

.checkbox-group label {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.checkbox-group input[type="checkbox"] {
    margin-right: 10px;
    width: 18px;
    height: 18px;
}

.form-actions {
    margin-top: 30px;
}

.button.primary {
    background: #0066cc;
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s;
}

.button.primary:hover {
    background: #0052a3;
}
</style>

<script>
// Drag & Drop функціонал для логотипу
(function() {
    const dropzone = document.getElementById('logo-dropzone');
    const fileInput = document.getElementById('logo-input');
    const deleteBtn = document.getElementById('delete-logo');
    const progressBar = document.getElementById('upload-progress');
    const progressFill = progressBar.querySelector('.progress-fill');
    const message = document.getElementById('upload-message');
    
    // Клік на dropzone відкриває file input
    dropzone.addEventListener('click', () => {
        if (!dropzone.classList.contains('has-logo')) {
            fileInput.click();
        }
    });
    
    // Вибір файлу через input
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            uploadFile(e.target.files[0]);
        }
    });
    
    // Drag & Drop події
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => {
            dropzone.classList.add('dragover');
        });
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => {
            dropzone.classList.remove('dragover');
        });
    });
    
    dropzone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            uploadFile(files[0]);
        }
    });
    
    // Завантаження файлу
    function uploadFile(file) {
        // Валідація типу
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        if (!allowedTypes.includes(file.type)) {
            showMessage('Недозволений тип файлу. Використовуйте PNG, JPG, SVG або WEBP', 'error');
            return;
        }
        
        // Валідація розміру
        if (file.size > 5 * 1024 * 1024) {
            showMessage('Файл занадто великий. Максимум 5MB', 'error');
            return;
        }
        
        const formData = new FormData();
        formData.append('logo', file);
        formData.append('csrf_token', '<?= generate_csrf_token() ?>');
        
        // Показуємо прогрес
        progressBar.style.display = 'block';
        progressFill.style.width = '0%';
        
        fetch('/admin/settings.php?action=upload_logo', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Оновлюємо інтерфейс
                dropzone.classList.add('has-logo');
                dropzone.innerHTML = `
                    <div class="logo-preview">
                        <img src="${data.logo_url}" alt="Логотип" id="logo-image">
                        <div class="logo-overlay">
                            <button type="button" class="btn-icon" id="delete-logo" title="Видалити">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                
                showMessage(data.message, 'success');
                progressFill.style.width = '100%';
                
                // Прив'язуємо подію видалення до нової кнопки
                document.getElementById('delete-logo').addEventListener('click', deleteLogo);
                
                setTimeout(() => {
                    progressBar.style.display = 'none';
                }, 1000);
            } else {
                showMessage(data.error, 'error');
                progressBar.style.display = 'none';
            }
        })
        .catch(error => {
            showMessage('Помилка завантаження: ' + error.message, 'error');
            progressBar.style.display = 'none';
        });
    }
    
    // Видалення логотипу
    function deleteLogo(e) {
        e.stopPropagation();
        
        if (!confirm('Видалити логотип?')) return;
        
        fetch('/admin/settings.php?action=delete_logo', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'csrf_token=<?= generate_csrf_token() ?>'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                dropzone.classList.remove('has-logo');
                dropzone.innerHTML = `
                    <div class="dropzone-placeholder">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <p>Перетягніть логотип сюди або клацніть для вибору</p>
                        <small>PNG, JPG, SVG, WEBP (макс. 5MB)</small>
                    </div>
                `;
                showMessage(data.message, 'success');
            } else {
                showMessage(data.error, 'error');
            }
        })
        .catch(error => {
            showMessage('Помилка: ' + error.message, 'error');
        });
    }
    
    if (deleteBtn) {
        deleteBtn.addEventListener('click', deleteLogo);
    }
    
    // Показати повідомлення
    function showMessage(text, type) {
        message.className = 'upload-message ' + type;
        message.textContent = text;
        message.style.display = 'block';
        
        setTimeout(() => {
            message.style.display = 'none';
        }, 5000);
    }
})();
</script>
