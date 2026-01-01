<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$selected_tag = $_GET['tag'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));

$posts_per_page = intval(get_setting('posts_per_page', 10));

// Отримати всі унікальні теги
$stmt = $pdo->query("SELECT tags FROM posts WHERE tags != '' ORDER BY created_at DESC");
$all_tags_raw = $stmt->fetchAll(PDO::FETCH_COLUMN);

$tags_count = [];
foreach ($all_tags_raw as $tags_string) {
    $tags_array = parse_tags($tags_string);
    foreach ($tags_array as $tag) {
        if (!isset($tags_count[$tag])) {
            $tags_count[$tag] = 0;
        }
        $tags_count[$tag]++;
    }
}

arsort($tags_count);

$posts = [];
$total = 0;
if ($selected_tag) {
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE tags LIKE ? ORDER BY created_at DESC");
    $stmt->execute(["%$selected_tag%"]);
    $all_posts = $stmt->fetchAll();
    
    $filtered_posts = [];
    foreach ($all_posts as $post) {
        $post_tags = parse_tags($post['tags']);
        if (in_array($selected_tag, $post_tags)) {
            $filtered_posts[] = $post;
        }
    }
    
    $total = count($filtered_posts);
    $offset = ($page - 1) * $posts_per_page;
    $posts = array_slice($filtered_posts, $offset, $posts_per_page);
}

$total_pages = $selected_tag ? ceil($total / $posts_per_page) : 0;

$pageTitle = $selected_tag ? "Тег: $selected_tag" : "Теги";
require 'includes/templates/header.php';
?>

<h2><?= $selected_tag ? 'Пости з тегом: ' . htmlspecialchars($selected_tag) : 'Всі теги' ?></h2>

<?php if (!$selected_tag): ?>
    <div style="margin: 30px 0;">
        <?php foreach ($tags_count as $tag => $count): ?>
            <a href="?tag=<?= urlencode($tag) ?>" style="margin-right: 15px; font-size: <?= min(24, 14 + ($count * 2)) ?>px;">
                #<?= htmlspecialchars($tag) ?>
            </a>
        <?php endforeach; ?>
    </div>
    
    <p style="color: #999; margin-top: 40px;">Оберіть тег, щоб переглянути пости</p>
<?php else: ?>
    <p style="margin: 20px 0;"><a href="/tags.php">← Повернутися до всіх тегів</a></p>

    <?php if (count($posts) > 0): ?>
        <?php foreach ($posts as $post): ?>
        <article class="post">
            <h2><a href="/post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a></h2>
            <div class="post-meta">
                <i class="fa-regular fa-clock"></i> <?= time_ago($post['created_at']) ?>
                <?php if (!empty($post['tags'])): ?>
                    <?php foreach (parse_tags($post['tags']) as $tag): ?>
                        · <a href="?tag=<?= urlencode($tag) ?>"><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($tag) ?></a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="post-content">
                <?= markdown_excerpt($post['content'], 300) ?>
            </div>
        </article>
        <?php endforeach; ?>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?tag=<?= urlencode($selected_tag) ?>&page=<?= $page - 1 ?>"><i class="fa-solid fa-chevron-left"></i> Попередня</a>
            <?php endif; ?>
            <span>Сторінка <?= $page ?> з <?= $total_pages ?></span>
            <?php if ($page < $total_pages): ?>
                <a href="?tag=<?= urlencode($selected_tag) ?>&page=<?= $page + 1 ?>">Наступна <i class="fa-solid fa-chevron-right"></i></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-state">
            <p>Постів з цим тегом не знайдено</p>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require 'includes/templates/footer.php'; ?>
