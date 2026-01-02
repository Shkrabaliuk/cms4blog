<?php
// index.php - Front Controller

require_once 'config/autoload.php';
require_once 'config/db.php';

// Ініціалізуємо сесію
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Отримання URL
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$path = trim($path, '/');

// --- РОУТИНГ ---

// 1. ГОЛОВНА СТОРІНКА (СТРІЧКА)
if ($path === '' || $path === 'index.php') {
    // Pagination параметри - завантажуємо з налаштувань
    $stmt = $pdo->query("SELECT value FROM settings WHERE `key` = 'posts_per_page'");
    $postsPerPage = $stmt ? (int)$stmt->fetchColumn() : 10;
    if ($postsPerPage < 1) $postsPerPage = 10;
    
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($page - 1) * $postsPerPage;
    
    // Підраховуємо загальну кількість постів
    $totalStmt = $pdo->query("SELECT COUNT(*) FROM posts WHERE is_published = 1");
    $totalPosts = $totalStmt->fetchColumn();
    $totalPages = ceil($totalPosts / $postsPerPage);
    
    // Отримуємо пости для поточної сторінки
    $stmt = $pdo->prepare("
        SELECT * FROM posts 
        WHERE is_published = 1 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$postsPerPage, $offset]);
    $posts = $stmt->fetchAll();

    // Завантажуємо назву блогу
    $stmt = $pdo->query("SELECT `value` FROM settings WHERE `key` = 'blog_title'");
    $blogTitle = $stmt->fetchColumn() ?: '/\\ogos';
    $blogTagline = $pdo->query("SELECT `value` FROM settings WHERE `key` = 'blog_tagline'")->fetchColumn();
    
    $pageTitle = $blogTagline ? "{$blogTitle} — {$blogTagline}" : $blogTitle;
    $childView = 'views/timeline.php';
    
    require 'views/layout.php';
    exit;
}

// 2. СТОРІНКА ОДНОГО ПОСТА
// Шукаємо пост за слагом
$stmt = $pdo->prepare("SELECT * FROM posts WHERE slug = ? AND is_published = 1");
$stmt->execute([$path]);
$post = $stmt->fetch();

if ($post) {
    // Знайшли пост, тепер завантажуємо теги (якщо таблиця існує)
    $tags = [];
    try {
        $stmt = $pdo->prepare("
            SELECT t.* 
            FROM tags t
            JOIN post_tags pt ON t.id = pt.tag_id
            WHERE pt.post_id = ?
            ORDER BY t.name
        ");
        $stmt->execute([$post['id']]);
        $tags = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Таблиця tags не існує - ігноруємо
    }
    
    // Завантажуємо коментарі
    $stmt = $pdo->prepare("
        SELECT * 
        FROM comments 
        WHERE post_id = ? 
        ORDER BY created_at ASC
    ");
    $stmt->execute([$post['id']]);
    $comments = $stmt->fetchAll();
    
    // Перевіряємо чи є помилка форми коментарів
    $error = $_SESSION['comment_error'] ?? null;
    $commentData = $_SESSION['comment_data'] ?? [];
    
    // Очищаємо сесію
    unset($_SESSION['comment_error']);
    unset($_SESSION['comment_data']);
    
    // Завантажуємо назву блогу
    $stmt = $pdo->query("SELECT `value` FROM settings WHERE `key` = 'blog_title'");
    $blogTitle = $stmt->fetchColumn() ?: '/\\ogos';
    
    $pageTitle = $post['title'] . " — " . $blogTitle;
    $childView = 'views/post.php';
    
    require 'views/layout.php';
    exit;
}

// 3. 404
http_response_code(404);
echo "404 - Not Found";