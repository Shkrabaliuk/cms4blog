<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!is_admin()) {
    header("Location: /index.php");
    exit;
}

$id = $_GET['id'] ?? null;
$post = null;

if ($id) {
    $post = get_post($id);
    if (!$post) {
        header("Location: /admin/admin.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $tags = trim($_POST['tags'] ?? '');
    
    if (empty($title)) {
        $error = "Заголовок не може бути порожнім";
    } else {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, tags = ? WHERE id = ?");
            $stmt->execute([$title, $content, $tags, $id]);
            header("Location: /admin/post-editor.php?id=$id&saved=1");
        } else {
            $stmt = $pdo->prepare("INSERT INTO posts (title, content, tags, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$title, $content, $tags]);
            $new_id = $pdo->lastInsertId();
            header("Location: /admin/post-editor.php?id=$new_id&saved=1");
        }
        exit;
    }
}

$pageTitle = $id ? "Редагування поста" : "Новий пост";
require '../includes/templates/header.php';
?>

<div class="admin-nav">
    <a href="/admin/admin.php?tab=posts"><i class="fa-solid fa-file-lines"></i> Пости</a>
    <a href="/admin/admin.php?tab=comments"><i class="fa-solid fa-comments"></i> Коментарі</a>
    <a href="/admin/admin.php?tab=settings"><i class="fa-solid fa-gear"></i> Налаштування</a>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin: 30px 0;">
    <h2><?= $id ? 'Редагування поста' : 'Новий пост' ?></h2>
    <?php if ($id): ?>
        <a href="/post.php?id=<?= $id ?>" target="_blank">Переглянути</a>
    <?php endif; ?>
</div>

<?php if (isset($_GET['saved'])): ?>
<div class="message success">Пост успішно збережено</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="message error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST">
    <div class="form-group">
        <label>Заголовок:</label>
        <input type="text" name="title" value="<?= htmlspecialchars($post['title'] ?? '') ?>" required autofocus>
    </div>
    
    <div class="form-group">
        <label>Вміст (markdown):</label>
        <textarea name="content" style="min-height: 400px; font-family: 'Courier New', monospace;" required><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
        <small style="color: #999; display: block; margin-top: 5px;">
            Підтримується Markdown: **жирний**, *курсив*, [посилання](url), ![зображення](url), `код`, ```блок коду```, > цитата<br>
            Галереї: [gallery]img1.jpg|Підпис 1, img2.jpg|Підпис 2[/gallery]
        </small>
    </div>
    
    <div class="form-group">
        <label>Теги (через кому):</label>
        <input type="text" name="tags" value="<?= htmlspecialchars($post['tags'] ?? '') ?>" placeholder="php, веб-розробка, cms">
    </div>
    
    <button type="submit"><i class="fa-solid fa-save"></i> <?= $id ? 'Зберегти зміни' : 'Опублікувати' ?></button>
    <a href="/admin/admin.php" style="margin-left: 10px;"><i class="fa-solid fa-times"></i> Скасувати</a>
</form>

<?php require '../includes/templates/footer.php'; ?>
