<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_admin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function get_setting($key, $default = '') {
    global $pdo;
    if (!isset($pdo)) return $default;
    
    try {
        $stmt = $pdo->prepare("SELECT value FROM settings WHERE `key` = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}

function set_setting($key, $value) {
    global $pdo;
    if (!isset($pdo)) return false;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO settings (`key`, value) VALUES (?, ?) 
                               ON DUPLICATE KEY UPDATE value = ?");
        $stmt->execute([$key, $value, $value]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function get_posts($search = '', $order = 'DESC', $page = 1) {
    global $pdo;
    if (!isset($pdo)) return [];
    
    $per_page = (int)get_setting('posts_per_page', 10);
    $offset = ($page - 1) * $per_page;
    
    $sql = "SELECT * FROM posts";
    $params = [];
    
    if ($search) {
        $sql .= " WHERE title LIKE ? OR content LIKE ? OR tags LIKE ?";
        $searchTerm = "%$search%";
        $params = [$searchTerm, $searchTerm, $searchTerm];
    }
    
    // ВАЖЛИВО: LIMIT і OFFSET вставляємо безпосередньо, не через placeholder
    $sql .= " ORDER BY created_at $order LIMIT $per_page OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function get_total_posts($search = '') {
    global $pdo;
    if (!isset($pdo)) return 0;
    
    $sql = "SELECT COUNT(*) FROM posts";
    if ($search) {
        $sql .= " WHERE title LIKE ? OR content LIKE ? OR tags LIKE ?";
        $searchTerm = "%$search%";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    } else {
        $stmt = $pdo->query($sql);
    }
    return $stmt->fetchColumn();
}

function get_post($id) {
    global $pdo;
    if (!isset($pdo)) return null;
    
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function estimate_reading_time($text) {
    $words = str_word_count(strip_tags($text));
    $minutes = ceil($words / 200);
    return $minutes > 0 ? $minutes : 1;
}

function excerpt($text, $limit = 300) {
    $text = strip_tags($text);
    if (mb_strlen($text) <= $limit) return $text;
    $text = mb_substr($text, 0, $limit);
    $lastSpace = mb_strrpos($text, ' ');
    if ($lastSpace !== false) {
        $text = mb_substr($text, 0, $lastSpace);
    }
    return $text . '...';
}

function markdown($text) {
    $text = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $text);
    $text = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $text);
    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);
    $text = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2">$1</a>', $text);
    $text = preg_replace('/^- (.+)$/m', '<li>$1</li>', $text);
    $text = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $text);
    $text = '<p>' . preg_replace('/\n\n/', '</p><p>', $text) . '</p>';
    return $text;
}

function time_ago($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'щойно';
    if ($diff < 3600) return floor($diff / 60) . ' хв тому';
    if ($diff < 86400) return floor($diff / 3600) . ' год тому';
    if ($diff < 604800) return floor($diff / 86400) . ' дн тому';
    
    return date('d.m.Y', $time);
}

function parse_tags($tags_string) {
    if (empty($tags_string)) return [];
    return array_map('trim', explode(',', $tags_string));
}

function format_tags($tags_array) {
    if (empty($tags_array)) return '';
    return implode(', ', $tags_array);
}

/**
 * Генерує title сторінки
 */
function generate_page_title($pageTitle = '', $blog_name = '') {
    $blog_name = $blog_name ?: get_setting('blog_name', 'Мій Блог');
    
    // Головна сторінка
    if (empty($pageTitle)) {
        $subtitle = get_setting('blog_subtitle', '');
        return $subtitle ? "$blog_name — $subtitle" : $blog_name;
    }
    
    // Пошук
    if (strpos($pageTitle, 'Пошук: ') === 0) {
        $query = trim(str_replace('Пошук:', '', $pageTitle));
        return "«$query» — Пошук — $blog_name";
    }
    
    // 404
    if (strpos($pageTitle, '404') === 0) {
        return "Сторінку не знайдено — $blog_name";
    }
    
    // Звичайна сторінка (пост)
    return "$pageTitle — $blog_name";
}

/**
 * Отримати коментарі до поста
 */
function get_comments($post_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, post_id, author, content, created_at FROM comments WHERE post_id = ? ORDER BY created_at ASC");
    $stmt->execute([$post_id]);
    return $stmt->fetchAll();
}

/**
 * Додати коментар
 */
function add_comment($post_id, $author, $content) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, author, content) VALUES (?, ?, ?)");
    return $stmt->execute([$post_id, $author, $content]);
}
