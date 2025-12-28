<?php

declare(strict_types=1);

/**
 * Routes Configuration
 * 
 * Define all application routes here.
 */

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\InstallController;

/** @var Router $router */

// Check if system is installed
$installedLockFile = STORAGE_PATH . '/installed.lock';
$isInstalled = file_exists($installedLockFile);

// Main route - показує інсталятор або головну залежно від стану
if (!$isInstalled) {
    // Якщо не встановлено - показуємо інсталятор на головній
    $router->get('/', [InstallController::class, 'index']);
    $router->post('/', [InstallController::class, 'index']);
} else {
    // Якщо встановлено - показуємо звичайні сторінки
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/about', [HomeController::class, 'about']);
}
