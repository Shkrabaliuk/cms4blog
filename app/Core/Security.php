<?php

declare(strict_types=1);

namespace App\Core;

class Security
{
    private const TOKEN_NAME = '_csrf_token';
    private const TOKEN_LENGTH = 32;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            $this->startSecureSession();
        }
    }

    private function startSecureSession(): void
    {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict',
        ]);

        session_start();

        if (!isset($_SESSION['_created'])) {
            $_SESSION['_created'] = time();
        }

        if (time() - $_SESSION['_created'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['_created'] = time();
        }
    }

    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION[self::TOKEN_NAME] = $token;
        return $token;
    }

    public function getToken(): string
    {
        if (!isset($_SESSION[self::TOKEN_NAME])) {
            return $this->generateToken();
        }

        return $_SESSION[self::TOKEN_NAME];
    }

    public function validateToken(?string $token): bool
    {
        if ($token === null || !isset($_SESSION[self::TOKEN_NAME])) {
            return false;
        }

        return hash_equals($_SESSION[self::TOKEN_NAME], $token);
    }

    public function csrfField(): string
    {
        $token = $this->getToken();
        return '<input type="hidden" name="' . self::TOKEN_NAME . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public function checkCsrf(): bool
    {
        $token = $_POST[self::TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        return $this->validateToken($token);
    }

    public function regenerateToken(): string
    {
        unset($_SESSION[self::TOKEN_NAME]);
        return $this->generateToken();
    }
}
