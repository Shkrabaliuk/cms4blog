# 📦 Керівництво з встановлення CMS4Blog

Детальна інструкція по встановленню та налаштуванню CMS4Blog.

## 🎯 Передумови

Перед встановленням переконайтеся, що ваш сервер відповідає системним вимогам:

### Мінімальні вимоги:

- **PHP:** 8.0 або вище
- **MySQL:** 5.7+ або MariaDB 10.2+
- **Web Server:** Apache 2.4+ з mod_rewrite або Nginx 1.18+
- **PHP Extensions:**
  - PDO
  - pdo_mysql
  - json
  - mbstring
- **Права запису:** на директорії `storage/cache` та `storage/logs`

### Рекомендовані налаштування PHP:

```ini
memory_limit = 128M
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 300
```

## 📥 Варіанти встановлення

### Варіант 1: Локальна розробка (XAMPP/WAMP/MAMP)

1. **Завантажте CMS4Blog:**
   ```bash
   # Завантажте архів або склонуйте репозиторій
   git clone https://github.com/yourusername/cms4blog.git
   ```

2. **Помістіть файли у htdocs:**
   - XAMPP: `C:\xampp\htdocs\cms4blog`
   - WAMP: `C:\wamp64\www\cms4blog`
   - MAMP: `/Applications/MAMP/htdocs/cms4blog`

3. **Налаштуйте права:**
   ```bash
   chmod -R 755 storage/
   chmod -R 755 database/
   ```

4. **Відкрийте у браузері:**
   ```
   http://localhost/cms4blog/public
   ```

5. **Слідуйте майстру встановлення**

### Варіант 2: Linux сервер (Ubuntu/Debian)

1. **Оновіть систему:**
   ```bash
   sudo apt update && sudo apt upgrade -y
   ```

2. **Встановіть необхідне ПЗ:**
   ```bash
   sudo apt install apache2 php8.1 php8.1-mysql php8.1-mbstring php8.1-json mysql-server -y
   ```

3. **Увімкніть mod_rewrite:**
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

4. **Завантажте CMS4Blog:**
   ```bash
   cd /var/www/html
   sudo git clone https://github.com/yourusername/cms4blog.git
   cd cms4blog
   ```

5. **Налаштуйте права:**
   ```bash
   sudo chown -R www-data:www-data storage/
   sudo chmod -R 755 storage/
   ```

6. **Створіть віртуальний хост Apache:**
   ```bash
   sudo nano /etc/apache2/sites-available/cms4blog.conf
   ```

   Вміст файлу:
   ```apache
   <VirtualHost *:80>
       ServerName cms4blog.local
       DocumentRoot /var/www/html/cms4blog/public

       <Directory /var/www/html/cms4blog/public>
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>

       ErrorLog ${APACHE_LOG_DIR}/cms4blog-error.log
       CustomLog ${APACHE_LOG_DIR}/cms4blog-access.log combined
   </VirtualHost>
   ```

7. **Активуйте сайт:**
   ```bash
   sudo a2ensite cms4blog.conf
   sudo systemctl restart apache2
   ```

8. **Додайте до /etc/hosts:**
   ```bash
   sudo nano /etc/hosts
   # Додайте рядок:
   127.0.0.1 cms4blog.local
   ```

9. **Відкрийте у браузері:**
   ```
   http://cms4blog.local/install
   ```

### Варіант 3: Nginx сервер

1. **Встановіть Nginx та PHP-FPM:**
   ```bash
   sudo apt install nginx php8.1-fpm php8.1-mysql php8.1-mbstring php8.1-json mysql-server -y
   ```

2. **Завантажте CMS4Blog:**
   ```bash
   cd /var/www
   sudo git clone https://github.com/yourusername/cms4blog.git
   cd cms4blog
   ```

3. **Налаштуйте права:**
   ```bash
   sudo chown -R www-data:www-data storage/
   sudo chmod -R 755 storage/
   ```

4. **Створіть конфігурацію Nginx:**
   ```bash
   sudo nano /etc/nginx/sites-available/cms4blog
   ```

   Вміст:
   ```nginx
   server {
       listen 80;
       server_name cms4blog.local;
       root /var/www/cms4blog/public;
       index index.php index.html;

       access_log /var/log/nginx/cms4blog-access.log;
       error_log /var/log/nginx/cms4blog-error.log;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
       }

       location ~ /\.(?!well-known).* {
           deny all;
       }

       location ~* \.(env|log|ini|conf|sql|sh|bak)$ {
           deny all;
       }
   }
   ```

5. **Активуйте конфігурацію:**
   ```bash
   sudo ln -s /etc/nginx/sites-available/cms4blog /etc/nginx/sites-enabled/
   sudo nginx -t
   sudo systemctl restart nginx
   ```

## 🗄️ Налаштування MySQL

### Створення бази даних (опціонально)

Інсталятор створить базу автоматично, але ви можете створити її вручну:

```sql
CREATE DATABASE cms4blog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'cms4blog_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON cms4blog.* TO 'cms4blog_user'@'localhost';
FLUSH PRIVILEGES;
```

## 🚀 Процес встановлення через веб-інтерфейс

### Крок 1: Привітання
- Ознайомтеся з можливостями CMS
- Натисніть "Продовжити"

### Крок 2: Перевірка системи
Інсталятор перевірить:
- ✅ Версію PHP (8.0+)
- ✅ PDO Extension
- ✅ PDO MySQL Driver
- ✅ JSON Extension
- ✅ mbstring Extension
- ✅ Права запису на storage/cache
- ✅ Права запису на storage/logs

**Якщо щось не пройдено:**
- Встановіть відсутні розширення
- Налаштуйте права доступу: `chmod -R 755 storage/`

### Крок 3: База даних

Введіть дані підключення:

| Поле | Опис | Приклад |
|------|------|---------|
| Хост | MySQL сервер | `localhost` або `127.0.0.1` |
| Порт | MySQL порт | `3306` (за замовчуванням) |
| Назва БД | Ім'я бази | `cms4blog` |
| Користувач | MySQL user | `root` або ваш user |
| Пароль | MySQL пароль | Ваш пароль або порожньо |

**Важливо:**
- База даних буде створена автоматично
- Переконайтеся, що користувач має права на CREATE DATABASE

### Крок 4: Завершення

Після успішного встановлення:
- ✅ База даних створена
- ✅ Таблиці створені (settings, users, migrations)
- ✅ Створено адміністратора
- ✅ Файл .env оновлено
- ✅ Lock-файл створено

**Дані для входу за замовчуванням:**
```
Username: admin
Email: admin@cms4blog.local
Password: admin123
```

⚠️ **ОБОВ'ЯЗКОВО змініть пароль після першого входу!**

## 🔧 Ручна конфігурація

### Якщо веб-інсталятор не працює:

1. **Скопіюйте .env.example в .env:**
   ```bash
   cp .env.example .env
   ```

2. **Відредагуйте .env:**
   ```bash
   nano .env
   ```

   ```env
   APP_ENV=development
   APP_DEBUG=true
   APP_URL=http://yourdomain.com
   APP_NAME=CMS4Blog

   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=cms4blog
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

3. **Створіть базу даних:**
   ```sql
   CREATE DATABASE cms4blog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

4. **Виконайте міграції вручну:**
   - Відкрийте файли з `database/migrations/`
   - Виконайте SQL з функції `up()` кожного файлу

5. **Створіть lock-файл:**
   ```bash
   touch storage/installed.lock
   ```

## ✅ Перевірка встановлення

### 1. Тест головної сторінки:
```
http://yourdomain.com/
```
Повинна відкритися красива головна сторінка з features.

### 2. Тест сторінки "Про систему":
```
http://yourdomain.com/about
```

### 3. Перевірка бази даних:
```sql
USE cms4blog;
SHOW TABLES;
-- Повинні бути: migrations, settings, users
```

### 4. Перевірка логів:
```bash
tail -f storage/logs/error.log
```

## 🐛 Усунення проблем

### Помилка: "Class not found"
```bash
# Перевірте autoloader
ls -la app/Core/
# Переконайтеся що всі файли на місці
```

### Помилка: "Permission denied"
```bash
# Налаштуйте права
sudo chown -R www-data:www-data storage/
sudo chmod -R 755 storage/
```

### Помилка: "Database connection failed"
```bash
# Перевірте MySQL
sudo systemctl status mysql

# Тест підключення
mysql -u root -p -e "SELECT 1;"

# Перевірте .env
cat .env | grep DB_
```

### Помилка: "404 Not Found" на всіх сторінках
```bash
# Apache: перевірте mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2

# Перевірте .htaccess у public/
ls -la public/.htaccess
```

### Білий екран (White Screen of Death)
```bash
# Увімкніть відображення помилок
# Відредагуйте public/index.php
error_reporting(E_ALL);
ini_set('display_errors', '1');

# Перевірте логи
tail -f storage/logs/error.log
tail -f /var/log/apache2/error.log  # Apache
tail -f /var/log/nginx/error.log    # Nginx
```

## 🔐 Безпека після встановлення

1. **Змініть пароль адміністратора** (коли буде реалізовано)

2. **Видаліть інсталятор** (опціонально):
   ```bash
   rm -rf app/Controllers/InstallController.php
   rm -rf templates/install/
   ```

3. **Налаштуйте production режим:**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```

4. **Налаштуйте HTTPS** (рекомендовано):
   ```bash
   # Let's Encrypt
   sudo apt install certbot python3-certbot-apache
   sudo certbot --apache -d yourdomain.com
   ```

5. **Регулярні backup:**
   ```bash
   # База даних
   mysqldump -u root -p cms4blog > backup_$(date +%Y%m%d).sql
   
   # Файли
   tar -czf cms4blog_backup_$(date +%Y%m%d).tar.gz /var/www/cms4blog
   ```

## 📞 Підтримка

Якщо у вас виникли проблеми:
- 📖 Перечитайте документацію
- 🐛 Перевірте [Issues](https://github.com/yourusername/cms4blog/issues)
- 💬 Створіть новий Issue з детальним описом проблеми

---

**Успішного встановлення! 🎉**
