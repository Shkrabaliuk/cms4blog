<?php
namespace App\Controllers;

use App\Config\Database;

class SearchController
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::connect();
    }

    public function index()
    {
        $q = $_GET['q'] ?? '';
        $results = [];
        $error = null;

        if (!empty($q)) {
            try {
                error_log("Search query: " . $q);

                // Use LIKE search for better Cyrillic support
                $stmt = $this->pdo->prepare("
                    SELECT 
                        id,
                        title,
                        slug,
                        SUBSTRING(content_raw, 1, 200) as snippet,
                        created_at as date
                    FROM posts 
                    WHERE is_published = 1 
                    AND (title LIKE ? OR content_raw LIKE ?)
                    ORDER BY 
                        CASE 
                            WHEN title LIKE ? THEN 1
                            ELSE 2
                        END,
                        created_at DESC
                    LIMIT 20
                ");
                $searchTerm = '%' . $q . '%';
                $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
                $results = $stmt->fetchAll();

                error_log("Search results count: " . count($results));

                // Add basic relevance score and format snippets with highlighting
                foreach ($results as &$result) {
                    $titleMatch = stripos($result['title'], $q) !== false ? 2 : 0;
                    // Find position of term in content
                    $pos = stripos($result['snippet'], $q);

                    // If term not in first 200 chars (which is what snippet currently is), we might need to look deeper
                    // But for now, let's just highlight what we have if possible. 
                    // ideally SQL should return full content or we use a better SQL snippet function if available (MySQL doesn't have a great one by default)
                    // Let's improve the SQL to fetch full content for better snippet generation PHP side for small scale
                    // Re-fetching content might be expensive, so keeping light.

                    // Highlight logic
                    $cleanSnippet = strip_tags($result['snippet']);
                    $safeSnippet = htmlspecialchars($cleanSnippet);
                    $safeQ = preg_quote(htmlspecialchars($q), '/');

                    $highlighted = preg_replace('/(' . $safeQ . ')/iu', '<mark>$1</mark>', $safeSnippet);

                    $result['relevance'] = $titleMatch + ($pos !== false ? 1 : 0);
                    $result['snippet'] = $highlighted . '...';
                }
                unset($result);

            } catch (\Exception $e) {
                error_log("Search error: " . $e->getMessage());
                $error = "Помилка пошуку";
            }
        }

        // Get blog settings
        $stmt = $this->pdo->query("SELECT `key`, `value` FROM settings");
        $blogSettings = [];
        while ($row = $stmt->fetch()) {
            $blogSettings[$row['key']] = $row['value'];
        }
        $blogTitle = $blogSettings['site_title'] ?? 'Logos Blog';
        $pageTitle = $q ? "Пошук: {$q} — {$blogTitle}" : "Пошук — {$blogTitle}";

        // Render view
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $isAdmin = isset($_SESSION['admin_id']);
        ob_start();
        include __DIR__ . '/../../templates/pages/search_results.php';
        $childView = ob_get_clean();
        require __DIR__ . '/../../templates/layouts/layout.php';
    }
}
