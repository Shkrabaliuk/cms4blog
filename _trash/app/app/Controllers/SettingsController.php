<?php
/**
 * SettingsController - управління налаштуваннями та логотипом
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/MediaModel.php';

class SettingsController extends BaseController {
    private $mediaModel;
    
    public function __construct() {
        parent::__construct();
        $this->mediaModel = new MediaModel($this->pdo);
    }
    
    /**
     * Сторінка налаштувань
     */
    public function index() {
        if (!is_admin()) {
            $this->redirect('/index.php');
        }
        
        // Отримуємо всі налаштування
        $stmt = $this->pdo->query("SELECT * FROM settings");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Отримуємо поточний логотип
        $logo = $this->mediaModel->getByType('logo');
        
        $pageTitle = "Налаштування";
        require __DIR__ . '/../../includes/templates/header.php';
        
        echo $this->view('admin/settings', [
            'settings' => $settings,
            'logo' => $logo
        ]);
        
        require __DIR__ . '/../../includes/templates/footer.php';
    }
    
    /**
     * Завантаження логотипу (AJAX)
     */
    public function uploadLogo() {
        header('Content-Type: application/json');
        
        if (!is_admin()) {
            $this->json(['error' => 'Unauthorized'], 403);
        }
        
        $this->validateCsrf();
        
        if (!isset($_FILES['logo'])) {
            $this->json(['error' => 'Файл не знайдено'], 400);
        }
        
        try {
            // Видаляємо старий логотип
            $oldLogo = $this->mediaModel->getByType('logo');
            if ($oldLogo) {
                $this->mediaModel->delete($oldLogo['id']);
            }
            
            // Завантажуємо новий
            $result = $this->mediaModel->upload($_FILES['logo'], 'logo');
            
            // Оновлюємо налаштування
            $stmt = $this->pdo->prepare("
                INSERT INTO settings (`key`, value, type) 
                VALUES ('logo_url', ?, 'file') 
                ON DUPLICATE KEY UPDATE value = ?
            ");
            $stmt->execute([$result['url'], $result['url']]);
            
            $this->json([
                'success' => true,
                'logo_url' => $result['url'],
                'message' => 'Логотип успішно завантажено'
            ]);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Видалення логотипу (AJAX)
     */
    public function deleteLogo() {
        header('Content-Type: application/json');
        
        if (!is_admin()) {
            $this->json(['error' => 'Unauthorized'], 403);
        }
        
        $this->validateCsrf();
        
        try {
            $logo = $this->mediaModel->getByType('logo');
            if ($logo) {
                $this->mediaModel->delete($logo['id']);
            }
            
            // Видаляємо з налаштувань
            $stmt = $this->pdo->prepare("DELETE FROM settings WHERE `key` = 'logo_url'");
            $stmt->execute();
            
            $this->json([
                'success' => true,
                'message' => 'Логотип видалено'
            ]);
            
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
    
    /**
     * Збереження налаштувань
     */
    public function save() {
        if (!is_admin()) {
            $this->redirect('/index.php');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/settings.php');
        }
        
        $this->validateCsrf();
        
        foreach ($_POST as $key => $value) {
            if ($key === 'csrf_token') continue;
            
            $type = 'text';
            if (is_numeric($value)) {
                $type = 'number';
            } elseif ($value === '0' || $value === '1') {
                $type = 'boolean';
            }
            
            $stmt = $this->pdo->prepare("
                INSERT INTO settings (`key`, value, type) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE value = ?, type = ?
            ");
            $stmt->execute([$key, $value, $type, $value, $type]);
        }
        
        $this->redirect('/admin/settings.php?saved=1');
    }
}
