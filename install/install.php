<?php
session_start();

if (file_exists('../config.php')) {
    header("Location: ../index.php");
    exit;
}

// Перевірка версії PHP
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die('Помилка: Потрібна версія PHP 7.4 або вище. Поточна версія: ' . PHP_VERSION);
}

// Перевірка необхідних розширень
$required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'fileinfo'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    die('Помилка: Відсутні необхідні PHP розширення: ' . implode(', ', $missing_extensions));
}

$error = '';
$success = '';
$databases = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['host'] ?? 'localhost';
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    $dbname = $_POST['dbname'] ?? '';
    $blog_password = $_POST['blog_password'] ?? '';
    $drop_existing = isset($_POST['drop_existing']);
    $install_demo = isset($_POST['install_demo']);

    if (empty($user) || empty($dbname) || empty($blog_password)) {
        $error = 'Заповніть всі поля';
    } else {
        try {
            $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // Перевірка версії MySQL
            $version = $pdo->query('SELECT VERSION()')->fetchColumn();
            if (version_compare($version, '5.7.0', '<')) {
                throw new Exception("Потрібна версія MySQL 5.7+ або MariaDB 10.2+. Поточна версія: $version");
            }

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$dbname`");

            // Видалення існуючих таблиць якщо вибрано
            if ($drop_existing) {
                $pdo->exec("DROP TABLE IF EXISTS `comments`");
                $pdo->exec("DROP TABLE IF EXISTS `posts`");
                $pdo->exec("DROP TABLE IF EXISTS `settings`");
                $pdo->exec("DROP TABLE IF EXISTS `users`");
            }

            // Таблиця користувачів
            $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `password` varchar(255) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Таблиця постів з тегами
            $pdo->exec("CREATE TABLE IF NOT EXISTS `posts` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `title` varchar(255) NOT NULL,
                `content` text NOT NULL,
                `tags` text,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `view_count` int(11) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                KEY `view_count` (`view_count`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Таблиця налаштувань
            $pdo->exec("CREATE TABLE IF NOT EXISTS `settings` (
                `key` varchar(100) NOT NULL,
                `value` text,
                PRIMARY KEY (`key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Таблиця коментарів
            $pdo->exec("CREATE TABLE IF NOT EXISTS `comments` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `post_id` int(11) NOT NULL,
                `author` varchar(100) NOT NULL,
                `content` text NOT NULL,
                `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `post_id` (`post_id`),
                KEY `status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            // Міграція: додавання колонки status якщо вона відсутня
            try {
                $pdo->exec("ALTER TABLE `comments` ADD COLUMN `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'approved' AFTER `content`");
            } catch (PDOException $e) {
                // Колонка вже існує
            }

            // Створення користувача
            $hash = password_hash($blog_password, PASSWORD_DEFAULT);
            
            // Перевіряємо чи існує користувач
            $userExists = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            
            if ($userExists > 0) {
                // Оновлюємо пароль існуючого користувача
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = 1");
                $stmt->execute([$hash]);
            } else {
                // Створюємо нового користувача
                $stmt = $pdo->prepare("INSERT INTO users (password) VALUES (?)");
                $stmt->execute([$hash]);
            }

            // Дефолтні налаштування
            $defaults = [
                'blog_name' => '/\\ogos',
                'blog_subtitle' => 'Мінімалістичний блог-движок',
                'author_name' => 'Yaroslav',
                'blog_description' => 'Блог у стилі Aegea від Іллі Бірмана',
                'posts_per_page' => '10',
                'show_view_counts' => '1',
                'footer_text' => '© 2026 /\\ogos',
                'footer_engine' => 'Движок: Aegea-inspired',
                'avatar' => '',
                'logo_path' => 'assets/images/logo.svg',
                'show_logo' => '1'
            ];
            
            foreach ($defaults as $key => $value) {
                $stmt = $pdo->prepare("REPLACE INTO settings (`key`, value) VALUES (?, ?)");
                $stmt->execute([$key, $value]);
            }

            // Тестовий контент
            if ($install_demo) {
                $demo_posts = [
                    [
                        'title' => 'Перший пост: Як почати вести блог',
                        'content' => "# Вітаю у моєму блозі!\n\nЦе **перший пост** у моєму новому блозі. Тут я буду ділитися:\n\n- Цікавими думками\n- Корисними порадами\n- Особистим досвідом\n\n## Чому блог?\n\nБлог - це чудовий спосіб *зберігати думки* та ділитися ними з іншими. До зустрічі!",
                        'tags' => 'блог, початок, першийпост',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-10 days'))
                    ],
                    [
                        'title' => '10 порад для продуктивності',
                        'content' => "## Як стати продуктивнішим\n\nПродуктивність - це **не про кількість**, а про *якість*. Ось мої основні принципи:\n\n1. Плануйте день з вечора\n2. Виконуйте найважче зранку\n3. Робіть перерви кожні 50 хвилин\n4. Відключайте сповіщення\n5. Використовуйте техніку Pomodoro\n\n### Висновок\n\nПродуктивність - це навичка, яку можна розвинути!",
                        'tags' => 'продуктивність, поради, розвиток',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-9 days'))
                    ],
                    [
                        'title' => 'Маркдаун: простий спосіб форматування',
                        'content' => "# Markdown - це просто!\n\nМаркдаун дозволяє **легко** форматувати текст:\n\n## Основні елементи:\n\n- *Курсив*\n- **Жирний**\n- [Посилання](https://example.com)\n- Списки\n\n### Чому варто вивчити?\n\nМаркдаун використовується *скрізь*: в GitHub, блогах, документації.",
                        'tags' => 'markdown, форматування, навчання',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-8 days'))
                    ],
                    [
                        'title' => 'Мій ранковий ритуал',
                        'content' => "## Як я починаю день\n\nРанок - це **найважливіший** час дня. Мій ритуал:\n\n1. Прокидаюся о 6:00\n2. Склянка води з лимоном\n3. 15 хвилин медитації\n4. Легка зарядка\n5. Корисний сніданок\n\n### Результат\n\nЗавдяки цьому ритуалу я почуваюся *енергійно* цілий день!",
                        'tags' => 'ранок, ритуал, здоров’я',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-7 days'))
                    ],
                    [
                        'title' => 'Топ-5 книг цього року',
                        'content' => "# Мої улюблені книги\n\nЧитання - це **інвестиція** в себе. Ось мої фаворити:\n\n1. \"Атомні звички\" - Джеймс Клір\n2. \"Глибока робота\" - Кел Ньюпорт\n3. \"Тонке мистецтво\" - Марк Менсон\n4. \"Sapiens\" - Юваль Ной Гарарі\n5. \"Потік\" - Міхай Чіксентмігайі\n\n## Чому саме ці?\n\nКожна з цих книг *змінила* мій погляд на життя.",
                        'tags' => 'книги, читання, розвиток',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-6 days'))
                    ],
                    [
                        'title' => 'Мінімалізм: менше - це більше',
                        'content' => "## Чому я обрав мінімалізм\n\nМінімалізм - це **не про відмову**, а про *свободу*.\n\n### Принципи:\n\n- Залишайте тільки те, що дає радість\n- Позбудьтеся зайвого\n- Фокус на якості, а не кількості\n- Цінуйте досвід, а не речі\n\nМінімалізм допомагає **знайти важливе**.",
                        'tags' => 'мінімалізм, стильжиття, філософія',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
                    ],
                    [
                        'title' => 'Технології, які я використовую',
                        'content' => "# Мій тех-стек\n\nОсь технології, які допомагають мені **щодня**:\n\n## Робота\n- VS Code - редактор коду\n- Git - контроль версій\n- Docker - контейнеризація\n\n## Комунікація\n- Slack - месенджер\n- Notion - нотатки\n\nПравильні *інструменти* роблять роботу приємнішою!",
                        'tags' => 'технології, інструменти, розробка',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-4 days'))
                    ],
                    [
                        'title' => 'Спорт у моєму житті',
                        'content' => "## Чому спорт важливий\n\nФізична активність - це **інвестиція** в здоров'я.\n\n### Моє регулярне тренування:\n\n- Біг 3 рази на тиждень\n- Силові вправи 2 рази\n- Йога щоранку\n- Розтяжка ввечері\n\n### Результати\n\nЗа 3 місяці я *покращив* свою витривалість на 40%!",
                        'tags' => 'спорт, здоров’я, тренування',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-3 days'))
                    ],
                    [
                        'title' => 'Подорожі та враження',
                        'content' => "# Мої улюблені місця\n\nПодорожі - це **найкраща освіта**. Ось мої фаворити:\n\n## Топ-3 міст\n\n1. **Лісабон** - місто трамваїв\n2. **Київ** - домівка\n3. **Барселона** - місто Гауді\n\n### Поради подорожуючим\n\n- Плануйте *гнучко*\n- Залишайте місце для спонтанності\n- Спілкуйтеся з місцевими",
                        'tags' => 'подорожі, враження, світ',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
                    ],
                    [
                        'title' => 'Фінансова грамотність: основи',
                        'content' => "## Чому важливо розуміти фінанси\n\nФінансова грамотність - це **ключ** до фінансової свободи.\n\n### Основні принципи:\n\n1. Витрачайте менше, ніж заробляєте\n2. Відкладайте 10% доходу\n3. Інвестуйте в себе\n4. Диверсифікуйте активи\n5. Уникайте *непотрібних боргів*\n\nФінансова грамотність - це навичка!",
                        'tags' => 'фінанси, гроші, інвестування',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
                    ],
                    [
                        'title' => 'Як я навчився кодити',
                        'content' => "# Мій шлях в програмування\n\nКодування - це **сучасна грамотність**. Як я починав:\n\n## Етапи навчання:\n\n1. **HTML/CSS** - основи вебу\n2. **JavaScript** - перша мова програмування\n3. **PHP** - серверна частина\n4. **Git** - контроль версій\n\n### Поради початківцям\n\n- Практикуйте *щодня*\n- Створюйте реальні проєкти\n- Не бійтеся помилок\n\nПрограмування - це **навичка**, яку може освоїти кожен!",
                        'tags' => 'програмування, навчання, код',
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                ];

                $post_id = 1;
                foreach ($demo_posts as $demo) {
                    $stmt = $pdo->prepare("INSERT INTO posts (title, content, tags, created_at) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$demo['title'], $demo['content'], $demo['tags'], $demo['created_at']]);
                    $post_id++;
                }

                // Тестові коментарі
                $demo_comments = [
                    // Коментарі до першого поста
                    ['post_id' => 1, 'author' => 'Марія', 'content' => 'Чудовий перший пост! Чекаю на продовження 😊', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-9 days 10:30'))],
                    ['post_id' => 1, 'author' => 'Олександр', 'content' => 'Дякую за мотивацію почати свій блог!', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-9 days 14:20'))],
                    
                    // Коментарі до другого поста
                    ['post_id' => 2, 'author' => 'Ірина', 'content' => 'Техніка Pomodoro реально працює! Використовую вже рік', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-8 days 09:15'))],
                    ['post_id' => 2, 'author' => 'Дмитро', 'content' => 'А я ще додаю правило "не більше 3 завдань на день"', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-8 days 16:45'))],
                    ['post_id' => 2, 'author' => 'Анна', 'content' => 'Збережу цей список собі 📋', 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s', strtotime('-8 days 19:30'))],
                    
                    // Коментарі до третього поста
                    ['post_id' => 3, 'author' => 'Сергій', 'content' => 'Markdown дійсно зручний! Використовую в Notion', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-7 days 11:00'))],
                    ['post_id' => 3, 'author' => 'Юлія', 'content' => 'А де можна практикувати markdown онлайн?', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-7 days 13:20'))],
                    
                    // Коментарі до четвертого поста
                    ['post_id' => 4, 'author' => 'Віктор', 'content' => 'О 6 ранку - це круто! Я тільки мрію так рано вставати', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-6 days 08:40'))],
                    ['post_id' => 4, 'author' => 'Катерина', 'content' => 'Медитація змінила моє життя! 🧘‍♀️', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-6 days 17:10'))],
                    ['post_id' => 4, 'author' => 'Максим', 'content' => 'Спробую завтра почати з цього ритуалу!', 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s', strtotime('-6 days 21:00'))],
                    
                    // Коментарі до п\'ятого поста
                    ['post_id' => 5, 'author' => 'Олена', 'content' => 'Атомні звички - моя улюблена книга! Перечитую щороку', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-5 days 10:25'))],
                    ['post_id' => 5, 'author' => 'Андрій', 'content' => 'Sapiens просто вражає! Рекомендую всім', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-5 days 15:50'))],
                    ['post_id' => 5, 'author' => 'Наталія', 'content' => 'Додам до списку "Тонке мистецтво" 📚', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-5 days 18:30'))],
                    
                    // Коментарі до шостого поста
                    ['post_id' => 6, 'author' => 'Павло', 'content' => 'Мінімалізм - це свобода від зайвого. Повністю погоджуюсь!', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-4 days 09:00'))],
                    ['post_id' => 6, 'author' => 'Валентина', 'content' => 'Після прибирання почуваєшся легше ✨', 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s', strtotime('-4 days 20:15'))],
                    
                    // Коментарі до сьомого поста
                    ['post_id' => 7, 'author' => 'Ігор', 'content' => 'VS Code + Git = must have для розробника', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-3 days 11:40'))],
                    ['post_id' => 7, 'author' => 'Тетяна', 'content' => 'А я ще використовую Figma для дизайну', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-3 days 14:55'))],
                    ['post_id' => 7, 'author' => 'Богдан', 'content' => 'Docker справді спрощує деплой!', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-3 days 19:20'))],
                    
                    // Коментарі до восьмого поста
                    ['post_id' => 8, 'author' => 'Світлана', 'content' => 'Біг - це медитація в русі 🏃‍♀️', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 days 07:30'))],
                    ['post_id' => 8, 'author' => 'Роман', 'content' => 'Йога допомогла мені з болями в спині', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 days 16:00'))],
                    ['post_id' => 8, 'author' => 'Людмила', 'content' => 'Мотивує почати тренуватись!', 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 days 21:45'))],
                    
                    // Коментарі до дев\'ятого поста
                    ['post_id' => 9, 'author' => 'Артем', 'content' => 'Лісабон - моє улюблене місто! 🇵🇹', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 day 10:10'))],
                    ['post_id' => 9, 'author' => 'Вікторія', 'content' => 'Барселона неймовірна! Хочу повернутись', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 day 15:35'))],
                    
                    // Коментарі до десятого поста
                    ['post_id' => 10, 'author' => 'Євген', 'content' => 'Фінансова грамотність - це must have!', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-12 hours'))],
                    ['post_id' => 10, 'author' => 'Олеся', 'content' => 'Правило 10% працює! Вже рік відкладаю', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-8 hours'))],
                    ['post_id' => 10, 'author' => 'Микола', 'content' => 'Корисна інформація, дякую! 💰', 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s', strtotime('-4 hours'))],
                    
                    // Коментарі до одинадцятого поста
                    ['post_id' => 11, 'author' => 'Денис', 'content' => 'PHP - відмінна мова для початківців!', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours'))],
                    ['post_id' => 11, 'author' => 'Аліна', 'content' => 'Я теж вчу JavaScript зараз 💻', 'status' => 'approved', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))],
                    ['post_id' => 11, 'author' => 'Ярослав', 'content' => 'Практика - найважливіше в програмуванні!', 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s', strtotime('-30 minutes'))]
                ];

                foreach ($demo_comments as $comment) {
                    $stmt = $pdo->prepare("INSERT INTO comments (post_id, author, content, status, created_at) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$comment['post_id'], $comment['author'], $comment['content'], $comment['status'], $comment['created_at']]);
                }
            }

            // Створення config.php
            $config = "<?php\ndefine('DB_HOST', '$host');\ndefine('DB_NAME', '$dbname');\ndefine('DB_USER', '$user');\ndefine('DB_PASS', '$pass');\n";
            file_put_contents('../config.php', $config);

            $success = 'Встановлення завершено! Перенаправлення...';
            header("refresh:2;url=../index.php");
        } catch (Exception $e) {
            $error = 'Помилка: ' . $e->getMessage();
        }
    }
}

// Отримання списку БД для dropdown
if (isset($_POST['get_databases'])) {
    $host = $_POST['host'] ?? 'localhost';
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';
    
    try {
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
        $stmt = $pdo->query("SHOW DATABASES");
        $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode(['success' => true, 'databases' => $databases]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Встановлення блогу</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<div class="install-container">
    <div class="install-icon">
        <svg width="80" height="80" viewBox="0 0 80 80" fill="none">
            <circle cx="40" cy="40" r="40" fill="#F4B942"/>
            <path d="M40 20 L45 35 L60 35 L48 45 L53 60 L40 50 L27 60 L32 45 L20 35 L35 35 Z" fill="white"/>
        </svg>
    </div>

    <h1>Встановлення</h1>

    <?php if ($error): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success-message"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" id="installForm">
        <div class="install-section">
            <h2>Database parameters that your hosting provider has given you:</h2>
            
            <div class="form-group">
                <label>Server</label>
                <input type="text" name="host" value="localhost" readonly>
                <div class="form-hint">Зазвичай це localhost, не змінюйте</div>
            </div>

            <div class="form-group">
                <label>User name and password</label>
                <input type="text" name="user" placeholder="root" required>
                <input type="password" name="pass" placeholder="Пароль (може бути порожнім)" style="margin-top: 8px;">
                <div class="form-hint">Отримайте ці дані у вашого хостинг-провайдера</div>
            </div>

            <div class="form-group">
                <label>Database name</label>
                <div class="db-selector">
                    <input type="text" name="dbname" id="dbnameInput" placeholder="Натисніть щоб вибрати..." onclick="loadDatabases()" required>
                    <div class="db-dropdown" id="dbDropdown"></div>
                </div>
                <div class="form-hint">Виберіть існуючу БД або введіть нову назву (створить автоматично)</div>
            </div>
        </div>

        <div class="install-section">
            <h2>Password you'd like to use to access your blog:</h2>
            
            <div class="form-group">
                <input type="password" name="blog_password" placeholder="Придумайте надійний пароль" required minlength="6">
                <div class="form-hint">Мінімум 6 символів. Запам'ятайте його!</div>
            </div>

            <div class="form-group">
                <label class="e2-switch" style="margin-top: 16px;">
                    <input type="checkbox" name="drop_existing" class="checkbox">
                    <i></i> Видалити існуючі дані (якщо база не порожня)
                </label>
                <div class="form-hint" style="color: #d32f2f; margin-top: 8px;">⚠️ Увага! Це видалить всі пости, коментарі та налаштування з бази даних</div>
            </div>

        </div>

        <button type="submit" class="install-button" id="submitBtn">
            <span>Start blogging</span>
            <span style="font-size: 12px; opacity: 0.7;">Ctrl + Enter</span>
        </button>
    </form>
</div>

<script>
let databases = [];

async function loadDatabases() {
    const host = document.querySelector('input[name="host"]').value;
    const user = document.querySelector('input[name="user"]').value;
    const pass = document.querySelector('input[name="pass"]').value;
    
    if (!user) {
        alert('Спочатку введіть User name');
        return;
    }
    
    const formData = new FormData();
    formData.append('get_databases', '1');
    formData.append('host', host);
    formData.append('user', user);
    formData.append('pass', pass);
    
    try {
        const response = await fetch('', { method: 'POST', body: formData });
        const data = await response.json();
        
        if (data.success) {
            databases = data.databases;
            showDropdown();
        } else {
            alert('Помилка підключення: ' + data.error);
        }
    } catch (e) {
        alert('Помилка: ' + e.message);
    }
}

function showDropdown() {
    const dropdown = document.getElementById('dbDropdown');
    dropdown.innerHTML = '';
    
    if (databases.length === 0) {
        dropdown.innerHTML = '<div class="db-option" style="color: #999;">Баз даних не знайдено</div>';
    } else {
        databases.forEach(db => {
            if (!['information_schema', 'mysql', 'performance_schema', 'sys'].includes(db)) {
                const option = document.createElement('div');
                option.className = 'db-option';
                option.textContent = db;
                option.onclick = () => selectDatabase(db);
                dropdown.appendChild(option);
            }
        });
    }
    
    dropdown.classList.add('active');
}

function selectDatabase(dbname) {
    document.getElementById('dbnameInput').value = dbname;
    document.getElementById('dbDropdown').classList.remove('active');
}

document.addEventListener('click', function(e) {
    const selector = document.querySelector('.db-selector');
    if (!selector.contains(e.target)) {
        document.getElementById('dbDropdown').classList.remove('active');
    }
});

document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        document.getElementById('installForm').submit();
    }
});
</script>

</body>
</html>
