<?php
/**
 * New Post Page Template
 */

use App\Services\Csrf;

$blogTitle = $blogSettings['site_title'] ?? '/\\ogos';
$pageTitle = "Новий пост — {$blogTitle}";

ob_start();
?>

<div class="settings-container">
    <h1>Створити новий пост</h1>

    <form method="POST" action="/admin/save_post" class="settings-form">
        <?= Csrf::field() ?>
        <input type="hidden" name="redirect_url" value="/">

        <div class="form-group">
            <label for="title">Заголовок</label>
            <input type="text" name="title" id="title" required autofocus>
        </div>

        <div class="form-group">
            <label for="slug">URL (slug)</label>
            <input type="text" name="slug" id="slug" required pattern="[a-z0-9\-]+">
            <small>Тільки латиниця, цифри та дефіси. Генерується автоматично з заголовка.</small>
        </div>

        <div class="form-group">
            <label for="content">Контент (Neasden)</label>
            <textarea id="content" name="content" required rows="20"></textarea>
            <small><strong>Синтаксис:</strong> # Заголовок • **жирний** • //курсив// • - список</small>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_published" value="1" checked>
                Опублікувати одразу
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">Створити пост</button>
            <a href="/" class="btn">Скасувати</a>
        </div>
    </form>
</div>

<script>
    // Auto-generate slug from title
    // Auto-generate slug from title with Transliteration
    function slugify(text) {
        const translat = {
            'а': 'a', 'б': 'b', 'в': 'v', 'г': 'h', 'ґ': 'g', 'д': 'd', 'е': 'e', 'є': 'ye', 'ж': 'zh', 'з': 'z',
            'и': 'y', 'і': 'i', 'ї': 'yi', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n', 'о': 'o', 'п': 'p',
            'р': 'r', 'с': 's', 'т': 't', 'у': 'u', 'ф': 'f', 'х': 'kh', 'ц': 'ts', 'ч': 'ch', 'ш': 'sh', 'щ': 'shch',
            'ь': '', 'ю': 'yu', 'я': 'ya',
            'ы': 'y', 'э': 'e', 'ё': 'yo', 'ъ': ''
        };

        return text.toLowerCase()
            .split('')
            .map(char => translat[char] || char)
            .join('')
            .replace(/[^a-z0-9\s\-]/g, '') // Remove existing non-latin chars that weren't transliterated (symbols)
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    document.getElementById('title')?.addEventListener('input', function (e) {
        const slugInput = document.getElementById('slug');
        if (!slugInput.dataset.manual) {
            slugInput.value = slugify(e.target.value);
        }
    });

    document.getElementById('slug')?.addEventListener('input', function () {
        this.dataset.manual = 'true';
    });

    // === IMAGE UPLOAD LOGIC ===
    const contentTextarea = document.getElementById('content');

    const uploadImage = async (file) => {
        const formData = new FormData();
        formData.append('image', file);

        // Show uploading state
        const cursorPos = contentTextarea.selectionStart;
        const uploadingText = `![Uploading ${file.name}...]`;
        const text = contentTextarea.value;
        contentTextarea.value = text.slice(0, cursorPos) + uploadingText + text.slice(cursorPos);

        try {
            const response = await fetch('/admin/upload_image', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                // Replace uploading text with actual markdown
                const currentText = contentTextarea.value;
                contentTextarea.value = currentText.replace(uploadingText, data.markdown);
            } else {
                alert('Upload failed: ' + data.error);
                contentTextarea.value = currentText.replace(uploadingText, '');
            }
        } catch (e) {
            console.error(e);
            alert('Upload error');
        }
    };

    contentTextarea?.addEventListener('drop', (e) => {
        e.preventDefault();
        const files = e.dataTransfer.files;
        if (files.length > 0 && files[0].type.startsWith('image/')) {
            uploadImage(files[0]);
        }
    });

    contentTextarea?.addEventListener('paste', (e) => {
        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
        for (const item of items) {
            if (item.kind === 'file' && item.type.startsWith('image/')) {
                e.preventDefault();
                uploadImage(item.getAsFile());
            }
        }
    });
</script>

<?php
$childView = ob_get_clean();
require __DIR__ . '/../layouts/layout.php';
