<?php
require_once __DIR__ . '/config/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/SearchService.php';

$searchService = new SearchService($pdo);

$q = $_GET['q'] ?? '';
$results = [];

if (!empty($q)) {
    try {
        $results = $searchService->search($q, 20);
    } catch (Exception $e) {
        $error = "Помилка пошуку: " . $e->getMessage();
    }
}

// Налаштування
$stmt = $pdo->query("SELECT `key`, `value` FROM settings");
$blogSettings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $blogSettings[$row['key']] = $row['value'];
}
$blogTitle = $blogSettings['blog_title'] ?? '/\\ogos';
$pageTitle = $q ? "Пошук: {$q} — {$blogTitle}" : "Пошук — {$blogTitle}";

// Контент сторінки
ob_start();
?>

<h1>Пошук</h1>

<form method="GET" action="/search.php" class="search-form" style="margin-bottom:40px">
    <input 
        type="search" 
        name="q" 
        placeholder="Введіть запит..."
        value="<?= htmlspecialchars($q) ?>"
        autofocus
        style="width:100%;padding:12px;font-size:18px;border:2px solid var(--border);border-radius:6px"
    >
</form>

<?php if (isset($error)): ?>
    <div class="error-message">
        <i class="fas fa-exclamation-triangle"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<?php if (!empty($q)): ?>
    <?php if (!empty($results)): ?>
        <div class="search-stats">
            Знайдено: <strong><?= count($results) ?></strong>
        </div>
        
        <?php foreach ($results as $result): ?>
            <article class="search-result">
                <h2 class="search-result-title">
                    <a href="/<?= htmlspecialchars($result['slug']) ?>">
                        <?= htmlspecialchars($result['title']) ?>
                    </a>
                </h2>
                
                <div class="search-result-snippet">
                    <?= $result['snippet'] ?>
                </div>
                
                <div class="search-result-meta">
                    <?= date('d.m.Y', strtotime($result['date'])) ?>
                    • Релевантність: <?= round($result['relevance'], 2) ?>
                </div>
            </article>
        <?php endforeach; ?>
        
    <?php else: ?>
        <div class="no-results">
            <h2>Нічого не знайдено</h2>
            <p>За запитом «<?= htmlspecialchars($q) ?>» результатів немає.</p>
            <p><a href="/">← Повернутися на головну</a></p>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php
$childView = ob_get_clean();
require __DIR__ . '/views/layout.php';
