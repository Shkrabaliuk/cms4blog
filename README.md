# CMS4Blog

Легка, швидка та захищена CMS для блогінгу на PHP 8.x + MySQL.

## 📋 Статус розробки

🚧 **В процесі розробки**

### Етапи

- [x] **Етап 1** — Архітектурний каркас ✅
- [x] **Етап 2** — Інфраструктура (Database, .env, error handling, інсталятор) ✅
- [ ] **Етап 3** — Модуль Blog (в розробці)

## 🚀 Швидкий старт

### Системні вимоги

- PHP 8.0 або вище
- MySQL 5.7 або вище / MariaDB 10.2+
- Apache/Nginx web server
- PDO PHP Extension
- JSON PHP Extension
- mbstring PHP Extension

### Встановлення

1. **Клонуйте репозиторій або завантажте файли:**
   ```bash
   git clone https://github.com/yourusername/cms4blog.git
   cd cms4blog
   ```

2. **Налаштуйте права доступу:**
   ```bash
   chmod -R 755 storage/
   chmod 644 .env.example
   ```

3. **Налаштуйте веб-сервер:**
   - **Apache:** Переконайтеся, що `mod_rewrite` увімкнено
   - **Nginx:** Використовуйте конфігурацію з `docs/nginx.conf.example`
   - Document Root повинен вказувати на папку `/public`

4. **Відкрийте сайт у браузері:**
   ```
   http://localhost/install
   ```

5. **Слідуйте майстру встановлення:**
   - Крок 1: Привітання
   - Крок 2: Перевірка системних вимог
   - Крок 3: Налаштування бази даних
   - Крок 4: Завершення встановлення

6. **Після встановлення:**
   - База даних буде створена автоматично
   - Таблиці створені через міграції
   - Створено адміністратора за замовчуванням:
     - **Логін:** admin
     - **Email:** admin@cms4blog.local
     - **Пароль:** admin123
   - ⚠️ **Обов'язково змініть пароль після першого входу!**

## 📁 Структура проекту

```
cms4blog/
├── app/
│   ├── Controllers/          # Контролери
│   ├── Core/                 # Ядро системи
│   │   ├── Container.php     # DI Container
│   │   ├── Controller.php    # Базовий контролер
│   │   ├── Database.php      # Database клас
│   │   ├── Migration.php     # Міграції
│   │   ├── Router.php        # Роутер
│   │   ├── Security.php      # Безпека (CSRF)
│   │   └── View.php          # Шаблонізатор
│   └── Models/               # Моделі (буде додано в Етапі 3)
├── config/
│   └── routes.php            # Маршрути
├── database/
│   └── migrations/           # Міграції БД
├── public/
│   ├── .htaccess            # Apache конфігурація
│   └── index.php            # Точка входу
├── storage/
│   ├── cache/               # Кеш файли
│   └── logs/                # Логи
├── templates/
│   ├── errors/              # Шаблони помилок
│   ├── home/                # Шаблони головної
│   ├── install/             # Шаблони інсталятора
│   └── layouts/             # Layouts
├── .env                     # Конфігурація
├── .env.example             # Приклад конфігурації
├── .gitignore
└── README.md
```

## 🔧 Конфігурація

### Конфігурація бази даних (.env)

```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cms4blog
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Apache (.htaccess вже налаштований)

Файл `.htaccess` у папці `/public` вже містить необхідні налаштування:
- Rewrite rules для ЧПУ
- Security headers
- Захист sensitive файлів

### Nginx

Приклад конфігурації:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/cms4blog/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }
}
```

## 🛠️ Розробка

### Додавання маршрутів

Редагуйте `config/routes.php`:

```php
// Простий маршрут
$router->get('/page', [PageController::class, 'show']);

// Маршрут з параметром
$router->get('/post/{id}', [PostController::class, 'show']);

// POST маршрут
$router->post('/contact', [ContactController::class, 'send']);

// Група маршрутів з префіксом
$router->group('/admin', function (Router $router) {
    $router->get('/dashboard', [AdminController::class, 'dashboard']);
}, ['AuthMiddleware']);
```

### Створення контролера

```php
<?php

namespace App\Controllers;

use App\Core\Controller;

class MyController extends Controller
{
    public function index(): void
    {
        $this->view->setLayout('main');
        echo $this->render('my/index', [
            'title' => 'My Page',
            'data' => $someData
        ]);
    }
}
```

### Робота з базою даних

```php
use App\Core\Database;

// SELECT
$users = Database::fetchAll("SELECT * FROM users WHERE status = ?", ['active']);

// INSERT
$userId = Database::execute(
    "INSERT INTO users (username, email) VALUES (?, ?)",
    ['john', 'john@example.com']
);

// UPDATE
Database::execute(
    "UPDATE users SET status = ? WHERE id = ?",
    ['inactive', $userId]
);
```

### Створення міграцій

Створіть файл у `database/migrations/` з назвою `003_create_posts_table.php`:

```php
<?php

use App\Core\Database;

function up(): void
{
    $sql = "CREATE TABLE posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    Database::execute($sql);
}

function down(): void
{
    Database::execute("DROP TABLE IF EXISTS posts");
}
```

## 🔒 Безпека

### Захист від CSRF

```php
// У формі
<?= $security->csrfField() ?>

// Перевірка
if ($security->checkCsrf()) {
    // Process form
}
```

### XSS захист

Всі дані автоматично екрануються у View. Для виведення HTML:

```php
<?= $this->raw($htmlContent) ?>
```

## 📝 Особливості

- ✅ **MVC Architecture** - Чітке розділення логіки
- ✅ **DI Container** - Управління залежностями
- ✅ **Routing** - Гнучкий роутинг з middleware
- ✅ **Template Engine** - Безпечна система шаблонів
- ✅ **CSRF Protection** - Захист від CSRF атак
- ✅ **Database Layer** - PDO wrapper з міграціями
- ✅ **Error Handling** - Централізована обробка помилок
- ✅ **Secure Sessions** - Захищені сесії
- ✅ **Auto Installer** - Веб-інсталятор

## 📚 Документація

- [Архітектура](docs/architecture.md) (буде додано)
- [API Reference](docs/api.md) (буде додано)
- [Contributing Guide](CONTRIBUTING.md) (буде додано)

## 🤝 Внесок

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 Ліцензія

MIT License - see [LICENSE](LICENSE) file for details.

## 👨‍💻 Автор

Створено з ❤️ для спільноти PHP розробників.

## 🐛 Баг-репорти

Знайшли баг? [Створіть issue](https://github.com/yourusername/cms4blog/issues)

## ⭐ Підтримайте проект

Якщо вам сподобався проект - поставте зірку на GitHub!

---

**Version:** 0.2.0 (Stage 2 Complete)  
**Status:** Development  
**PHP:** 8.0+  
**License:** MIT
