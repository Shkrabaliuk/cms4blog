<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$id = $_GET['id'] ?? null;
$post = get_post($id);

if (!$post) {
    header("Location: index.php");
    exit;
}

$pageTitle = $post['title'];
require 'includes/header.php';
?>

<main>
    <article>
        <div style="margin-bottom: 20px;">
            <a href="index.php" style="color: #666; text-decoration: none; font-size: 14px;">← На головну</a>
        </div>

        <h1 style="margin-bottom: 20px;"><?= htmlspecialchars($post['title']) ?></h1>
        
        <div class="content" style="font-size: 18px; line-height: 1.7; color: #222;">
            <?= nl2br(htmlspecialchars($post['content'])) ?>
        </div>
        
        <div class="meta-bottom" style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; display: flex; gap: 15px;">
            <span><?= date('d.m.Y', strtotime($post['created_at'])) ?></span>
            <?php if (is_admin()): ?>
                <a href="post-editor.php?id=<?= $post['id'] ?>" style="color: #d00; text-decoration: none;">✎ Редагувати</a>
            <?php endif; ?>
        </div>
    </article>
</main>

<?php require 'includes/footer.php'; ?>