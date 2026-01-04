<?php
namespace App\Services;

class Cache
{
    private static $cacheDir = __DIR__ . '/../../storage/cache';
    private static $enabled = true; // Turn to true/false to toggle globally

    private static function getPath($key)
    {
        // Sanitize key to be safe filename
        $key = preg_replace('/[^a-z0-9\_\-]/i', '_', $key);
        return self::$cacheDir . '/' . md5($key) . '.cache';
    }

    public static function get($key, $duration = 3600)
    {
        if (!self::$enabled)
            return false;

        $file = self::getPath($key);

        if (file_exists($file)) {
            // Check if expired
            if ((time() - filemtime($file)) < $duration) {
                return file_get_contents($file);
            }
            // Expired, delete it
            @unlink($file);
        }

        return false;
    }

    public static function set($key, $content)
    {
        if (!self::$enabled)
            return;

        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0777, true);
        }

        file_put_contents(self::getPath($key), $content);
    }

    public static function clear()
    {
        if (!is_dir(self::$cacheDir))
            return;

        $files = glob(self::$cacheDir . '/*');
        foreach ($files as $file) {
            if (is_file($file))
                unlink($file);
        }
    }
}
