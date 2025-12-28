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

// Installation routes (only if not installed)
if (!$isInstalled) {
    $router->get('/install', [InstallController::class, 'index']);
    $router->post('/install', [InstallController::class, 'index']);
}

// Main routes
$router->get('/', [HomeController::class, 'index']);
$router->get('/about', [HomeController::class, 'about']);

// Redirect to install if not installed and accessing any other route
if (!$isInstalled && $_SERVER['REQUEST_URI'] !== '/install') {
    header('Location: /install');
    exit;
}

// Example: API routes group
// $router->group('/api', function (Router $router) {
//     $router->get('/posts', [PostController::class, 'index']);
//     $router->get('/posts/{id}', [PostController::class, 'show']);
//     $router->post('/posts', [PostController::class, 'store']);
// });

// Example: Admin routes with middleware
// $router->group('/admin', function (Router $router) {
//     $router->get('/dashboard', [AdminController::class, 'dashboard']);
// }, ['AuthMiddleware']);
