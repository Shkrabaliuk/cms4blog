<?php
/**
 * Search Results Page
 * Minimalist design - shows only essential information
 */

use App\Services\View;

$blogTitle = $blogSettings['site_title'] ?? '/\\ogos';
$searchQuery = htmlspecialchars($q ?? '');
$pageTitle = $searchQuery ? "Пошук: {$searchQuery} — {$blogTitle}" : "Пошук — {$blogTitle}";

ob_start();
?>

<div class="search-page">
    <h1>Пошук</h1>

    <form action="/search.php" method="get" style="margin-bottom: 2rem;">
        <input type="search" name="q" value="<?= $searchQuery ?>" placeholder="Введіть запит..." required autofocus>
        <button type="submit">Шукати</button>
    </form>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <?= View::icon('info') ?>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php elseif (!empty($q)): ?>
        <?php if (empty($results)): ?>
            <p style="color: #999;">Нічого не знайдено за запитом "<strong><?= $searchQuery ?></strong>"</p>
        <?php else: ?>
            <p style="color: #999; margin-bottom: 1.5rem;">
                Знайдено: <strong><?= count($results) ?></strong>
            </p>

            <?php foreach ($results as $result): ?>
                <article style="margin-bottom: 2rem;">
                    <h2 style="margin-bottom: 0.5rem;">
                        <a href="/<?= htmlspecialchars($result['slug']) ?>">
                            <?= htmlspecialchars($result['title']) ?>
                        </a>
                    </h2>

                    <?php if (!empty($result['snippet'])): ?>
                        <p style="color: #666; margin: 0.5rem 0;">
                            <?= $result['snippet'] ?>
                        </p>
                    <?php endif; ?>

                    <small style="color: #999;">
                        <?= date('d.m.Y', strtotime($result['date'])) ?>
                    </small>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php else: ?>
        <p style="color: #999;">Введіть запит для пошуку</p>
    <?php endif; ?>
</div>