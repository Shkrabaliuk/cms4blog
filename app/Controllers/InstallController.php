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
        if ($this->isInstalled()) {
            $this->redirect('/?already_installed=1');
        }

        $step = $this->inputGet('step', 'welcome');
        
        switch ($step) {
            case 'requirements':
                $this->showRequirements();
                break;
            case 'database':
                $this->showDatabase();
                break;
            case 'install':
                $this->processInstall();
                break;
            default:
                $this->showWelcome();
        }
    }

    private function showWelcome(): void
    {
        echo $this->render('install/welcome');
    }

    private function showRequirements(): void
    {
        $requirements = [
            'PHP Version >= 8.0' => version_compare(PHP_VERSION, '8.0.0', '>='),
            'PDO Extension' => extension_loaded('pdo'),
            'PDO MySQL Driver' => extension_loaded('pdo_mysql'),
            'JSON Extension' => extension_loaded('json'),
            'mbstring Extension' => extension_loaded('mbstring'),
            'Storage Directory Writable' => is_writable(STORAGE_PATH),
            'Cache Directory Writable' => is_writable(STORAGE_PATH . '/cache'),
            'Logs Directory Writable' => is_writable(STORAGE_PATH . '/logs'),
        ];

        $allPassed = !in_array(false, $requirements, true);

        echo $this->render('install/requirements', [
            'requirements' => $requirements,
            'allPassed' => $allPassed,
        ]);
    }

    private function showDatabase(): void
    {
        $error = null;
        $envFile = BASE_PATH . '/.env';
        
        $config = [
            'host' => getenv('DB_HOST') ?: 'localhost',
            'port' => getenv('DB_PORT') ?: '3306',
            'database' => getenv('DB_DATABASE') ?: 'cms4blog',
            'username' => getenv('DB_USERNAME') ?: 'root',
            'password' => getenv('DB_PASSWORD') ?: '',
        ];

        if ($this->isPost()) {
            $config = [
                'host' => $this->inputPost('db_host', 'localhost'),
                'port' => $this->inputPost('db_port', '3306'),
                'database' => $this->inputPost('db_database', 'cms4blog'),
                'username' => $this->inputPost('db_username', 'root'),
                'password' => $this->inputPost('db_password', ''),
            ];

            try {
                // Test connection
                Database::configure($config);
                
                // Try to create database
                Database::createDatabase($config['database']);

                // Save to .env
                $this->saveEnvConfig($config);

                $this->redirect('/install?step=install');
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }

        echo $this->render('install/database', [
            'config' => $config,
            'error' => $error,
        ]);
    }

    private function processInstall(): void
    {
        if ($this->isInstalled()) {
            $this->redirect('/');
        }

        $error = null;
        $success = false;

        try {
            // Load database config
            $config = [
                'host' => getenv('DB_HOST'),
                'port' => getenv('DB_PORT'),
                'database' => getenv('DB_DATABASE'),
                'username' => getenv('DB_USERNAME'),
                'password' => getenv('DB_PASSWORD'),
            ];

            Database::configure($config);

            // Run migrations
            $migrationsPath = BASE_PATH . '/database/migrations';
            $executedMigrations = Migration::runAll($migrationsPath);

            // Create lock file
            file_put_contents(self::LOCK_FILE, date('Y-m-d H:i:s'));

            $success = true;

        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        echo $this->render('install/complete', [
            'success' => $success,
            'error' => $error,
        ]);
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
