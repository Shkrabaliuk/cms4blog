<?php
namespace App\Controllers;

use App\Config\Database;

class PageController
{
    public function about()
    {
        $pdo = Database::connect();

        // Check if admin (for header display)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $isAdmin = isset($_SESSION['admin_id']);

        // Cache Check
        $cacheKey = 'page_about';
        if (!$isAdmin && $cached = \App\Services\Cache::get($cacheKey)) {
            echo $cached;
            return;
        }

        // Get blog settings
        $settingsStmt = $pdo->query("SELECT `key`, `value` FROM settings");
        $blogSettings = [];
        while ($row = $settingsStmt->fetch()) {
            $blogSettings[$row['key']] = $row['value'];
        }

        $blogTitle = $blogSettings['site_title'] ?? 'About';
        $pageTitle = 'Про мене';

        // Render
        ob_start();
        require __DIR__ . '/../../templates/partials/header.php';
        require __DIR__ . '/../../templates/pages/about.php';
        require __DIR__ . '/../../templates/partials/footer.php';
        $html = ob_get_clean();

        if (!$isAdmin) {
            \App\Services\Cache::set($cacheKey, $html);
        }
        echo $html;
    }
}
