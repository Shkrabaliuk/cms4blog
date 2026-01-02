<?php 
// Парсимо контент через Neasden
require_once __DIR__ . '/../includes/ContentParser.php';
require_once __DIR__ . '/../includes/csrf.php';
$parser = new ContentParser();

if (empty($posts)): ?>
    <div class="e2-note">
        <p class="empty-message">Поки що тут тихо...</p>
    </div>
<?php else: ?>
    
    <!-- Навігація вгорі (новіші пости) -->
    <?php if (isset($page) && $page > 1): ?>
        <div class="pagination">
            <a href="/?page=<?= $page - 1 ?>" class="pagination-link">
                <span class="e2-svgi">↑</span>
                Читати вище
            </a>
        </div>
    <?php endif; ?>
    
    <!-- Форма створення нового посту -->
    <?php if ($isAdmin): ?>
        <div id="newPostForm" style="display: none;" class="e2-note">
            <h2>Новий пост</h2>
            <form method="POST" action="/admin/save_post.php">
                <?= csrfField() ?>
                <input type="hidden" name="redirect_url" value="/">
                
                <div class="form-control">
                    <label>Заголовок</label>
                    <input 
                        type="text" 
                        name="title" 
                        id="new_title"
                        required
                    >
                </div>
                
                <div class="form-control">
                    <label>URL (slug)</label>
                    <input 
                        type="text" 
                        name="slug" 
                        id="new_slug"
                        required
                        pattern="[a-z0-9\-]+"
                    >
                    <small>Тільки латиниця, цифри та дефіси</small>
                </div>
                
                <div class="form-control">
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
                    ></textarea>
                    <small>
                        <strong>Синтаксис:</strong> # Заголовок • **жирний** • //курсив// • - список
                    </small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="e2-submit-button">
                        <span class="e2-svgi">✓</span> Створити
                    </button>
                    <button type="button" onclick="toggleNewPostForm()" class="e2-button">
                        <span class="e2-svgi">✕</span> Скасувати
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
        <article class="e2-note">
            <h1>
                <a href="/<?= htmlspecialchars($post['slug']) ?>">
                    <?= htmlspecialchars($post['title']) ?>
                </a>
            </h1>
            
            <div class="e2-text">
                <?= $parser->parse($post['content']) ?>
            </div>
            
            <footer class="e2-band">
                <div class="e2-band-scrollable">
                    <nav>
                        <?php if ($isAdmin): ?>
                        <div class="band-item">
                        <a href="/<?= htmlspecialchars($post['slug']) ?>#edit" class="e2-button">
                            <span class="e2-svgi">✎</span> Редагувати
                        
                        <div class="band-item">
                            <span title="<?= date('d.m.Y H:i', strtotime($post['created_at'])) ?>">
                                <?= date('Y', strtotime($post['created_at'])) ?>
                            </span>
                        </div>
                        
                        <?php
                        // Завантажуємо теги
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
                        } catch (PDOException $e) {}
                        ?>
                        
                        <?php foreach ($tags as $tag): ?>
                        <div class="band-item">
                            <a href="/tag/<?= urlencode($tag['name']) ?>" class="e2-tag">
                                <?= htmlspecialchars($tag['name']) ?>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </nav>
                </div>
            </footer>
        </article>
    <?php endforeach; ?>
    
    <!-- Навігація внизу (старіші пости) -->
    <?php if (isset($page) && isset($totalPages) && $page < $totalPages): ?>
        <div class="pagination">
            <a href="/?page=<?= $page + 1 ?>" class="pagination-link">
                Читати нижче
                <span class="e2-svgi">↓</span>
            </a>
        </div>
    <?php endif; ?>
    </div>

<?php endif; ?>