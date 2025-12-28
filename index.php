<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$posts = get_posts();
$pageTitle = "Головна";
require 'includes/header.php'; 
?>

<main>
    <?php if (count($posts) > 0): ?>
        <?php foreach ($posts as $post): ?>
            <article>
                <h2>
                    <a href="post.php?id=<?= $post['id'] ?>">
                        <?= htmlspecialchars($post['title']) ?>
                    </a>
                </h2>

                <div class="content">
                    <?= nl2br(htmlspecialchars(excerpt($post['content'], 300))) ?>
                </div>

                <div class="meta-bottom" style="display: flex; gap: 15px; align-items: center;">
                    <span><?= date('d.m.Y', strtotime($post['created_at'])) ?></span>
                    
                    <?php if (is_admin()): ?>
                        <a href="post-editor.php?id=<?= $post['id'] ?>" style="color: #d00; text-decoration: none; font-size: 12px;">✎ Редагувати</a>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>

    <?php else: ?>
        <div style="text-align: center; padding: 50px 0; color: #999;">
            <p>Тут поки порожньо.</p>
        </div>
    <?php endif; ?>
</main>

<?php require 'includes/footer.php'; ?>