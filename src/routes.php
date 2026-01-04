<?php
use App\Controllers\HomeController;
use App\Controllers\PostController;
use App\Controllers\RssController;
use App\Controllers\SitemapController;
use App\Controllers\SearchController;
use App\Controllers\Api\AuthController;
use App\Controllers\Api\CommentController;
use App\Controllers\PageController;
use App\Controllers\ArchiveController;
use App\Controllers\TagController;

/** @var \Bramus\Router\Router $router */

// 404 Handler
$router->set404(function () {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    // Note: Template path might change in next steps, keeping current for now
    require __DIR__ . '/../templates/pages/404.php';
});

// Home
$router->get('/', function () {
    $controller = new HomeController();
    $controller->index();
});

// Archive
$router->get('/archive', function () {
    $controller = new ArchiveController();
    $controller->index();
});

// About
$router->get('/about', function () {
    $controller = new PageController();
    $controller->about();
});

// Single Post View
$router->get('/post/([a-z0-9\-]+)', function ($slug) {
    $controller = new PostController();
    $controller->show($slug);
});

// Tag View
$router->get('/tag/([a-z0-9\-\._%\+]+)', function ($slug) { // Added + and _ support
    $controller = new TagController();
    $controller->show($slug);
});

// RSS & Sitemap
$router->get('/rss.php', function () {
    (new RssController())->index();
});

$router->get('/sitemap.php', function () {
    (new SitemapController())->index();
});

// Search
$router->get('/search.php', function () {
    (new SearchController())->index();
});

// API Routes
$router->post('/api/login.php', function () {
    (new AuthController())->login();
});

$router->get('/api/logout.php', function () {
    (new AuthController())->logout();
});

$router->post('/api/post_comment.php', function () {
    (new CommentController())->store();
});

// Legacy/Root fallback
$router->get('/([a-z0-9\-]+)', function ($slug) {
    $controller = new PostController();
    $controller->show($slug);
});

// Admin Routes
$router->mount('/admin', function () use ($router) {
    // Dashboard
    $router->get('/', function () {
        header('Location: /admin/settings');
    });

    // Settings
    $router->get('/settings', function () {
        (new \App\Controllers\Admin\SettingsController())->index();
    });

    $router->post('/settings', function () {
        (new \App\Controllers\Admin\SettingsController())->update();
    });

    // New Post
    $router->get('/new-post', function () {
        (new \App\Controllers\Admin\PostController())->newPost();
    });

    // Post Management
    $router->post('/save_post', function () {
        (new \App\Controllers\Admin\PostController())->save();
    });

    $router->post('/delete_post', function () {
        (new \App\Controllers\Admin\PostController())->delete();
    });

    // Utilities
    $router->get('/backup', function () {
        (new \App\Controllers\Admin\BackupController())->download();
    });

    $router->get('/clear_logs', function () {
        (new \App\Controllers\Admin\LogsController())->clear();
    });

    $router->post('/reinstall', function () {
        (new \App\Controllers\Admin\ReinstallController())->execute();
    });

    $router->post('/upload_image', function () {
        (new \App\Controllers\Admin\MediaController())->upload();
    });

    // Legacy admin files fallback
    $router->get('/(.*)', function ($file) {
        $path = __DIR__ . '/admin/' . $file;
        if (file_exists($path)) {
            require $path;
        } else {
            echo "Admin page not found.";
        }
    });
});
