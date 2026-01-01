<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$page = max(1, intval($_GET['page'] ?? 1));
$per_page = (int)get_setting('posts_per_page', 10);

$posts = get_posts('', 'DESC', $page);
$total = get_total_posts('');
$total_pages = ceil($total / $per_page);

require 'includes/templates/header.php';
?>

<?php if (count($posts) > 0): ?>
    <?php foreach ($posts as $post): ?>
    <article class="post">
        <h2><a href="/post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h2>
        <div class="post-meta">
            <?= time_ago($post['created_at']) ?>
            <?php if (!empty($post['tags'])): ?>
                <?php foreach (parse_tags($post['tags']) as $tag): ?>
                    · <a href="/tags.php?tag=<?= urlencode($tag) ?>"><?= htmlspecialchars($tag) ?></a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="post-content">
            <?= markdown_excerpt($post['content'], 400) ?>
        </div>
    </article>
    <?php endforeach; ?>

    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>">← Новіші</a>
        <?php endif; ?>
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?>">Старіші →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
<?php else: ?>
    <div class="empty-state">
        <p>Поки що немає постів</p>
    </div>
<?php endif; ?>

<?php require 'includes/templates/footer.php'; ?>
