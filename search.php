<?php
require_once __DIR__ . '/config/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/SearchService.php';

$searchService = new SearchService($pdo);

// Отримання query параметра
$q = $_GET['q'] ?? '';
$results = [];

if (!empty($q)) {
    try {
        $results = $searchService->search($q, 20);
    } catch (Exception $e) {
        $error = "Помилка пошуку: " . $e->getMessage();
    }
}

// Завантажуємо назву блогу
$stmt = $pdo->query("SELECT `value` FROM settings WHERE `key` = 'blog_title'");
$blogTitle = $stmt->fetchColumn() ?: '/\\ogos';

$pageTitle = $q ? "Пошук: {$q} — {$blogTitle}" : "Пошук — {$blogTitle}";
?>
<!DOCTYPE html>
<html lang="uk" class="dark-mode">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <link rel="stylesheet" href="/assets/fonts/tildasans.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <style>
        .search-form {
            margin-bottom: 40px;
        }
        
        .search-input {
            width: 100%;
            padding: 12px;
            font-size: 18px;
            font-family: inherit;
            background: var(--inputBackgroundColor);
            border: 2px solid var(--thinRuleColor);
            border-radius: 6px;
            transition: border-color var(--time);
        }
        
        .search-input:focus {
            outline: none;
            border-color: var(--hoverColor);
        }
        
        .search-result {
            margin-bottom: 40px;
            padding-bottom: 40px;
            border-bottom: 1px solid var(--thinRuleColor);
        }
        
        .search-result:last-child {
            border-bottom: none;
        }
        
        .search-result-title {
            margin: 0 0 8px;
        }
        
        .search-result-title a {
            font-size: 28px;
            line-height: 32px;
            font-weight: bold;
            color: var(--headingsColor);
            border-bottom: 1px solid rgba(0, 0, 0, 0.15);
        }
        
        .search-result-snippet {
            font-size: 18px;
            line-height: 26px;
            margin-top: 12px;
        }
        
        .search-result-snippet mark {
            background: var(--markedTextBackground);
            color: var(--foregroundColor);
            font-weight: 600;
            padding: 2px 4px;
            border-radius: 2px;
        }
        
        .search-result-meta {
            font-size: var(--smallFontSize);
            color: #999;
            margin-top: 8px;
        }
        
        .search-stats {
            margin-bottom: 30px;
            color: #999;
            font-size: var(--smallFontSize);
        }
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .no-results i {
            font-size: 48px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <div class="logo">
            <a href="/">/\ogos <span>blog</span></a>
        </div>
    </header>

    <main>
        <h1 class="mb-24">Пошук</h1>
        
        <form method="GET" action="/search.php" class="search-form">
            <input 
                type="search" 
                name="q" 
                class="search-input"
                placeholder="Введіть запит..."
                value="<?= htmlspecialchars($q) ?>"
                autofocus
                autocomplete="off"
            >
        </form>
        
        <?php if (isset($error)): ?>
            <div class="error-box">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($q)): ?>
            <?php if (!empty($results)): ?>
                <div class="search-stats">
                    Знайдено результатів: <strong><?= count($results) ?></strong>
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
                            <i class="far fa-calendar"></i>
                            <?= date('d.m.Y', strtotime($result['date'])) ?>
                            •
                            <i class="fas fa-chart-line"></i>
                            Релевантність: <?= round($result['relevance'], 2) ?>
                        </div>
                    </article>
                <?php endforeach; ?>
                
            <?php else: ?>
                <div class="no-results">
                    <div><i class="far fa-frown"></i></div>
                    <h2>Нічого не знайдено</h2>
                    <p>За запитом «<?= htmlspecialchars($q) ?>» результатів немає.</p>
                    <p class="mt-20">
                        <a href="/" class="back-home-link">← Повернутися на головну</a>
                    </p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <footer>
        Powered by /\ogos + Rose Search — <?= date('Y') ?>
    </footer>
</div>

</body>
</html>
