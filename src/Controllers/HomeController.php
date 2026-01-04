<?php
namespace App\Controllers;

use App\Config\Database;
use App\Services\View;

class HomeController
{
    public function index()
    {
        $pdo = Database::connect();

        // Check if admin
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $isAdmin = isset($_SESSION['admin_id']);

        // Cache Check
        $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $cacheKey = 'home_page_' . $page;

        if (!$isAdmin && $cached = \App\Services\Cache::get($cacheKey)) {
            echo $cached;
            return;
        }

        // Settings (cache this in a real app)
        $stmt = $pdo->query("SELECT value FROM settings WHERE `key` = 'posts_per_page'");
        $postsPerPage = $stmt ? (int) $stmt->fetchColumn() : 10;
        if ($postsPerPage < 1)
            $postsPerPage = 10;

        // Pagination
        $offset = ($page - 1) * $postsPerPage;

        // Fetch Posts
        $stmt = $pdo->prepare("
            SELECT * FROM posts 
            WHERE is_published = 1 
            ORDER BY created_at DESC 
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':limit', $postsPerPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $posts = $stmt->fetchAll();

        // Prepare content field for each post
        foreach ($posts as &$post) {
            $post['content'] = $post['content_raw'] ?? '';
        }
        unset($post);

        // Total for pagination UI
        $total = $pdo->query("SELECT COUNT(*) FROM posts WHERE is_published = 1")->fetchColumn();
        $totalPages = ceil($total / $postsPerPage);

        // Global Blog Settings for Header
        $blogSettings = [];
        $settingsStmt = $pdo->query("SELECT `key`, `value` FROM settings");
        while ($row = $settingsStmt->fetch()) {
            $blogSettings[$row['key']] = $row['value'];
        }
        $blogTitle = $blogSettings['site_title'] ?? 'Logos Blog';

        // Render
        ob_start();
        require __DIR__ . '/../../templates/partials/header.php';
        require __DIR__ . '/../../templates/pages/timeline.php';
        require __DIR__ . '/../../templates/partials/footer.php';
        $html = ob_get_clean();

        if (!$isAdmin) {
            \App\Services\Cache::set($cacheKey, $html);
        }
        echo $html;
    }
}
