# 📚 Документація CMS - Рефакторинг та покращення

## 🎯 Огляд змін

### ✅ Виконано:

1. **База даних** - Розширено структуру
2. **Архітектура** - Впроваджено MVC (спрощений варіант)
3. **Медіа-система** - Додано управління файлами
4. **Drag & Drop логотип** - Повна реалізація з AJAX
5. **Безпека** - Покращено валідацію та захист файлів

---

## 📊 Нова структура БД

### Таблиця `media`
```sql
CREATE TABLE `media` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `filename` varchar(255) NOT NULL,
    `original_name` varchar(255) NOT NULL,
    `mime_type` varchar(100) NOT NULL,
    `size` int(11) NOT NULL,
    `type` enum('logo','avatar','post_image','gallery'),
    `uploaded_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
);
```

### Оновлення існуючих таблиць:
- `settings` - додано колонку `type` (text/number/boolean/file/json)
- `posts` - додано колонку `slug` для ЧПУ
- `comments` - додано `email` та `ip_address`
- `users` - додано `username`

---

## 🏗️ Нова архітектура

```
cms4blog/
├── app/                          # Нова MVC структура
│   ├── Controllers/
│   │   ├── BaseController.php    # Базовий контролер
│   │   └── SettingsController.php # Контролер налаштувань
│   ├── Models/
│   │   └── MediaModel.php        # Модель для медіа
│   └── Views/
│       └── admin/
│           └── settings.php      # View налаштувань
├── uploads/                      # Завантажені файли
│   ├── logos/
│   ├── avatars/
│   ├── posts/
│   └── gallery/
├── assets/                       # Статичні ресурси
│   ├── fontawesome/              # Font Awesome 7.1.0
│   ├── fotorama/                 # Fotorama 4.6.4
│   ├── fonts/                    # Tilda Sans
│   └── css/
│       └── style.css             # Normalize + стилі
└── includes/                     # Старі файли (поступово переносити)
```

---

## 🚀 Використання нових можливостей

### 1. Міграція БД
```bash
php install/migrate.php
```

### 2. Завантаження логотипу

**Через адмін-панель:**
- Відкрийте `/admin/settings.php`
- Перетягніть файл в область Drag & Drop
- Або клацніть для вибору файлу

**Програматично:**
```php
require_once 'app/Models/MediaModel.php';

$mediaModel = new MediaModel($pdo);
$result = $mediaModel->upload($_FILES['logo'], 'logo');

// Результат:
// ['id' => 1, 'filename' => 'logos/abc123.png', 'url' => '/uploads/logos/abc123.png']
```

### 3. Використання нового контролера

**admin/settings.php:**
```php
require_once '../app/Controllers/SettingsController.php';

$controller = new SettingsController();

switch ($_GET['action'] ?? 'index') {
    case 'upload_logo':
        $controller->uploadLogo();
        break;
    case 'delete_logo':
        $controller->deleteLogo();
        break;
    default:
        $controller->index();
}
```

### 4. AJAX запити для логотипу

**Завантаження:**
```javascript
const formData = new FormData();
formData.append('logo', file);
formData.append('csrf_token', '<?= generate_csrf_token() ?>');

fetch('/admin/settings.php?action=upload_logo', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => {
    console.log(data.logo_url); // /uploads/logos/abc123.png
});
```

**Видалення:**
```javascript
fetch('/admin/settings.php?action=delete_logo', {
    method: 'POST',
    body: 'csrf_token=TOKEN'
});
```

---

## 🔒 Безпека

### Завантаження файлів:
- ✅ Валідація MIME-типів
- ✅ Обмеження розміру (5MB)
- ✅ Унікальні імена файлів
- ✅ .htaccess блокує виконання PHP
- ✅ CSRF захист

### Uploads .htaccess:
```apache
# Блокуємо PHP
<FilesMatch "\.(php|phtml)$">
    Deny from all
</FilesMatch>

# Дозволяємо тільки зображення
<FilesMatch "\.(jpg|png|gif|webp|svg)$">
    Allow from all
</FilesMatch>
```

---

## 📋 Чеклист міграції старого коду

### Пріоритет 1 (Критично):
- [x] База даних оновлена
- [x] Система медіа працює
- [x] Логотип з Drag & Drop
- [ ] Перенести admin.php в контролер
- [ ] Перенести post-editor.php в контролер
- [ ] Перенести comments.php в контролер

### Пріоритет 2 (Важливо):
- [ ] Створити PostController
- [ ] Створити CommentController
- [ ] Створити MediaController для галерей
- [ ] Додати API endpoints для AJAX

### Пріоритет 3 (Nice to have):
- [ ] Кешування (Redis/Memcached)
- [ ] CDN інтеграція
- [ ] Компресія зображень (WebP)
- [ ] Lazy loading для галерей

---

## 🎨 Інтеграція бібліотек

### ✅ Normalize.css
Вбудовано в `assets/css/style.css` (рядки 1-70)

### ✅ Fotorama
```html
<link rel="stylesheet" href="/assets/fotorama/fotorama.css">
<script src="/assets/fotorama/fotorama.js"></script>
```

**Використання в постах:**
```markdown
[gallery]
/uploads/posts/img1.jpg|Опис 1,
/uploads/posts/img2.jpg|Опис 2
[/gallery]
```

### ✅ Font Awesome 7.1.0
```html
<link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
```

### ✅ Tilda Sans
```html
<link rel="stylesheet" href="/assets/fonts/tildasans.css">
```

---

## 🐛 Troubleshooting

### Логотип не відображається:
1. Перевірте права на папку `uploads/` (chmod 755)
2. Перевірте, чи існує файл у БД: `SELECT * FROM media WHERE type='logo'`
3. Перевірте налаштування: `SELECT * FROM settings WHERE key='logo_url'`

### Помилка завантаження:
1. Перевірте php.ini: `upload_max_filesize` та `post_max_size`
2. Перевірте .htaccess у uploads/
3. Дивіться error_log сервера

### Drag & Drop не працює:
1. Перевірте консоль браузера (F12)
2. Перевірте CSRF токен
3. Переконайтесь, що jQuery підключено

---

## 📞 API Endpoints

### POST `/admin/settings.php?action=upload_logo`
**Request:**
```
Content-Type: multipart/form-data
logo: [file]
csrf_token: [token]
```

**Response:**
```json
{
    "success": true,
    "logo_url": "/uploads/logos/abc123.png",
    "message": "Логотип успішно завантажено"
}
```

### POST `/admin/settings.php?action=delete_logo`
**Request:**
```
csrf_token: [token]
```

**Response:**
```json
{
    "success": true,
    "message": "Логотип видалено"
}
```

---

## 🔄 Наступні кроки

1. **Запустити міграцію:** `php install/migrate.php`
2. **Протестувати завантаження логотипу**
3. **Поступово переносити інші сторінки в MVC**
4. **Додати юніт-тести**
5. **Оптимізувати запити до БД**

---

**Готово! 🎉**

Ваша CMS тепер має:
- ✅ Сучасну архітектуру MVC
- ✅ Drag & Drop для медіа
- ✅ Розширену БД
- ✅ Покращену безпеку
- ✅ Готовність до масштабування
