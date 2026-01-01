<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header("Location: /");
    exit;
}

$post = get_post_by_id($id);
if (!$post) {
    header("Location: /404.php");
    exit;
}

increment_post_views($id);

// Обробка коментарів
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $author = trim($_POST['author'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
    if ($author && $email && $content) {
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, author, email, content, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
        $stmt->execute([$id, $author, $email, $content]);
        header("Location: /post.php?id=$id&commented=1");
        exit;
    }
}

$comments = get_approved_comments($id);
$pageTitle = $post['title'];

require 'includes/templates/header.php';
?>

<article class="post post-single">
    <h2><?= htmlspecialchars($post['title']) ?></h2>
    <div class="post-meta">
        <?= date('d.m.Y', strtotime($post['created_at'])) ?>
        <?php if (!empty($post['tags'])): ?>
            <?php foreach (parse_tags($post['tags']) as $tag): ?>
                · <a href="/tags.php?tag=<?= urlencode($tag) ?>"><?= htmlspecialchars($tag) ?></a>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if (is_admin()): ?>
            · <a href="/admin/post-editor.php?id=<?= $id ?>">Редагувати</a>
        <?php endif; ?>
    </div>
    <div class="post-content">
        <?= markdown($post['content']) ?>
    </div>
</article>

<?php if (isset($_GET['commented'])): ?>
<div class="message success">
    Коментар додано. Він з'явиться після модерації.
</div>
<?php endif; ?>

<div class="comments">
    <h3>Коментарі (<?= count($comments) ?>)</h3>
    
    <?php if (count($comments) > 0): ?>
        <?php foreach ($comments as $comment): ?>
        <div class="comment">
            <div class="comment-author"><?= htmlspecialchars($comment['author']) ?></div>
            <div class="comment-date"><?= time_ago($comment['created_at']) ?></div>
            <div class="comment-content"><?= nl2br(htmlspecialchars($comment['content'])) ?></div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <h3>Додати коментар</h3>
    <form method="POST">
        <input type="hidden" name="comment" value="1">
        
        <div class="form-group">
            <label>Ім'я:</label>
            <input type="text" name="author" required>
        </div>
        
        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label>Коментар:</label>
            <textarea name="content" required></textarea>
        </div>
        
        <button type="submit">Надіслати</button>
    </form>
</div>

<?php require 'includes/templates/footer.php'; ?>
