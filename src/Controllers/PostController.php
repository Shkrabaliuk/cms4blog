<?php
namespace App\Controllers;

use App\Config\Database;
use App\Services\Render;

class PostController
{
    public function show($slug)
    {
        $pdo = Database::connect();

        // Check if admin
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $isAdmin = isset($_SESSION['admin_id']);

        // Cache Check (only for non-admins)
        $cacheKey = 'post_' . $slug;
        if (!$isAdmin && $cached = \App\Services\Cache::get($cacheKey)) {
            // Still update views
            $pdo->prepare("UPDATE posts SET views = views + 1 WHERE slug = ?")->execute([$slug]);
            echo $cached;
            return;
        }

        // 1. Отримуємо пост
        $stmt = $pdo->prepare("SELECT * FROM posts WHERE slug = ? AND is_published = 1 LIMIT 1");
        $stmt->execute([$slug]);
        $post = $stmt->fetch();

        // 404 Not Found
        if (!$post) {
            http_response_code(404);
            require __DIR__ . '/../../templates/pages/404.php';
            return;
        }

        // 2. Лічильник переглядів (+1)
        $pdo->prepare("UPDATE posts SET views = views + 1 WHERE id = ?")->execute([$post['id']]);

        // ... logic continues ...

        // 3. Підготовка контенту (Markdown -> HTML)
        if (!empty($post['content_html'])) {
            $post['content'] = $post['content_html'];
        } else {
            $post['content'] = Render::html($post['content_raw'] ?? '');
        }

        // 4. Коментарі
        $stmt = $pdo->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY created_at ASC");
        $stmt->execute([$post['id']]);
        $comments = $stmt->fetchAll();

        // 5. Теги
        $tags = [];

        // 6. Глобальні налаштування
        $blogSettings = [];
        $settingsStmt = $pdo->query("SELECT `key`, `value` FROM settings");
        while ($row = $settingsStmt->fetch()) {
            $blogSettings[$row['key']] = $row['value'];
        }

        // Змінні для View
        $blogTitle = $blogSettings['site_title'] ?? 'Logos Blog';
        $pageTitle = $post['title'];

        // 7. Рендеринг
        ob_start();
        require __DIR__ . '/../../templates/partials/header.php';
        require __DIR__ . '/../../templates/pages/post.php';
        require __DIR__ . '/../../templates/partials/footer.php';
        $html = ob_get_clean();

        if (!$isAdmin) {
            \App\Services\Cache::set($cacheKey, $html);
        }
        echo $html;
    }
}
