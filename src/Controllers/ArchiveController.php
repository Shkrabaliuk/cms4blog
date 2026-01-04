<?php
namespace App\Controllers;

use App\Config\Database;

class ArchiveController
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
        $cacheKey = 'archive_page';
        if (!$isAdmin && $cached = \App\Services\Cache::get($cacheKey)) {
            echo $cached;
            return;
        }

        // Fetch all published posts ordered by newest first
        $stmt = $pdo->query("SELECT * FROM posts WHERE is_published = 1 ORDER BY created_at DESC");
        $allPosts = $stmt->fetchAll();

        // Group posts by year
        $postsByYear = [];
        foreach ($allPosts as $post) {
            $year = date('Y', strtotime($post['created_at']));
            $postsByYear[$year][] = $post;
        }

        // Get blog settings
        $settingsStmt = $pdo->query("SELECT `key`, `value` FROM settings");
        $blogSettings = [];
        while ($row = $settingsStmt->fetch()) {
            $blogSettings[$row['key']] = $row['value'];
        }

        $blogTitle = $blogSettings['site_title'] ?? 'Archive';
        $pageTitle = 'Всі статті';

        // Render
        ob_start();
        require __DIR__ . '/../../templates/partials/header.php';
        require __DIR__ . '/../../templates/pages/archive.php';
        require __DIR__ . '/../../templates/partials/footer.php';
        $html = ob_get_clean();

        if (!$isAdmin) {
            \App\Services\Cache::set($cacheKey, $html);
        }
        echo $html;
    }
}
