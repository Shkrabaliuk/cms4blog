<?php
namespace App\Controllers;

use App\Config\Database;

class TagController
{
    public function show($tagName)
    {
        $pdo = Database::connect();

        // Check if admin
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $isAdmin = isset($_SESSION['admin_id']);

        // Decode tag name form URL
        $tagNameDecoded = urldecode($tagName);

        // Fetch posts by tag
        $stmt = $pdo->prepare("
            SELECT p.* 
            FROM posts p
            JOIN post_tags pt ON p.id = pt.post_id
            JOIN tags t ON pt.tag_id = t.id
            WHERE t.name = ? AND p.is_published = 1
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$tagNameDecoded]);
        $posts = $stmt->fetchAll();

        // Populate content/tags for timeline view (reusing logic is good)
        foreach ($posts as &$post) {
            $post['content'] = $post['content_raw'] ?? ''; // simplified for list view
        }
        unset($post);

        // Global Settings
        $settingsStmt = $pdo->query("SELECT `key`, `value` FROM settings");
        $blogSettings = [];
        while ($row = $settingsStmt->fetch()) {
            $blogSettings[$row['key']] = $row['value'];
        }
        $blogTitle = $blogSettings['site_title'] ?? 'Logos Blog';

        $pageTitle = "#" . htmlspecialchars($tagNameDecoded) . " — " . $blogTitle;
        $page = 1; // Simplify pagination for tags for now
        $totalPages = 1;

        // Render using existing Timeline template
        ob_start();
        require __DIR__ . '/../../templates/partials/header.php';
        require __DIR__ . '/../../templates/pages/timeline.php';
        require __DIR__ . '/../../templates/partials/footer.php';
        echo ob_get_clean();
    }
}
