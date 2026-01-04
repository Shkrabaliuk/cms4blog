<?php
use App\Services\Render;
use App\Services\Csrf;

if (empty($post)): ?>
    <section>
        <p>Пост не знайдено</p>
    </section>
<?php else: ?>

    <!-- Post Content -->
    <article id="postView">
        <!-- Admin Edit Button (Sticky) -->

        <header>
            <h1><?= htmlspecialchars($post['title']) ?></h1>
            <p class="post-meta">
                <time datetime="<?= date('Y-m-d', strtotime($post['created_at'])) ?>">
                    <?= date('d.m.Y', strtotime($post['created_at'])) ?>
                </time>
                <?php if (!empty($tags)): ?>
                    <?php foreach ($tags as $tag): ?>
                        · <a href="/tag/<?= urlencode($tag['name']) ?>">#<?= htmlspecialchars($tag['name']) ?></a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </p>
        </header>

        <section>
            <?= $post['content'] ?>
        </section>
    </article>

    <!-- Edit Mode (Admin Only) -->
    <?php if ($isAdmin): ?>
        <div id="postEdit" hidden>
            <h2>Редагування посту</h2>
            <form method="POST" action="/admin/save_post">
                <?= Csrf::field() ?>
                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                <input type="hidden" name="redirect_url" value="/<?= htmlspecialchars($post['slug']) ?>">

                <label for="edit_title">
                    Заголовок
                    <input type="text" id="edit_title" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
                </label>

                <label for="edit_slug">
                    URL (slug)
                    <input type="text" id="edit_slug" name="slug" value="<?= htmlspecialchars($post['slug']) ?>" required
                        pattern="[a-z0-9\-]+">
                    <small>Тільки латиниця, цифри та дефіси</small>
                </label>

                <label for="content">
                    Контент (Neasden)
                    <textarea id="content" name="content" required
                        rows="20"><?= htmlspecialchars($post['content']) ?></textarea>
                    <small><strong>Синтаксис:</strong> # Заголовок · **жирний** · //курсив// · - список</small>
                </label>

                <button type="submit">Зберегти</button>
                <button type="button" onclick="toggleEditMode()">Скасувати</button>
            </form>
        </div>

        <script>
            function toggleEditMode() {
                const viewMode = document.getElementById('postView');
                const editMode = document.getElementById('postEdit');

                if (viewMode.hidden) {
                    viewMode.hidden = false;
                    editMode.hidden = true;
                    window.location.hash = '';
                } else {
                    viewMode.hidden = true;
                    editMode.hidden = false;
                    document.getElementById('edit_title').focus();
                    window.location.hash = 'edit';
                }
            }

            // Auto-open edit mode if #edit in URL
            if (window.location.hash === '#edit') {
                toggleEditMode();
            }

            // Slugify for Edit Mode
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
                    .replace(/[^a-z0-9\s\-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }

            const editTitle = document.getElementById('edit_title');
            const editSlug = document.getElementById('edit_slug');

            if (editTitle && editSlug) {
                editTitle.addEventListener('input', function (e) {
                    // Only auto-update if the user hasn't manually edited the slug
                    if (!editSlug.dataset.manual) {
                        editSlug.value = slugify(e.target.value);
                    }
                });

                editSlug.addEventListener('input', function () {
                    this.dataset.manual = 'true';
                });
            }

            // === IMAGE UPLOAD LOGIC (Edit Mode) ===
            const contentTextarea = document.getElementById('content');

            const uploadImage = async (file) => {
                const formData = new FormData();
                formData.append('image', file);

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
                        const currentText = contentTextarea.value;
                        contentTextarea.value = currentText.replace(uploadingText, data.markdown);
                    } else {
                        alert('Upload failed: ' + data.error);
                        contentTextarea.value = contentTextarea.value.replace(uploadingText, '');
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
    <?php endif; ?>

    <!-- Comments Section -->
    <section id="comments">
        <?php if (!empty($comments)): ?>
            <h2>Коментарів: <?= count($comments) ?></h2>
        <?php else: ?>
            <h2>Коментарі поки відсутні</h2>
            <small>Чи не бажаєте написати перший?</small>
        <?php endif; ?>

        <?php if (!empty($comments)): ?>
            <?php foreach ($comments as $comment): ?>
                <article class="comment">
                    <header>
                        <strong><?= htmlspecialchars($comment['author_name']) ?></strong>
                        <small>
                            <?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?>
                        </small>
                    </header>
                    <div>
                        <?= nl2br(htmlspecialchars($comment['content'])) ?>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Comment Form -->
        <div>
            <h3>Залишити коментар</h3>

            <?php if (isset($_SESSION['comment_error'])): ?>
                <p class="error-text"><?= htmlspecialchars($_SESSION['comment_error']) ?></p>
                <?php unset($_SESSION['comment_error']); ?>
            <?php endif; ?>

            <form method="POST" action="/api/post_comment.php">
                <?= Csrf::field() ?>
                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                <input type="hidden" name="redirect_url" value="/<?= htmlspecialchars($post['slug']) ?>">

                <label for="author_name">
                    Ім'я
                    <input type="text" id="author_name" name="author_name" required maxlength="100" placeholder="Ваше ім'я">
                </label>

                <label for="comment_content">
                    Коментар
                    <textarea id="comment_content" name="content" required maxlength="5000" rows="5"
                        placeholder="Ваш коментар..."></textarea>
                </label>

                <button type="submit">Відправити</button>
            </form>
        </div>
    </section>

<?php endif; ?>