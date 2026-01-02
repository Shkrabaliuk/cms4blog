<?php
// admin/upload_image.php - Завантаження картинок з drag & drop

require_once '../config/db.php';
require_once '../includes/auth.php';

// Перевірка авторизації
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Перевірка методу
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Перевірка наявності файлу
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['image'];
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

// Перевірка типу файлу
if (!in_array($file['type'], $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file type. Allowed: JPG, PNG, GIF, WebP']);
    exit;
}

// Перевірка розміру (максимум 10MB)
$maxSize = 10 * 1024 * 1024; // 10MB
if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['error' => 'File too large. Maximum 10MB']);
    exit;
}

// Створення структури папок: uploads/YYYY/MM/
$year = date('Y');
$month = date('m');
$uploadDir = "../uploads/{$year}/{$month}";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Генерація унікального імені файлу
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '_' . time() . '.' . $extension;
$filepath = "{$uploadDir}/{$filename}";

// Переміщення завантаженого файлу
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save file']);
    exit;
}

// TODO: Генерація thumbnail (WebP + оригінал)
// TODO: Збереження метаданих в БД (таблиця media)

// Повертаємо URL для вставки в редактор
$url = "/uploads/{$year}/{$month}/{$filename}";
// Для Neasden потрібен відносний шлях від pathMedia
$neasdenPath = "{$year}/{$month}/{$filename}";

echo json_encode([
    'success' => true,
    'url' => $url,
    'neasden' => $neasdenPath, // Формат для Neasden
    'markdown' => "![{$file['name']}]({$url})", // Формат для Markdown
    'filename' => $filename
]);
