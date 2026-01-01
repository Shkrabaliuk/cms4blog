<?php
/**
 * MediaModel - модель для роботи з медіа-файлами
 */

class MediaModel {
    private $pdo;
    private $uploadDir = __DIR__ . '/../../uploads/';
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        
        // Створюємо директорії якщо відсутні
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
        foreach (['logos', 'avatars', 'posts', 'gallery'] as $dir) {
            if (!is_dir($this->uploadDir . $dir)) {
                mkdir($this->uploadDir . $dir, 0755, true);
            }
        }
    }
    
    /**
     * Завантаження файлу
     */
    public function upload($file, $type = 'post_image') {
        // Валідація
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Недозволений тип файлу');
        }
        
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            throw new Exception('Файл занадто великий (макс. 5MB)');
        }
        
        // Генерація унікального імені
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        
        // Визначення підпапки
        $subfolder = [
            'logo' => 'logos',
            'avatar' => 'avatars',
            'post_image' => 'posts',
            'gallery' => 'gallery'
        ][$type] ?? 'posts';
        
        $filepath = $this->uploadDir . $subfolder . '/' . $filename;
        
        // Переміщення файлу
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Помилка завантаження файлу');
        }
        
        // Збереження в БД
        $stmt = $this->pdo->prepare("
            INSERT INTO media (filename, original_name, mime_type, size, type) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $subfolder . '/' . $filename,
            $file['name'],
            $file['type'],
            $file['size'],
            $type
        ]);
        
        return [
            'id' => $this->pdo->lastInsertId(),
            'filename' => $subfolder . '/' . $filename,
            'url' => '/uploads/' . $subfolder . '/' . $filename
        ];
    }
    
    /**
     * Видалення файлу
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("SELECT filename FROM media WHERE id = ?");
        $stmt->execute([$id]);
        $media = $stmt->fetch();
        
        if ($media) {
            $filepath = $this->uploadDir . $media['filename'];
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            
            $stmt = $this->pdo->prepare("DELETE FROM media WHERE id = ?");
            $stmt->execute([$id]);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Отримання файлу за типом
     */
    public function getByType($type, $limit = 1) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM media 
            WHERE type = ? 
            ORDER BY uploaded_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$type, $limit]);
        
        return $limit === 1 ? $stmt->fetch() : $stmt->fetchAll();
    }
}
