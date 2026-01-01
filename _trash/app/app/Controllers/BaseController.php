<?php
/**
 * BaseController - базовий контролер
 * Всі контролери наслідують цей клас
 */

class BaseController {
    protected $pdo;
    
    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    /**
     * Рендеринг view з даними
     */
    protected function view($template, $data = []) {
        extract($data);
        
        ob_start();
        require __DIR__ . '/../Views/' . $template . '.php';
        $content = ob_get_clean();
        
        return $content;
    }
    
    /**
     * JSON відповідь
     */
    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Редирект
     */
    protected function redirect($url) {
        header("Location: $url");
        exit;
    }
    
    /**
     * Перевірка CSRF токену
     */
    protected function validateCsrf() {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $this->json(['error' => 'Invalid CSRF token'], 403);
        }
    }
}
