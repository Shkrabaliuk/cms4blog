<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Migration;

class InstallController extends Controller
{
    private const LOCK_FILE = STORAGE_PATH . '/installed.lock';

    public function index(): void
    {
        // Перевірка чи вже встановлено
        if ($this->isInstalled()) {
            $this->redirect('/');
        }

        // Якщо POST - обробляємо установку
        if ($this->isPost()) {
            $this->processInstall();
            return;
        }

        // Показуємо форму
        $this->showInstallForm();
    }

    private function showInstallForm(): void
    {
        echo $this->render('install/simple');
    }

    private function processInstall(): void
    {
        try {
            // Отримуємо дані з форми
            $server = $this->inputPost('server', 'localhost');
            $username = $this->inputPost('username', 'root');
            $password = $this->inputPost('password', '');
            $database = $this->inputPost('database');
            $adminPassword = $this->inputPost('admin_password');

            // Валідація
            if (empty($database)) {
                $this->json(['success' => false, 'error' => 'Database name is required'], 400);
            }

            if (empty($adminPassword)) {
                $this->json(['success' => false, 'error' => 'Admin password is required'], 400);
            }

            // Налаштовуємо підключення до БД
            $config = [
                'host' => $server,
                'port' => '3306',
                'database' => $database,
                'username' => $username,
                'password' => $password,
            ];

            Database::configure($config);

            // Створюємо базу даних
            Database::createDatabase($database);

            // Виконуємо міграції
            $migrationsPath = BASE_PATH . '/database/migrations';
            Migration::runAll($migrationsPath);

            // Оновлюємо пароль адміністратора
            $hashedPassword = password_hash($adminPassword, PASSWORD_BCRYPT);
            Database::execute(
                "UPDATE users SET password = :password WHERE username = 'admin'",
                ['password' => $hashedPassword]
            );

            // Зберігаємо конфігурацію в .env
            $this->saveEnvConfig($config);

            // Створюємо lock файл
            file_put_contents(self::LOCK_FILE, date('Y-m-d H:i:s'));

            // Успіх
            $this->json(['success' => true, 'message' => 'Installation completed successfully']);

        } catch (\Exception $e) {
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function isInstalled(): bool
    {
        return file_exists(self::LOCK_FILE);
    }

    private function saveEnvConfig(array $config): void
    {
        $envFile = BASE_PATH . '/.env';
        
        if (!file_exists($envFile)) {
            $exampleFile = BASE_PATH . '/.env.example';
            if (file_exists($exampleFile)) {
                copy($exampleFile, $envFile);
            } else {
                file_put_contents($envFile, '');
            }
        }

        $envContent = file_get_contents($envFile);

        $replacements = [
            'DB_HOST' => $config['host'],
            'DB_PORT' => $config['port'],
            'DB_DATABASE' => $config['database'],
            'DB_USERNAME' => $config['username'],
            'DB_PASSWORD' => $config['password'],
        ];

        foreach ($replacements as $key => $value) {
            if (preg_match("/^{$key}=.*/m", $envContent)) {
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envFile, $envContent);
    }
}
