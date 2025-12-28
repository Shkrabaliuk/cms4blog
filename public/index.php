<?php

declare(strict_types=1);

/**
 * CMS4Blog - Entry Point
 * 
 * All requests are routed through this file.
 */

// Prevent direct access to other PHP files
define('APP_START', microtime(true));

// Error reporting based on environment
$isProduction = getenv('APP_ENV') === 'production';

if ($isProduction) {
    error_reporting(0);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Set default timezone
date_default_timezone_set('UTC');

// Define base paths
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('TEMPLATES_PATH', BASE_PATH . '/templates');
define('PUBLIC_PATH', __DIR__);

// PSR-4 Autoloader
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = APP_PATH . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Load environment variables
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (!getenv($name)) {
                putenv("{$name}={$value}");
            }
        }
    }
}

// Global exception handler
set_exception_handler(function (Throwable $e): void {
    $isProduction = getenv('APP_ENV') === 'production';
    
    http_response_code(500);
    
    if ($isProduction) {
        echo '500 Internal Server Error';
        
        // Log error
        $logFile = STORAGE_PATH . '/logs/error.log';
        $message = sprintf(
            "[%s] %s: %s in %s:%d\n",
            date('Y-m-d H:i:s'),
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
        error_log($message, 3, $logFile);
    } else {
        echo '<h1>Error</h1>';
        echo '<p><strong>' . htmlspecialchars(get_class($e)) . '</strong>: ';
        echo htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p>File: ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    }
    
    exit(1);
});

// Global error handler - convert errors to exceptions
set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Bootstrap application
use App\Core\Router;
use App\Core\Container;

$container = new Container();
$router = new Router();

// Register core services
$container->singleton(Router::class, fn() => $router);
$container->singleton(Container::class, fn() => $container);

// Load routes
$routesFile = CONFIG_PATH . '/routes.php';
if (file_exists($routesFile)) {
    require $routesFile;
}

// Dispatch request
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$router->dispatch($method, $uri);
