<?php
namespace App\Controllers\Admin;

use App\Config\Database;
use App\Services\Auth;
use App\Services\Csrf;

class PostController
{
    private $pdo;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        Auth::require();
        $this->pdo = Database::connect();
    }

    public function newPost()
    {
        // Load blog settings
        $stmt = $this->pdo->query("SELECT `key`, `value` FROM settings");
        $blogSettings = [];
        while ($row = $stmt->fetch()) {
            $blogSettings[$row['key']] = $row['value'];
        }

        $isAdmin = true;
        require __DIR__ . '/../../../templates/pages/admin_new_post.php';
    }

    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        // Verify CSRF
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            die('Невірний CSRF токен');
        }

        $post_id = $_POST['post_id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $content = $_POST['content'] ?? '';
        $is_published = isset($_POST['is_published']) ? 1 : 0;

        // Auto-generate slug if empty (Server-side fallback)
        if (empty($slug) && !empty($title)) {
            $slug = self::slugify($title);
        }

        // Validation
        if (empty($title) || empty($slug) || empty($content)) {
            die('Всі поля обов\'язкові');
        }

        // Check slug format
        if (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
            die('Slug може містити тільки латинські літери, цифри та дефіси');
        }

        try {
            // Get admin user_id
            $user_id = $_SESSION['admin_id'] ?? 1;

            if ($post_id) {
                // Update existing post
                $stmt = $this->pdo->prepare("
                    UPDATE posts 
                    SET title = ?, slug = ?, content_raw = ?, is_published = ?
                    WHERE id = ?
                ");
                $stmt->execute([$title, $slug, $content, $is_published, $post_id]);

                // Redirect to updated URL
                header("Location: /$slug");
            } else {
                // Create new post
                $stmt = $this->pdo->prepare("
                    INSERT INTO posts (user_id, title, slug, content_raw, is_published) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$user_id, $title, $slug, $content, $is_published]);

                header("Location: /$slug");
            }
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                die('Помилка: пост з таким slug вже існує');
            }
            error_log("Save post error: " . $e->getMessage());
            die('Помилка збереження посту');
        }
        exit;
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            die('Невірний CSRF токен');
        }

        $post_id = $_POST['post_id'] ?? null;

        if ($post_id) {
            try {
                $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = ?");
                $stmt->execute([$post_id]);
            } catch (\PDOException $e) {
                error_log("Delete post error: " . $e->getMessage());
                die('Помилка видалення посту');
            }
        }

        header('Location: /');
        exit;
    }

    private static function slugify($text)
    {
        // Simple Cyrillic to Latin transliteration
        $cyr = [
            'а',
            'б',
            'в',
            'г',
            'ґ',
            'д',
            'е',
            'є',
            'ж',
            'з',
            'и',
            'і',
            'ї',
            'й',
            'к',
            'л',
            'м',
            'н',
            'о',
            'п',
            'р',
            'с',
            'т',
            'у',
            'ф',
            'х',
            'ц',
            'ч',
            'ш',
            'щ',
            'ь',
            'ю',
            'я',
            'А',
            'Б',
            'В',
            'Г',
            'Ґ',
            'Д',
            'Е',
            'Є',
            'Ж',
            'З',
            'И',
            'І',
            'Ї',
            'Й',
            'К',
            'Л',
            'М',
            'Н',
            'О',
            'П',
            'Р',
            'С',
            'Т',
            'У',
            'Ф',
            'Х',
            'Ц',
            'Ч',
            'Ш',
            'Щ',
            'Ь',
            'Ю',
            'Я'
        ];
        $lat = [
            'a',
            'b',
            'v',
            'h',
            'g',
            'd',
            'e',
            'ye',
            'zh',
            'z',
            'y',
            'i',
            'yi',
            'y',
            'k',
            'l',
            'm',
            'n',
            'o',
            'p',
            'r',
            's',
            't',
            'u',
            'f',
            'kh',
            'ts',
            'ch',
            'sh',
            'shch',
            '',
            'yu',
            'ya',
            'A',
            'B',
            'V',
            'H',
            'G',
            'D',
            'E',
            'Ye',
            'Zh',
            'Z',
            'Y',
            'I',
            'Yi',
            'Y',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'R',
            'S',
            'T',
            'U',
            'F',
            'Kh',
            'Ts',
            'Ch',
            'Sh',
            'Shch',
            '',
            'Yu',
            'Ya'
        ];

        $text = str_replace($cyr, $lat, $text);
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s\-]/', '', $text);
        $text = preg_replace('/\s+/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }
}
