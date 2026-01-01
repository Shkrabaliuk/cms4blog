<?php
/**
 * Міграція: Додавання таблиці медіа-файлів та оновлення структури БД
 * Версія: 1.1
 * Дата: 2026-01-01
 */

require_once __DIR__ . '/../includes/db.php';

try {
    // 1. Створення таблиці media для зображень, логотипів тощо
    $pdo->exec("CREATE TABLE IF NOT EXISTS `media` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `filename` varchar(255) NOT NULL,
        `original_name` varchar(255) NOT NULL,
        `mime_type` varchar(100) NOT NULL,
        `size` int(11) NOT NULL,
        `type` enum('logo','avatar','post_image','gallery') NOT NULL DEFAULT 'post_image',
        `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `type` (`type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. Оновлення таблиці settings - додавання типу даних
    $columns = $pdo->query("SHOW COLUMNS FROM settings LIKE 'type'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE `settings` 
            ADD COLUMN `type` enum('text','number','boolean','file','json') NOT NULL DEFAULT 'text' AFTER `value`");
    }

    // 3. Додавання email колонки для коментарів (якщо відсутня)
    $columns = $pdo->query("SHOW COLUMNS FROM comments LIKE 'email'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE `comments` 
            ADD COLUMN `email` varchar(255) NOT NULL AFTER `author`,
            ADD KEY `email` (`email`)");
    }

    // 4. Додавання IP адреси для rate limiting коментарів
    $columns = $pdo->query("SHOW COLUMNS FROM comments LIKE 'ip_address'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE `comments` 
            ADD COLUMN `ip_address` varchar(45) AFTER `email`");
    }

    // 5. Додавання slug для постів (для ЧПУ)
    $columns = $pdo->query("SHOW COLUMNS FROM posts LIKE 'slug'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE `posts` 
            ADD COLUMN `slug` varchar(255) AFTER `title`,
            ADD UNIQUE KEY `slug` (`slug`)");
        
        // Генерація slug для існуючих постів
        $posts = $pdo->query("SELECT id, title FROM posts WHERE slug IS NULL OR slug = ''")->fetchAll();
        foreach ($posts as $post) {
            $slug = generate_slug($post['title']);
            $pdo->prepare("UPDATE posts SET slug = ? WHERE id = ?")->execute([$slug, $post['id']]);
        }
    }

    // 6. Додавання username для users
    $columns = $pdo->query("SHOW COLUMNS FROM users LIKE 'username'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE `users` 
            ADD COLUMN `username` varchar(100) NOT NULL DEFAULT 'admin' AFTER `id`,
            ADD UNIQUE KEY `username` (`username`)");
    }

    echo "✅ Міграція виконана успішно!\n";
    echo "Створено/оновлено:\n";
    echo "- Таблиця media\n";
    echo "- Колонка settings.type\n";
    echo "- Колонка comments.email\n";
    echo "- Колонка comments.ip_address\n";
    echo "- Колонка posts.slug\n";
    echo "- Колонка users.username\n";
    
} catch (PDOException $e) {
    echo "❌ Помилка міграції: " . $e->getMessage() . "\n";
    die();
}

/**
 * Генерація slug з українського тексту
 */
function generate_slug($text) {
    $transliteration = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'h', 'ґ' => 'g', 'д' => 'd',
        'е' => 'e', 'є' => 'ie', 'ж' => 'zh', 'з' => 'z', 'и' => 'y', 'і' => 'i',
        'ї' => 'i', 'й' => 'i', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'kh', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch',
        'ь' => '', 'ю' => 'iu', 'я' => 'ia'
    ];
    
    $text = mb_strtolower($text, 'UTF-8');
    $text = strtr($text, $transliteration);
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    $text = trim($text, '-');
    
    return $text ?: 'post-' . time();
}
