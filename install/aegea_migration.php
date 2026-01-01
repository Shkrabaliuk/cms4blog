<?php
/**
 * AEGEA-STYLE MIGRATION
 * 
 * Перехід від складної структури до мінімалістичної (як в Aegea)
 * 
 * ВАЖЛИВО: Створює бекап перед змінами!
 * 
 * Запуск: php install/aegea_migration.php
 */

require_once __DIR__ . '/../includes/db.php';

echo "🚀 AEGEA-STYLE MIGRATION\n";
echo "========================\n\n";

try {
    // ========================================
    // КРОК 1: БЕКАП
    // ========================================
    echo "📦 Крок 1: Створення бекапу...\n";
    
    // Бекап posts
    $pdo->exec("DROP TABLE IF EXISTS posts_backup");
    $pdo->exec("CREATE TABLE posts_backup AS SELECT * FROM posts");
    $postsCount = $pdo->query("SELECT COUNT(*) FROM posts_backup")->fetchColumn();
    echo "   ✅ Збережено $postsCount постів\n";
    
    // Бекап comments
    $pdo->exec("DROP TABLE IF EXISTS comments_backup");
    $pdo->exec("CREATE TABLE comments_backup AS SELECT * FROM comments");
    $commentsCount = $pdo->query("SELECT COUNT(*) FROM comments_backup")->fetchColumn();
    echo "   ✅ Збережено $commentsCount коментарів\n";
    
    // Бекап settings
    $pdo->exec("DROP TABLE IF EXISTS settings_backup");
    $pdo->exec("CREATE TABLE settings_backup AS SELECT * FROM settings");
    $settingsCount = $pdo->query("SELECT COUNT(*) FROM settings_backup")->fetchColumn();
    echo "   ✅ Збережено $settingsCount налаштувань\n\n";
    
    // ========================================
    // КРОК 2: ВИДАЛЕННЯ ЗАЙВИХ ТАБЛИЦЬ
    // ========================================
    echo "🗑️  Крок 2: Видалення зайвих таблиць...\n";
    
    // Видаляємо media (логотип зберігаємо як path в settings)
    $mediaExists = $pdo->query("SHOW TABLES LIKE 'media'")->rowCount();
    if ($mediaExists) {
        // Зберігаємо логотип перед видаленням
        $logo = $pdo->query("SELECT filename FROM media WHERE type = 'logo' ORDER BY uploaded_at DESC LIMIT 1")->fetch();
        if ($logo) {
            $pdo->prepare("INSERT INTO settings (`key`, value) VALUES ('logo_path', ?) ON DUPLICATE KEY UPDATE value = ?")
                ->execute(['/uploads/' . $logo['filename'], '/uploads/' . $logo['filename']]);
            echo "   ✅ Логотип збережено: {$logo['filename']}\n";
        }
        
        $pdo->exec("DROP TABLE media");
        echo "   ✅ Таблицю media видалено\n";
    }
    
    echo "\n";
    
    // ========================================
    // КРОК 3: ОНОВЛЕННЯ POSTS
    // ========================================
    echo "📝 Крок 3: Оптимізація таблиці posts...\n";
    
    // Перевіряємо і додаємо slug якщо відсутній
    $columns = $pdo->query("SHOW COLUMNS FROM posts LIKE 'slug'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE posts ADD COLUMN `slug` varchar(255) NOT NULL AFTER `title`");
        
        // Генеруємо slug для існуючих постів
        $posts = $pdo->query("SELECT id, title FROM posts WHERE slug = '' OR slug IS NULL")->fetchAll();
        foreach ($posts as $post) {
            $slug = generate_slug($post['title']);
            $pdo->prepare("UPDATE posts SET slug = ? WHERE id = ?")->execute([$slug, $post['id']]);
        }
        
        $pdo->exec("ALTER TABLE posts ADD UNIQUE KEY `slug` (`slug`)");
        echo "   ✅ Додано колонку slug\n";
    }
    
    // Перейменування created_at → published_at
    $columns = $pdo->query("SHOW COLUMNS FROM posts LIKE 'published_at'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE posts CHANGE `created_at` `published_at` datetime NOT NULL");
        echo "   ✅ created_at → published_at\n";
    }
    
    // Перейменування view_count → views
    $columns = $pdo->query("SHOW COLUMNS FROM posts LIKE 'views'")->fetchAll();
    if (empty($columns)) {
        $columns = $pdo->query("SHOW COLUMNS FROM posts LIKE 'view_count'")->fetchAll();
        if (!empty($columns)) {
            $pdo->exec("ALTER TABLE posts CHANGE `view_count` `views` int(11) NOT NULL DEFAULT 0");
            echo "   ✅ view_count → views\n";
        }
    }
    
    // Додавання updated_at
    $columns = $pdo->query("SHOW COLUMNS FROM posts LIKE 'updated_at'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE posts ADD COLUMN `updated_at` datetime DEFAULT NULL AFTER `published_at`");
        echo "   ✅ Додано updated_at\n";
    }
    
    // Додавання is_published
    $columns = $pdo->query("SHOW COLUMNS FROM posts LIKE 'is_published'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE posts ADD COLUMN `is_published` tinyint(1) NOT NULL DEFAULT 1 AFTER `views`");
        $pdo->exec("ALTER TABLE posts ADD KEY `published` (`is_published`, `published_at`)");
        echo "   ✅ Додано is_published\n";
    }
    
    echo "\n";
    
    // ========================================
    // КРОК 4: ОНОВЛЕННЯ COMMENTS
    // ========================================
    echo "💬 Крок 4: Оптимізація таблиці comments...\n";
    
    // Перейменування created_at → posted_at
    $columns = $pdo->query("SHOW COLUMNS FROM comments LIKE 'posted_at'")->fetchAll();
    if (empty($columns)) {
        $pdo->exec("ALTER TABLE comments CHANGE `created_at` `posted_at` datetime NOT NULL");
        echo "   ✅ created_at → posted_at\n";
    }
    
    // Конвертація status → is_approved
    $columns = $pdo->query("SHOW COLUMNS FROM comments LIKE 'is_approved'")->fetchAll();
    if (empty($columns)) {
        // Додаємо нову колонку
        $pdo->exec("ALTER TABLE comments ADD COLUMN `is_approved` tinyint(1) NOT NULL DEFAULT 0 AFTER `content`");
        
        // Конвертуємо дані
        $pdo->exec("UPDATE comments SET is_approved = CASE WHEN status = 'approved' THEN 1 ELSE 0 END");
        
        // Видаляємо стару колонку
        $pdo->exec("ALTER TABLE comments DROP COLUMN `status`");
        
        echo "   ✅ status → is_approved (boolean)\n";
    }
    
    // Перейменування ip_address → ip
    $columns = $pdo->query("SHOW COLUMNS FROM comments LIKE 'ip'")->fetchAll();
    if (empty($columns)) {
        $columns = $pdo->query("SHOW COLUMNS FROM comments LIKE 'ip_address'")->fetchAll();
        if (!empty($columns)) {
            $pdo->exec("ALTER TABLE comments CHANGE `ip_address` `ip` varchar(45) DEFAULT NULL");
            echo "   ✅ ip_address → ip\n";
        }
    }
    
    // Додавання foreign key
    try {
        $pdo->exec("ALTER TABLE comments ADD CONSTRAINT `comments_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE");
        echo "   ✅ Додано foreign key\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') === false) {
            echo "   ⚠️  Foreign key: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
    
    // ========================================
    // КРОК 5: СПРОЩЕННЯ SETTINGS
    // ========================================
    echo "⚙️  Крок 5: Спрощення таблиці settings...\n";
    
    // Видаляємо колонку type
    $columns = $pdo->query("SHOW COLUMNS FROM settings LIKE 'type'")->fetchAll();
    if (!empty($columns)) {
        $pdo->exec("ALTER TABLE settings DROP COLUMN `type`");
        echo "   ✅ Видалено колонку type\n";
    }
    
    // Додаємо стандартні налаштування
    $defaults = [
        'site_name' => 'Мій блог',
        'site_subtitle' => '',
        'author_name' => 'Автор',
        'posts_per_page' => '10',
        'comments_moderation' => '1',
        'timezone' => 'Europe/Kiev'
    ];
    
    foreach ($defaults as $key => $value) {
        $exists = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE `key` = ?");
        $exists->execute([$key]);
        if ($exists->fetchColumn() == 0) {
            $pdo->prepare("INSERT INTO settings (`key`, value) VALUES (?, ?)")->execute([$key, $value]);
        }
    }
    
    echo "   ✅ Налаштування за замовчуванням додано\n\n";
    
    // ========================================
    // КРОК 6: ОНОВЛЕННЯ USERS
    // ========================================
    echo "👤 Крок 6: Оптимізація таблиці users...\n";
    
    // Перейменування username → login
    $columns = $pdo->query("SHOW COLUMNS FROM users LIKE 'login'")->fetchAll();
    if (empty($columns)) {
        $columns = $pdo->query("SHOW COLUMNS FROM users LIKE 'username'")->fetchAll();
        if (!empty($columns)) {
            $pdo->exec("ALTER TABLE users CHANGE `username` `login` varchar(50) NOT NULL");
            echo "   ✅ username → login\n";
        } else {
            // Додаємо login якщо відсутній
            $pdo->exec("ALTER TABLE users ADD COLUMN `login` varchar(50) NOT NULL DEFAULT 'admin' AFTER `id`");
            $pdo->exec("ALTER TABLE users ADD UNIQUE KEY `login` (`login`)");
            echo "   ✅ Додано колонку login\n";
        }
    }
    
    echo "\n";
    
    // ========================================
    // ПІДСУМОК
    // ========================================
    echo "✅ МІГРАЦІЯ ЗАВЕРШЕНА УСПІШНО!\n";
    echo "================================\n\n";
    
    echo "📊 Статистика:\n";
    echo "   Постів: " . $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn() . "\n";
    echo "   Коментарів: " . $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn() . "\n";
    echo "   Налаштувань: " . $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn() . "\n";
    echo "   Користувачів: " . $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() . "\n\n";
    
    echo "📁 Бекап створено:\n";
    echo "   - posts_backup\n";
    echo "   - comments_backup\n";
    echo "   - settings_backup\n\n";
    
    echo "🎉 База даних тепер у AEGEA-стилі!\n";
    echo "   - Мінімалістична структура\n";
    echo "   - Оптимізовані назви полів\n";
    echo "   - Foreign keys для цілісності\n\n";
    
    echo "🚀 Наступний крок: Спрощення файлової структури\n";
    
} catch (PDOException $e) {
    echo "\n❌ ПОМИЛКА: " . $e->getMessage() . "\n";
    echo "\n🔄 Відновлення з бекапу:\n";
    echo "   DROP TABLE posts; CREATE TABLE posts AS SELECT * FROM posts_backup;\n";
    echo "   DROP TABLE comments; CREATE TABLE comments AS SELECT * FROM comments_backup;\n";
    echo "   DROP TABLE settings; CREATE TABLE settings AS SELECT * FROM settings_backup;\n";
    die();
}

/**
 * Генерація slug з тексту
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
