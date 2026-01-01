<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Перевірка CSRF токена
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Невалідний запит');
    }
    
    $post_id = intval($_POST['post_id']);
    $author = trim($_POST['author']);
    $content = trim($_POST['content']);
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    // Перевірка rate limit
    if (!check_comment_rate_limit($ip_address)) {
        die('Перевищено ліміт коментарів. Спробуйте пізніше (максимум 5 коментарів за 5 хвилин).');
    }
    
    // Validate that post exists
    $post = get_post($post_id);
    if (!$post) {
        header("Location: /404.php");
        exit;
    }
    
    // Validate input
    if ($post_id && $author && $content) {
        // Check length limits
        if (mb_strlen($author) > 100) {
            $author = mb_substr($author, 0, 100);
        }
        if (mb_strlen($content) > 5000) {
            $content = mb_substr($content, 0, 5000);
        }
        
        add_comment($post_id, $author, $content);
    }
    
    // Sanitize post_id for redirect
    $safe_post_id = intval($post_id);
    header("Location: /post.php?id=" . $safe_post_id . "&comment=pending");
    exit;
}
