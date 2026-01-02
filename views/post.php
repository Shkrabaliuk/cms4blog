<?php 
// Парсимо контент через Neasden
require_once __DIR__ . '/../includes/ContentParser.php';
require_once __DIR__ . '/../includes/csrf.php';
$parser = new ContentParser();

if (empty($post)): ?>
    <p class="empty-message">Пост не знайдено</p>
<?php else: ?>
    
    <!-- Режим перегляду -->
    <div class="e2-note" id="postView">
        <h1><?= htmlspecialchars($post['title']) ?></h1>
        
        <div class="e2-note-text e2-text">
            <?= $parser->parse($post['content']) ?>
        </div>
        
        <?php if ($post['type'] === 'image' && !empty($post['gallery_images'])): ?>
        <div class="fotorama" data-nav="thumbs" data-width="100%" data-ratio="16/9">
            <?php foreach ($post['gallery_images'] as $img): ?>
                <img src="<?= htmlspecialchars($img) ?>" alt="">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <div class="e2-band">
            <div class="e2-band-scrollable">
                <nav>
                    <?php if ($isAdmin): ?>
                    <div class="band-item">
                        <button onclick="toggleEditMode()">
                            <i class="fas fa-pen"></i> Редагувати
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="band-item">
                        <a href="#comments">
                            <i class="fas fa-comment"></i>
                            <?= !empty($comments) ? count($comments) : 'Коментарі' ?>
                        </a>
                    </div>
                    
                    <div class="band-item">
                        <span title="<?= date('d.m.Y H:i', strtotime($post['created_at'])) ?>">
                            <?= date('Y', strtotime($post['created_at'])) ?>
                        </span>
                    </div>
                    
                    <?php foreach ($tags as $tag): ?>
                    <div class="band-item">
                        <a href="/tag/<?= urlencode($tag['name']) ?>" class="e2-tag">
                            <?= htmlspecialchars($tag['name']) ?>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </nav>
            </div>
        </div>
    </div>
    
    <!-- Режим редагування (прихований за замовчуванням) -->
    <?php if ($isAdmin): ?>
    <div id="postEdit" style="display: none;" class="post">
        <form method="POST" action="/admin/save_post.php">
            <?= csrfField() ?>
            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
            <input type="hidden" name="redirect_url" value="/<?= htmlspecialchars($post['slug']) ?>">
            
            <div class="form-group">
                <label for="edit_title">Заголовок</label>
                <input 
                    type="text" 
                    id="edit_title" 
                    name="title" 
                    value="<?= htmlspecialchars($post['title']) ?>"
                    required
                    class="form-input"
                >
            </div>
            
            <div class="form-group">
                <label for="edit_slug">URL (slug)</label>
                <input 
                    type="text" 
                    id="edit_slug" 
                    name="slug" 
                    value="<?= htmlspecialchars($post['slug']) ?>"
                    required
                    pattern="[a-z0-9\-]+"
                    class="form-input"
                >
                <small class="hint-text">Тільки латиниця, цифри та дефіси</small>
            </div>
            
            <div class="form-group">
                <label for="edit_content">Контент (Neasden розмітка)</label>
                
                <!-- Drag & Drop зона для картинок -->
                <div id="imageDropzone" class="image-dropzone">
                    <div class="dropzone-icon">📷</div>
                    <div class="dropzone-text">
                        <strong>Перетягніть картинки сюди</strong> або клікніть для вибору
                    </div>
                    <div class="dropzone-hint">
                        JPG, PNG, GIF, WebP • Максимум 10MB • Ctrl+V для вставки
                    </div>
                    <div class="upload-progress">Завантаження...</div>
                </div>
                
                <textarea 
                    id="content" 
                    name="content" 
                    required
                    rows="20"
                    class="form-textarea"
                ><?= htmlspecialchars($post['content']) ?></textarea>
                <small class="hint-text">
                    <strong>Синтаксис:</strong> # Заголовок • **жирний** • //курсив// • - список • відступ 4 пробіли для коду
                </small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Зберегти
                </button>
                <button type="button" onclick="toggleEditMode()" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Скасувати
                </button>
            </div>
        </form>
    </div>
    
    <script>
        function toggleEditMode() {
            const viewMode = document.getElementById('postView');
            const editMode = document.getElementById('postEdit');
            
            if (viewMode.style.display === 'none') {
                viewMode.style.display = 'block';
                editMode.style.display = 'none';
                window.location.hash = '';
            } else {
                viewMode.style.display = 'none';
                editMode.style.display = 'block';
                document.getElementById('edit_title').focus();
                window.location.hash = 'edit';
            }
        }
        
        // Автоматично відкрити редагування, якщо в URL є #edit
        if (window.location.hash === '#edit') {
            toggleEditMode();
        }
    </script>
    <?php endif; ?>
    
    <?php if (!empty($comments)): ?>
        <section class="comments">
            <h2 class="comments-heading">
                Коментарі (<?= count($comments) ?>)
            </h2>
            
            <?php foreach ($comments as $comment): ?>
                <div class="comment <?= $comment['parent_id'] ? 'reply' : '' ?>">
                    <div class="comment-userpic">
                        <?php if (!empty($comment['userpic'])): ?>
                            <img src="<?= htmlspecialchars($comment['userpic']) ?>" alt="">
                        <?php endif; ?>
                    </div>
                    
                    <div class="comment-content">
                        <div class="comment-date">
                            <span class="comment-author">
                                <?= htmlspecialchars($comment['author_name']) ?>
                            </span>
                            <?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?>
                        </div>
                        
                        <div class="comment-text">
                            <?= nl2br(htmlspecialchars($comment['content'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
    
    <!-- Форма додавання коментаря -->
    <section class="comment-form-section">
        <h3 class="comment-form-heading">
            <?= !empty($comments) ? 'Залишити коментар' : 'Будьте першим, хто прокоментує' ?>
        </h3>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="/post_comment.php" class="comment-form">
            <?= csrfField() ?>
            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
            <input type="hidden" name="redirect_url" value="/<?= htmlspecialchars($post['slug']) ?>">
            
            <div class="form-group">
                <label for="author_name">Ім'я</label>
                <input 
                    type="text" 
                    id="author_name" 
                    name="author_name" 
                    required 
                    maxlength="100"
                    class="form-input"
                    placeholder="Ваше ім'я"
                    value="<?= htmlspecialchars($commentData['author_name'] ?? '') ?>"
                >
            </div>
            
            <div class="form-group">
                <label for="content">Коментар</label>
                <textarea 
                    id="content" 
                    name="content" 
                    required 
                    maxlength="5000"
                    rows="5"
                    class="form-textarea"
                    placeholder="Ваш коментар..."
                ><?= htmlspecialchars($commentData['content'] ?? '') ?></textarea>
            </div>
            
            <button type="submit" class="btn-submit">Відправити</button>
        </form>
    </section>
    
<?php endif; ?>