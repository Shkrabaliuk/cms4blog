<?php 
// Парсимо контент через Neasden
require_once __DIR__ . '/../includes/ContentParser.php';
require_once __DIR__ . '/../includes/csrf.php';
$parser = new ContentParser();

if (empty($posts)): ?>
    <div class="post">
        <p class="empty-message">Поки що тут тихо...</p>
    </div>
<?php else: ?>
    
    <!-- Навігація вгорі (новіші пости) -->
    <?php if (isset($page) && $page > 1): ?>
        <div class="pagination pagination-top">
            <a href="/?page=<?= $page - 1 ?>" class="pagination-link pagination-prev">
                <i class="fas fa-arrow-up"></i>
                Читати вище
            </a>
        </div>
    <?php endif; ?>
    
    <!-- Форма створення нового посту -->
    <?php if ($isAdmin): ?>
        <div id="newPostForm" style="display: none;" class="post">
            <h2>Новий пост</h2>
            <form method="POST" action="/admin/save_post.php">
                <?= csrfField() ?>
                <input type="hidden" name="redirect_url" value="/">
                
                <div class="form-group">
                    <label>Заголовок</label>
                    <input 
                        type="text" 
                        name="title" 
                        id="new_title"
                        required
                        class="form-input"
                    >
                </div>
                
                <div class="form-group">
                    <label>URL (slug)</label>
                    <input 
                        type="text" 
                        name="slug" 
                        id="new_slug"
                        required
                        pattern="[a-z0-9\-]+"
                        class="form-input"
                    >
                    <small class="hint-text">Тільки латиниця, цифри та дефіси</small>
                </div>
                
                <div class="form-group">
                    <label>Контент (Neasden)</label>
                    
                    <!-- Drag & Drop зона для картинок -->
                    <div id="newPostDropzone" class="image-dropzone">
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
                        id="newPostContent"
                        name="content" 
                        required
                        rows="15"
                        class="form-textarea"
                    ></textarea>
                    <small class="hint-text">
                        <strong>Синтаксис:</strong> # Заголовок • **жирний** • //курсив// • - список
                    </small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Створити
                    </button>
                    <button type="button" onclick="toggleNewPostForm()" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Скасувати
                    </button>
                </div>
            </form>
        </div>
        
        <script>
            function toggleNewPostForm() {
                const form = document.getElementById('newPostForm');
                const timeline = document.getElementById('timelineContent');
                if (form.style.display === 'none') {
                    form.style.display = 'block';
                    timeline.style.display = 'none';
                    document.getElementById('new_title').focus();
                } else {
                    form.style.display = 'none';
                    timeline.style.display = 'block';
                }
            }
            
            // Автогенерація slug з заголовка
            document.getElementById('new_title')?.addEventListener('input', function(e) {
                const slugInput = document.getElementById('new_slug');
                if (!slugInput.dataset.manual) {
                    slugInput.value = e.target.value
                        .toLowerCase()
                        .replace(/[^a-z0-9\s\-]/g, '')
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-')
                        .trim();
                }
            });
            
            document.getElementById('new_slug')?.addEventListener('input', function() {
                this.dataset.manual = 'true';
            });
        </script>
    <?php endif; ?>
    
    <div id="timelineContent">
    <?php foreach ($posts as $post): ?>
        <div class="note" data-note-id="<?= $post['id'] ?>">
            <article class="h-entry">
                <h1 class="note-title p-name">
                    <a href="/<?= htmlspecialchars($post['slug']) ?>">
                        <?= htmlspecialchars($post['title']) ?>
                    </a>
                </h1>
                
                <div class="note-text e-content">
                    <?= $parser->parse($post['content']) ?>
                </div>
            </article>
            
            <div class="band band-meta-size note-meta">
                <div class="band-scrollable">
                    <div class="band-scrollable-inner">
                        <nav>
                            <?php if ($isAdmin): ?>
                            <div class="band-item">
                                <a href="/<?= htmlspecialchars($post['slug']) ?>#edit" class="band-item-inner" title="Редагувати">
                                    <i class="fas fa-pen"></i>
                                </a>
                            </div>
                            <?php endif; ?>
                            
                            <div class="band-item">
                                <div class="band-item-inner">
                                    <span title="<?= date('d.m.Y H:i', strtotime($post['created_at'])) ?>">
                                        <?= date('Y', strtotime($post['created_at'])) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <?php
                            // Завантажуємо теги для поста
                            $tags = [];
                            try {
                                $stmt = $pdo->prepare("
                                    SELECT t.* 
                                    FROM tags t
                                    JOIN post_tags pt ON t.id = pt.tag_id
                                    WHERE pt.post_id = ?
                                    ORDER BY t.name
                                ");
                                $stmt->execute([$post['id']]);
                                $tags = $stmt->fetchAll();
                            } catch (PDOException $e) {
                                // Таблиця tags не існує - ігноруємо
                            }
                            ?>
                            
                            <?php if (!empty($tags)): ?>
                                <?php foreach ($tags as $tag): ?>
                                <div class="band-item">
                                    <a href="/tag/<?= urlencode($tag['name']) ?>" class="tag band-item-inner">
                                        <?= htmlspecialchars($tag['name']) ?>
                                    </a>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    
    <!-- Навігація внизу (старіші пости) -->
    <?php if (isset($page) && isset($totalPages) && $page < $totalPages): ?>
        <div class="pagination pagination-bottom">
            <a href="/?page=<?= $page + 1 ?>" class="pagination-link pagination-next">
                Читати нижче
                <i class="fas fa-arrow-down"></i>
            </a>
        </div>
    <?php endif; ?>
    </div>

<?php endif; ?>