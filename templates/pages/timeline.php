<?php
use App\Services\Render;
// $posts passed from Controller

if (empty($posts)): ?>
    <section>
        <p>Поки що тут тихо...</p>
    </section>
<?php else: ?>


    <?php foreach ($posts as $post): ?>
        <article>
            <?php if ($isAdmin): ?>
                <div class="admin-floating-actions">
                    <a href="/<?= htmlspecialchars($post['slug']) ?>#edit" class="btn-edit-sticky" title="Редагувати">
                        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </a>
                </div>
            <?php endif; ?>

            <header>
                <h2>
                    <a href="/<?= htmlspecialchars($post['slug']) ?>">
                        <?= htmlspecialchars($post['title']) ?>
                    </a>
                </h2>
            </header>

            <section>
                <?= Render::html($post['content']) ?>
            </section>

            <footer>
                <p class="post-meta">
                    <time datetime="<?= date('Y-m-d', strtotime($post['created_at'])) ?>">
                        <?= date('d.m.Y', strtotime($post['created_at'])) ?>
                    </time>

                    <?php
                    // Get comment count
                    try {
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ?");
                        $stmt->execute([$post['id']]);
                        $commentCount = (int) $stmt->fetchColumn();

                        if ($commentCount > 0) {
                            echo ' · ' . $commentCount . ' ' . ($commentCount === 1 ? 'коментар' : 'коментарів');
                        }
                    } catch (\PDOException $e) {
                        // Comments table issue
                    }
                    ?>

                    <?php
                    // Get tags (if table exists)
                    try {
                        $stmt = $pdo->prepare("
                            SELECT t.name
                            FROM tags t
                            JOIN post_tags pt ON t.id = pt.tag_id
                            WHERE pt.post_id = ?
                            ORDER BY t.name
                        ");
                        $stmt->execute([$post['id']]);
                        $tags = $stmt->fetchAll();

                        if (!empty($tags)) {
                            foreach ($tags as $tag) {
                                echo ' · <a href="/tag/' . urlencode($tag['name']) . '">#' . htmlspecialchars($tag['name']) . '</a>';
                            }
                        }
                    } catch (\PDOException $e) {
                        // Tags table doesn't exist yet
                    }
                    ?>
                </p>
            </footer>
        </article>
    <?php endforeach; ?>

    <!-- Pagination -->
    <?php if ($page > 1 || $page < $totalPages): ?>
        <nav class="pagination">

            <?php if ($page > 1): ?>
                <a href="/?page=<?= $page - 1 ?>">
                    ← Повернутись
                </a>
            <?php else: ?>
                <div></div> <!-- Spacer for flexbox -->
            <?php endif; ?>

            <?php if ($page < $totalPages): ?>
                <a href="/?page=<?= $page + 1 ?>">
                    Читати далі →
                </a>
            <?php else: ?>
                <div></div> <!-- Spacer for flexbox -->
            <?php endif; ?>

        </nav>
    <?php endif; ?>

<?php endif; ?>