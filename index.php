<?php
// index.php - Main Entry Point

// 1. Check for Installation
if (!file_exists(__DIR__ . '/src/Config/db.php')) {
    if (file_exists(__DIR__ . '/install.php')) {
        header('Location: /install.php');
        exit;
    } else {
        die('System is not installed and install.php is missing.');
    }
}

// 2. Composer Autoload
require __DIR__ . '/vendor/autoload.php';

use Bramus\Router\Router;
use App\Controllers\HomeController;
use App\Controllers\PostController;
use App\Controllers\RssController;
use App\Controllers\SitemapController;
use App\Controllers\SearchController;
use App\Controllers\Api\AuthController;
use App\Controllers\Api\CommentController;

// 3. Initialize Router
$router = new Router();

// 4. Load Routes
require __DIR__ . '/src/routes.php';

// 5. Run Application
$router->run();