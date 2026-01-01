<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Вихід
if (isset($_GET['logout'])) {
    session_start();
    session_destroy();
    header("Location: /");
    exit;
}

if (!is_admin()) {
    header("Location: /admin/login.php");
    exit;
}

$message = '';

// Обробка дій
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Видалення поста
    if ($action === 'delete_post') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM comments WHERE post_id = ?");
        $stmt->execute([$id]);
        $message = 'Пост видалено';
    }
    
    // Модерація коментаря
    elseif ($action === 'approve_comment') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("UPDATE comments SET status = 'approved' WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Коментар схвалено';
    }
    elseif ($action === 'reject_comment') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("UPDATE comments SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Коментар відхилено';
    }
    elseif ($action === 'delete_comment') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Коментар видалено';
    }
    
    // Збереження налаштувань
    elseif ($action === 'save_settings') {
        // Завантаження логотипу
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'svg', 'webp'];
            
            if (in_array($ext, $allowed)) {
                $filename = 'logo_' . time() . '.' . $ext;
                $upload_dir = '../uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $filename)) {
                    $path = '/uploads/' . $filename;
                    $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES ('logo_path', ?) ON DUPLICATE KEY UPDATE `value` = ?");
                    $stmt->execute([$path, $path]);
                }
            }
        }
        
        // Інші налаштування
        foreach ($_POST as $key => $value) {
            if (in_array($key, ['action', 'csrf_token'])) continue;
            $stmt = $pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = ?");
            $stmt->execute([$key, $value, $value]);
        }
        
        $message = 'Налаштування збережено';
    }
}

$view = $_GET['view'] ?? 'posts';

$pageTitle = 'Адміністрування';
require '../includes/templates/header.php';
?>

<style>
.admin-tabs { display: flex; gap: 20px; margin: 30px 0; border-bottom: 2px solid #000; }
.admin-tabs a { padding: 10px 0; color: #666; font-weight: 600; border-bottom: 3px solid transparent; margin-bottom: -2px; }
.admin-tabs a:hover { color: #000; text-decoration: none; }
.admin-tabs a.active { color: #000; border-bottom-color: #000; }
</style>

<?php if ($message): ?>
<div class="message success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="admin-tabs">
    <a href="?view=posts" class="<?= $view === 'posts' ? 'active' : '' ?>">Пости</a>
    <a href="?view=comments" class="<?= $view === 'comments' ? 'active' : '' ?>">Коментарі</a>
    <a href="?view=settings" class="<?= $view === 'settings' ? 'active' : '' ?>">Налаштування</a>
</div>

<?php if ($view === 'posts'): ?>
    <h2>Всі пости</h2>
    <p><a href="/admin/post-editor.php" class="button">Додати новий пост</a></p>
    
    <?php
    $stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
    $posts = $stmt->fetchAll();
    ?>
    
    <?php if (count($posts) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Заголовок</th>
                <th>Дата</th>
                <th>Дії</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($posts as $post): ?>
            <tr>
                <td><a href="/post.php?id=<?= $post['id'] ?>" target="_blank"><?= htmlspecialchars($post['title']) ?></a></td>
                <td><?= date('d.m.Y', strtotime($post['created_at'])) ?></td>
                <td>
                    <a href="/admin/post-editor.php?id=<?= $post['id'] ?>">Редагувати</a>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Видалити?')">
                        <input type="hidden" name="action" value="delete_post">
                        <input type="hidden" name="id" value="<?= $post['id'] ?>">
                        <button type="submit" style="background:none;border:none;color:#c00;cursor:pointer;text-decoration:underline;">Видалити</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>Постів немає</p>
    <?php endif; ?>

<?php elseif ($view === 'comments'): ?>
    <h2>Модерація коментарів</h2>
    
    <?php
    $status = $_GET['status'] ?? 'pending';
    $stmt = $pdo->prepare("SELECT c.*, p.title as post_title FROM comments c LEFT JOIN posts p ON c.post_id = p.id WHERE c.status = ? ORDER BY c.created_at DESC");
    $stmt->execute([$status]);
    $comments = $stmt->fetchAll();
    ?>
    
    <div style="margin: 20px 0;">
        <a href="?view=comments&status=pending" style="<?= $status === 'pending' ? 'font-weight:bold;' : '' ?>">На модерації</a> · 
        <a href="?view=comments&status=approved" style="<?= $status === 'approved' ? 'font-weight:bold;' : '' ?>">Схвалені</a> · 
        <a href="?view=comments&status=rejected" style="<?= $status === 'rejected' ? 'font-weight:bold;' : '' ?>">Відхилені</a>
    </div>
    
    <?php if (count($comments) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Автор</th>
                <th>Коментар</th>
                <th>Пост</th>
                <th>Дата</th>
                <th>Дії</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($comments as $comment): ?>
            <tr>
                <td><?= htmlspecialchars($comment['author']) ?><br><small><?= htmlspecialchars($comment['email']) ?></small></td>
                <td><?= nl2br(htmlspecialchars(mb_substr($comment['content'], 0, 100))) ?></td>
                <td><a href="/post.php?id=<?= $comment['post_id'] ?>"><?= htmlspecialchars($comment['post_title']) ?></a></td>
                <td><?= time_ago($comment['created_at']) ?></td>
                <td>
                    <?php if ($status !== 'approved'): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="approve_comment">
                        <input type="hidden" name="id" value="<?= $comment['id'] ?>">
                        <button type="submit" style="background:none;border:none;color:green;cursor:pointer;text-decoration:underline;">Схвалити</button>
                    </form>
                    <?php endif; ?>
                    <?php if ($status !== 'rejected'): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="reject_comment">
                        <input type="hidden" name="id" value="<?= $comment['id'] ?>">
                        <button type="submit" style="background:none;border:none;color:orange;cursor:pointer;text-decoration:underline;">Відхилити</button>
                    </form>
                    <?php endif; ?>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Видалити?')">
                        <input type="hidden" name="action" value="delete_comment">
                        <input type="hidden" name="id" value="<?= $comment['id'] ?>">
                        <button type="submit" style="background:none;border:none;color:#c00;cursor:pointer;text-decoration:underline;">Видалити</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>Коментарів немає</p>
    <?php endif; ?>

<?php elseif ($view === 'settings'): ?>
    <h2>Налаштування</h2>
    
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save_settings">
        
        <div class="form-group">
            <label>Назва блогу:</label>
            <input type="text" name="blog_name" value="<?= htmlspecialchars(get_setting('blog_name', '/\\ogos')) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Логотип:</label>
            <?php $logo = get_setting('logo_path'); ?>
            <?php if ($logo): ?>
                <div style="margin: 10px 0;">
                    <img src="<?= htmlspecialchars($logo) ?>" style="max-width: 200px;">
                </div>
            <?php endif; ?>
            <input type="file" name="logo" accept="image/*">
        </div>
        
        <div class="form-group">
            <label>Постів на сторінку:</label>
            <input type="number" name="posts_per_page" value="<?= intval(get_setting('posts_per_page', 10)) ?>" min="1" max="50">
        </div>
        
        <button type="submit">Зберегти</button>
    </form>

<?php endif; ?>

<?php require '../includes/templates/footer.php'; ?>
