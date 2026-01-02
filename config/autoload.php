<?php
/**
 * PSR-4 автозавантажувач для бібліотек Rose та Neasden
 */

spl_autoload_register(function ($class) {
    // Rose бібліотека (S2\Rose\...)
    if (strpos($class, 'S2\\Rose\\') === 0) {
        $file = __DIR__ . '/../assets/libs/rose/' . str_replace('\\', '/', substr($class, 8)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
    
    // Neasden бібліотека (Neasden\...)
    if (strpos($class, 'Neasden\\') === 0) {
        $file = __DIR__ . '/../assets/libs/neasden/' . str_replace('\\', '/', substr($class, 8)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

/**
 * Helper функція для Neasden - отримання розмірів зображення
 * Обгортка для getimagesize з обробкою помилок
 */
if (!function_exists('e2_getimagesize')) {
    function e2_getimagesize($filename) {
        if (!file_exists($filename)) {
            return false;
        }
        
        $size = @getimagesize($filename);
        return $size !== false ? $size : false;
    }
}
