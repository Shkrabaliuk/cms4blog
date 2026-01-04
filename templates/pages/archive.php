<?php
// templates/archive.php
// Expected variables: $postsByYear (array of years -> posts)
?>

<div class="archive-container">
    <header class="archive-header">
        <h1>
            Всі статті
            <sup class="rss-sup"><a href="/rss.php">RSS</a></sup>
        </h1>
        <p class="archive-intro">
            Статті про розробку, дизайн, життя та інші цікаві теми.
        </p>
    </header>

    <?php foreach ($postsByYear as $year => $posts): ?>
        <section class="archive-year-section">
            <h2>
                <?= htmlspecialchars($year) ?>
            </h2>
            <ul class="archive-posts-list">
                <?php foreach ($posts as $post): ?>
                    <li>
                        <time datetime="<?= date('Y-m-d\TH:i', strtotime($post['created_at'])) ?>">
                            <?= date('d.m', strtotime($post['created_at'])) ?>
                        </time>
                        <span>
                            <a href="/<?= htmlspecialchars($post['slug']) ?>">
                                <?= htmlspecialchars($post['title']) ?>
                            </a>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endforeach; ?>
</div>