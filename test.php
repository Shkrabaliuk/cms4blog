<?php
// test.php - Diagnostic test file
echo "=== DIAGNOSTIC TEST ===\n\n";

// 1. PHP Version
echo "1. PHP Version: " . PHP_VERSION . "\n\n";

// 2. Check if files exist
$files = [
    'index.php',
    'src/routes.php',
    'src/Controllers/PageController.php',
    'src/Controllers/Admin/SettingsController.php',
    'templates/pages/about.php',
    'templates/pages/admin_settings.php',
    'vendor/autoload.php'
];

echo "2. File Check:\n";
foreach ($files as $file) {
    $exists = file_exists(__DIR__ . '/' . $file) ? '✓' : '✗';
    echo "   $exists $file\n";
}

// 3. Check if DB config exists
echo "\n3. Database Config: ";
if (file_exists(__DIR__ . '/src/Config/db.php')) {
    echo "✓ EXISTS\n";
} else {
    echo "✗ MISSING\n";
}

// 4. Try to load autoloader
echo "\n4. Autoloader: ";
try {
    require __DIR__ . '/vendor/autoload.php';
    echo "✓ LOADED\n";
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}

// 5. Check .htaccess
echo "\n5. .htaccess: ";
if (file_exists(__DIR__ . '/.htaccess')) {
    echo "✓ EXISTS\n";
    echo "   Content:\n";
    echo "   " . str_replace("\n", "\n   ", file_get_contents(__DIR__ . '/.htaccess')) . "\n";
} else {
    echo "✗ MISSING\n";
}

// 6. Check mod_rewrite
echo "\n6. Apache Modules:\n";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo "   mod_rewrite: " . (in_array('mod_rewrite', $modules) ? '✓' : '✗') . "\n";
} else {
    echo "   Cannot check (not Apache or CGI mode)\n";
}

// 7. Current REQUEST_URI
echo "\n7. Current Request:\n";
echo "   REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
echo "   SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";

echo "\n=== END TEST ===\n";
