# 📊 Структура БД - Поточний стан vs Aegea-стиль

## 🔴 ПОТОЧНА СТРУКТУРА (Занадто складна)

### Таблиці:
```
users (id, username, password)
posts (id, title, slug, content, tags, created_at, view_count)
comments (id, post_id, author, email, ip_address, content, status, created_at)
settings (key, value, type)
media (id, filename, original_name, mime_type, size, type, uploaded_at)
```

### Проблеми:
- ❌ Занадто багато таблиць для простого блогу
- ❌ `media` таблиця - overkill для лого
- ❌ `settings` з типізацією - надмірна складність
- ❌ Немає концепції "перманентних посилань" як в Aegea

---

## ✅ AEGEA-СТИЛЬ СТРУКТУРА (Мінімалістична)

### Філософія:
> "Найкраща БД - та, якої майже немає" - Ілля Бірман

### Оптимізована структура:

```sql
-- 1. КОРИСТУВАЧІ (мінімум полів)
CREATE TABLE `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `login` varchar(50) NOT NULL,
    `password` varchar(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. ПОСТИ (ядро системи)
CREATE TABLE `posts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `slug` varchar(255) NOT NULL,
    `title` varchar(255) NOT NULL,
    `content` longtext NOT NULL,
    `tags` varchar(500) DEFAULT NULL COMMENT 'Через кому',
    `published_at` datetime NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    `is_published` tinyint(1) NOT NULL DEFAULT 1,
    `views` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `published` (`is_published`, `published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. КОМЕНТАРІ (прив'язані до постів)
CREATE TABLE `comments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `post_id` int(11) NOT NULL,
    `author` varchar(100) NOT NULL,
    `email` varchar(255) NOT NULL,
    `content` text NOT NULL,
    `ip` varchar(45) DEFAULT NULL,
    `posted_at` datetime NOT NULL,
    `is_approved` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `post_id` (`post_id`, `is_approved`),
    CONSTRAINT `comments_post` FOREIGN KEY (`post_id`) 
        REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. НАЛАШТУВАННЯ (key-value, БЕЗ типів)
CREATE TABLE `settings` (
    `key` varchar(100) NOT NULL,
    `value` text,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Налаштування за замовчуванням
INSERT INTO `settings` VALUES
    ('site_name', 'Мій блог'),
    ('site_subtitle', ''),
    ('author_name', 'Автор'),
    ('logo_path', ''),
    ('posts_per_page', '10'),
    ('comments_moderation', '1'),
    ('timezone', 'Europe/Kiev');
```

---

## 🔄 МІГРАЦІЯ: Від поточної до Aegea-стилю

### Зміни:

1. **ВИДАЛИТИ таблицю `media`** 
   - Логотип зберігається просто як шлях в `settings`
   - Зображення в постах - через markdown `![](path)`

2. **СПРОСТИТИ `settings`**
   - Видалити колонку `type`
   - Все текст, парсинг на рівні PHP

3. **ПЕРЕЙМЕНУВАТИ поля для консистентності**
   - `created_at` → `published_at`
   - `status` → `is_approved`
   - `view_count` → `views`

4. **ДОДАТИ `is_published`**
   - Чернетки vs опубліковані пости

### SQL для міграції:

```sql
-- 1. Бекап старих даних
CREATE TABLE posts_backup AS SELECT * FROM posts;
CREATE TABLE comments_backup AS SELECT * FROM comments;

-- 2. Видалення зайвого
DROP TABLE IF EXISTS media;

-- 3. Оновлення posts
ALTER TABLE posts 
    CHANGE `created_at` `published_at` datetime NOT NULL,
    CHANGE `view_count` `views` int(11) NOT NULL DEFAULT 0,
    ADD COLUMN `updated_at` datetime DEFAULT NULL AFTER `published_at`,
    ADD COLUMN `is_published` tinyint(1) NOT NULL DEFAULT 1 AFTER `views`;

-- 4. Оновлення comments
ALTER TABLE comments
    CHANGE `created_at` `posted_at` datetime NOT NULL,
    CHANGE `status` `is_approved` tinyint(1) NOT NULL DEFAULT 0,
    CHANGE `ip_address` `ip` varchar(45) DEFAULT NULL;

-- Конвертація статусів
UPDATE comments SET is_approved = 
    CASE 
        WHEN status = 'approved' THEN 1 
        ELSE 0 
    END;

-- 5. Спрощення settings
ALTER TABLE settings DROP COLUMN IF EXISTS `type`;

-- 6. Додавання foreign key
ALTER TABLE comments 
    ADD CONSTRAINT `comments_post` 
    FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) 
    ON DELETE CASCADE;

-- 7. Видалення users.username, залишаємо login
ALTER TABLE users 
    CHANGE `username` `login` varchar(50) NOT NULL;
```

---

## 📁 ФАЙЛОВА СТРУКТУРА

### Aegea-style (що потрібно):
```
/index.php              - Timeline (стрічка)
/post/[slug].php        - Окремий пост
/admin.php              - Проста адмінка (одна сторінка!)
/ajax.php               - AJAX endpoints
/config.php             - Налаштування
/functions.php          - Всі функції
/style.css              - Єдиний CSS файл
/uploads/               - Зображення
```

### Від чого відмовитись:
- ❌ Складна MVC структура `app/Controllers/Models/Views`
- ❌ Окремі сторінки для кожної адмін-функції
- ❌ Класи та OOP (Aegea використовує процедурний стиль!)

---

## 🎨 AEGEA FEATURES

### 1. Timeline на головній
```php
// index.php
$posts = $pdo->query("
    SELECT * FROM posts 
    WHERE is_published = 1 
    ORDER BY published_at DESC 
    LIMIT 20
")->fetchAll();

foreach ($posts as $post) {
    echo render_post_preview($post);
}
```

### 2. On-page editing (при натисканні "E")
```javascript
// Якщо адмін натискає "E" - показуємо форму
document.addEventListener('keydown', (e) => {
    if (e.key === 'e' && isAdmin) {
        showInlineEditor();
    }
});
```

### 3. Коментарі на сторінці поста
```php
// post/[slug].php
$comments = $pdo->prepare("
    SELECT * FROM comments 
    WHERE post_id = ? AND is_approved = 1 
    ORDER BY posted_at ASC
")->execute([$post_id])->fetchAll();
```

---

## 🚀 НАСТУПНІ КРОКИ

1. **Запустити міграцію БД** ⬅️ Ви тут
2. Спростити файлову структуру
3. Реалізувати Timeline
4. Додати inline editing
5. Інтегрувати Fotorama
6. Drag-n-drop лого

**Готові виконати міграцію БД?** 
Я створю SQL файл для безпечного переходу! ✅
