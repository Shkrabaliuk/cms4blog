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
        error_log("get_setting error: " . $e->getMessage());
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
        error_log("set_setting error: " . $e->getMessage());
        return false;
    }
}

function get_posts($search = '', $order = 'DESC', $page = 1) {
    global $pdo;
    if (!isset($pdo)) return [];
    
    // Захист від SQL injection - дозволяємо тільки ASC або DESC
    $allowed_orders = ['ASC', 'DESC'];
    $order = in_array(strtoupper($order), $allowed_orders) ? strtoupper($order) : 'DESC';
    
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
    // Gallery shortcode: [gallery]img1.jpg|Caption 1,img2.jpg|Caption 2[/gallery]
    $text = preg_replace_callback('/\[gallery\](.*?)\[\/gallery\]/s', function($matches) {
        $items = explode(',', $matches[1]);
        $images = [];
        foreach ($items as $item) {
            $item = trim($item);
            if (strpos($item, '|') !== false) {
                list($src, $caption) = explode('|', $item, 2);
                $images[trim($src)] = trim($caption);
            } else {
                $images[] = trim($item);
            }
        }
        return gallery($images);
    }, $text);
    
    // Code blocks з ```
    $text = preg_replace('/```([a-z]*)\n([\s\S]*?)```/', '<pre><code>$2</code></pre>', $text);
    
    // Inline code з `
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
    
    // Заголовки
    $text = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $text);
    $text = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $text);
    
    // Blockquotes
    $text = preg_replace('/^> (.+)$/m', '<blockquote>$1</blockquote>', $text);
    
    // Жирний та курсив
    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $text);
    
    // Посилання
    $text = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2">$1</a>', $text);
    
    // Нумеровані списки
    $text = preg_replace('/^\d+\. (.+)$/m', '<li>$1</li>', $text);
    
    // Марковані списки
    $text = preg_replace('/^- (.+)$/m', '<li>$1</li>', $text);
    
    // Обгортаємо списки в ul/ol
    $text = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $text);
    
    // Параграфи
    $text = '<p>' . preg_replace('/\n\n/', '</p><p>', $text) . '</p>';
    
    return $text;
}

function markdown_excerpt($text, $limit = 300) {
    // Спочатку обробляємо маркдаун
    $html = markdown($text);
    // Потім обрізаємо
    $plain = strip_tags($html);
    if (mb_strlen($plain) <= $limit) return $html;
    
    // Обрізаємо текст
    $plain = mb_substr($plain, 0, $limit);
    $lastSpace = mb_strrpos($plain, ' ');
    if ($lastSpace !== false) {
        $plain = mb_substr($plain, 0, $lastSpace);
    }
    
    // Повертаємо маркдаун для обрізаного тексту
    $lines = explode("\n", $text);
    $result = '';
    $current_length = 0;
    
    foreach ($lines as $line) {
        $line_length = mb_strlen(strip_tags($line));
        if ($current_length + $line_length > $limit) {
            break;
        }
        $result .= $line . "\n";
        $current_length += $line_length;
    }
    
    return markdown($result) . '<p>...</p>';
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
function get_comments($post_id, $status = 'approved') {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, post_id, author, content, created_at, status FROM comments WHERE post_id = ? AND status = ? ORDER BY created_at ASC");
    $stmt->execute([$post_id, $status]);
    return $stmt->fetchAll();
}

/**
 * Отримати всі коментарі (для адміна)
 */
function get_all_comments($status = null) {
    global $pdo;
    if ($status) {
        $stmt = $pdo->prepare("SELECT c.*, p.title as post_title FROM comments c LEFT JOIN posts p ON c.post_id = p.id WHERE c.status = ? ORDER BY c.created_at DESC");
        $stmt->execute([$status]);
    } else {
        $stmt = $pdo->query("SELECT c.*, p.title as post_title FROM comments c LEFT JOIN posts p ON c.post_id = p.id ORDER BY c.created_at DESC");
    }
    return $stmt->fetchAll();
}

/**
 * Додати коментар
 */
function add_comment($post_id, $author, $content) {
    global $pdo;
    // Нові коментарі за замовчуванням мають статус 'pending'
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, author, content, status) VALUES (?, ?, ?, 'pending')");
    return $stmt->execute([$post_id, $author, $content]);
}

/**
 * Змінити статус коментаря
 */
function moderate_comment($comment_id, $status) {
    global $pdo;
    $allowed_statuses = ['pending', 'approved', 'rejected'];
    if (!in_array($status, $allowed_statuses)) {
        return false;
    }
    $stmt = $pdo->prepare("UPDATE comments SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $comment_id]);
}

/**
 * Видалити коментар
 */
function delete_comment($comment_id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    return $stmt->execute([$comment_id]);
}

/**
 * Створити новий пост
 */
function create_post($title, $content, $tags = '') {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO posts (title, content, tags) VALUES (?, ?, ?)");
    return $stmt->execute([$title, $content, $tags]);
}

/**
 * Оновити пост
 */
function update_post($id, $title, $content, $tags = '') {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, tags = ? WHERE id = ?");
    return $stmt->execute([$title, $content, $tags, $id]);
}

/**
 * Видалити пост
 */
function delete_post($id) {
    global $pdo;
    
    // Спочатку видаляємо коментарі до цього поста
    $stmt = $pdo->prepare("DELETE FROM comments WHERE post_id = ?");
    $stmt->execute([$id]);
    
    // Потім видаляємо сам пост
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    return $stmt->execute([$id]);
}

/**
 * Генерувати CSRF токен
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Перевірити CSRF токен
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Перевірити rate limit для коментарів
 */
function check_comment_rate_limit($ip_address) {
    // Використовуємо сесію для простого rate limiting
    if (!isset($_SESSION['comment_timestamps'])) {
        $_SESSION['comment_timestamps'] = [];
    }
    
    $now = time();
    $five_minutes_ago = $now - 300;
    
    // Видаляємо старі мітки часу
    $_SESSION['comment_timestamps'] = array_filter(
        $_SESSION['comment_timestamps'],
        function($timestamp) use ($five_minutes_ago) {
            return $timestamp > $five_minutes_ago;
        }
    );
    
    // Перевірка: не більше 5 коментарів за 5 хвилин
    if (count($_SESSION['comment_timestamps']) >= 5) {
        return false;
    }
    
    // Додаємо поточний час
    $_SESSION['comment_timestamps'][] = $now;
    
    return true;
}

/**
 * Збільшити лічильник переглядів поста
 */
function increment_post_views($post_id) {
    global $pdo;
    
    // Перевіряємо чи користувач вже переглядав цей пост в цій сесії
    if (!isset($_SESSION['viewed_posts'])) {
        $_SESSION['viewed_posts'] = [];
    }
    
    // Якщо вже переглядав - не збільшуємо
    if (in_array($post_id, $_SESSION['viewed_posts'])) {
        return;
    }
    
    // Додаємо до списку переглянутих
    $_SESSION['viewed_posts'][] = $post_id;
    
    // Збільшуємо лічильник
    try {
        $stmt = $pdo->prepare("UPDATE posts SET view_count = view_count + 1 WHERE id = ?");
        $stmt->execute([$post_id]);
    } catch (PDOException $e) {
        // Якщо колонка view_count не існує - ігноруємо
        error_log("increment_post_views error: " . $e->getMessage());
    }
}

/**
 * Отримати кількість переглядів поста
 */
function get_post_views($post_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT view_count FROM posts WHERE id = ?");
        $stmt->execute([$post_id]);
        $result = $stmt->fetch();
        return $result ? (int)$result['view_count'] : 0;
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Create Fotorama gallery from images array
 * Usage: gallery(['img1.jpg' => 'Caption 1', 'img2.jpg' => 'Caption 2'])
 */
function gallery($images, $options = []) {
    $defaults = [
        'width' => '100%',
        'ratio' => '16/9',
        'nav' => 'thumbs',
        'allowfullscreen' => true,
    ];
    
    $options = array_merge($defaults, $options);
    
    $attrs = [];
    foreach ($options as $key => $value) {
        if (is_bool($value)) {
            $attrs[] = $value ? $key : '';
        } else {
            $attrs[] = 'data-' . $key . '="' . htmlspecialchars($value) . '"';
        }
    }
    
    $html = '<div class="fotorama" ' . implode(' ', array_filter($attrs)) . '>';
    
    foreach ($images as $src => $caption) {
        if (is_numeric($src)) {
            $src = $caption;
            $caption = '';
        }
        $html .= '<img src="' . htmlspecialchars($src) . '"';
        if ($caption) {
            $html .= ' data-caption="' . htmlspecialchars($caption) . '"';
        }
        $html .= '>';
    }
    
    $html .= '</div>';
    
    return $html;
}
