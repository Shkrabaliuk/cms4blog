<?php
session_start();

if (!file_exists('config.php')) {
    header("Location: install/install.php");
    exit;
}

require 'includes/db.php';
require 'includes/functions.php';

$selected_tag = $_GET['tag'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));

$blog_name = get_setting('blog_name', 'Мій Блог');
$blog_subtitle = get_setting('blog_subtitle', 'Простий блог на PHP');
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

// Сортування тегів за кількістю постів
arsort($tags_count);

// Пости з вибраним тегом
$posts = [];
$total = 0;
if ($selected_tag) {
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE tags LIKE ? ORDER BY created_at DESC");
    $stmt->execute(["%$selected_tag%"]);
    $all_posts = $stmt->fetchAll();
    
    // Фільтруємо точні збіги тегів
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

require 'includes/templates/header.php';
?>

<div class="e2-heading">
  <h2><?= $selected_tag ? 'Пости з тегом: ' . htmlspecialchars($selected_tag) : 'Всі теги' ?></h2>
</div>

<?php if (!$selected_tag): ?>
  <!-- Хмара тегів -->
  <div class="tags-cloud">
    <?php foreach ($tags_count as $tag => $count): 
      $size = min(24, 14 + ($count * 2));
    ?>
      <a href="?tag=<?= urlencode($tag) ?>" 
         class="tag-link"
         style="font-size: <?= $size ?>px;"
         title="<?= $count ?> <?= $count == 1 ? 'пост' : 'постів' ?>">
        #<?= htmlspecialchars($tag) ?>
      </a>
    <?php endforeach; ?>
  </div>

  <p class="tags-description">
    Оберіть тег, щоб переглянути всі пости з цим тегом.
  </p>

<?php else: ?>
  <!-- Пости з вибраним тегом -->
  <p class="tags-back-link">
    <a href="tags.php">← Повернутися до всіх тегів</a>
  </p>

  <?php if (count($posts) > 0): ?>
    <?php foreach ($posts as $post): ?>
      <article class="e2-entry">
        <header class="e2-entry-header">
          <h3 class="e2-entry-title">
            <a href="post.php?id=<?= $post['id'] ?>"><?= htmlspecialchars($post['title']) ?></a>
          </h3>
          <div class="e2-entry-published">
            <time class="published" datetime="<?= $post['created_at'] ?>">
              <?= format_date($post['created_at']) ?>
            </time>
            <?php if ($post['tags']): ?>
              <span class="post-tags">
                <?php foreach (parse_tags($post['tags']) as $tag): ?>
                  <a href="?tag=<?= urlencode($tag) ?>" class="post-tag">#<?= htmlspecialchars($tag) ?></a>
                <?php endforeach; ?>
              </span>
            <?php endif; ?>
          </div>
        </header>
        <div class="e2-entry-content">
          <?= markdown_excerpt($post['content']) ?>
        </div>
      </article>
    <?php endforeach; ?>

    <?php if ($total_pages > 1): ?>
      <div class="e2-pages">
        <?php if ($page > 1): ?>
          <a href="?tag=<?= urlencode($selected_tag) ?>&page=<?= $page - 1 ?>">← Попередня</a>
        <?php endif; ?>
        
        <?php if ($page < $total_pages): ?>
          <a href="?tag=<?= urlencode($selected_tag) ?>&page=<?= $page + 1 ?>">Наступна →</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  <?php else: ?>
    <p>Постів з цим тегом не знайдено.</p>
  <?php endif; ?>

<?php endif; ?>

<?php require 'includes/templates/footer.php'; ?>
