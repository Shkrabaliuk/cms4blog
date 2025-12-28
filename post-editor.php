<?php
// post-editor.php (лежить в корені)
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!is_admin()) { header("Location: index.php"); exit; }

$id = $_GET['id'] ?? null;
$post = ['title' => '', 'content' => ''];
if ($id) {
    $post = get_post($id);
    if (!$post) die("Пост не знайдено");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    if ($id) {
        $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
        $stmt->execute([$title, $content, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");
        $stmt->execute([$title, $content]);
    }
    header("Location: index.php");
    exit;
}

$pageTitle = $id ? "Редагування" : "Новий пост";
require 'includes/header.php';
?>

<main class="container"> <form method="POST">
        <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" 
               placeholder="Заголовок..." required
               style="width: 100%; font-size: 32px; font-weight: 700; border: none; outline: none; margin-bottom: 20px; font-family: inherit;">
        
        <textarea name="content" placeholder="Текст..." required
                  style="width: 100%; min-height: 400px; border: none; outline: none; resize: vertical; font-family: inherit; font-size: 18px; line-height: 1.6;"><?= htmlspecialchars($post['content']) ?></textarea>

        <div style="margin-top: 20px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Зберегти</button>
            <a href="index.php" class="btn btn-outline">Скасувати</a>
        </div>
    </form>
</main>

<?php require 'includes/footer.php'; ?>