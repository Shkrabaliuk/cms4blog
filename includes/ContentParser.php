<?php
/**
 * ContentParser - обгортка для Neasden markup парсера
 * 
 * Neasden - власний Markdown-подібний парсер Ілії Бірмана для Aegea
 * Підтримує: заголовки, списки, цитати, код з підсвічуванням, відео, аудіо, фотогалереї
 */

require_once __DIR__ . '/../config/autoload.php';

// Явно завантажуємо Configuration.php, бо там два класи (Configuration + DefaultConfiguration)
require_once __DIR__ . '/../assets/libs/neasden/Configuration.php';

use Neasden\Configuration;
use Neasden\DefaultConfiguration;
use Neasden\Interpreter;
use Neasden\Renderer;

class ContentParser {
    private $interpreter;
    private $renderer;
    private $configuration;
    
    public function __construct() {
        // Створюємо конфігурацію з базовими налаштуваннями
        $this->configuration = new DefaultConfiguration();
        
        // Налаштування для нашого CMS
        $this->configuration->language = 'uk';
        $this->configuration->baseUrl = '/';
        // Вказуємо абсолютний шлях до uploads на сервері
        $this->configuration->pathMedia = __DIR__ . '/../uploads/';
        $this->configuration->htmlImgSrcPrefix = '/uploads/';
        
        // Типографіка
        $this->configuration->typographyOn = true;
        $this->configuration->typographyQuotes = true;
        $this->configuration->typographyMarkup = true;
        $this->configuration->typographyAutoHref = true;
        
        // Підсвічування коду
        $this->configuration->htmlCodeOn = true;
        $this->configuration->htmlCodeWrap = ['<pre><code>', '</code></pre>'];
        
        // Розширення (відключаємо складні)
        $this->configuration->extensions = [
            'Picture',  // Картинки з підписами
            'HR',       // Горизонтальні лінії
            'Block',    // Блоки цитат/коду
            'Table',    // Таблиці
            // 'Fotorama', // Галереї (можна включити пізніше)
            // 'Video',    // Відео (YouTube, Vimeo)
            // 'Audio',    // Аудіо
            // 'Tweet',    // Вбудовані твіти
        ];
        
        // Ініціалізуємо парсер та рендерер
        $this->interpreter = new Interpreter($this->configuration);
        $this->renderer = new Renderer($this->configuration);
    }
    
    /**
     * Парсинг контенту з Neasden розмітки в HTML
     * 
     * @param string $content Вихідний текст у Neasden форматі
     * @return string HTML результат
     */
    public function parse($content) {
        if (empty($content)) {
            return '';
        }
        
        // Інтерпретуємо розмітку
        $this->interpreter->setInput($content);
        $this->interpreter->run();
        $result = $this->interpreter->getModel();
        
        // Рендеримо в HTML
        $this->renderer->setModel($result);
        $html = $this->renderer->getHTML();
        
        return $html;
    }
    
    /**
     * Отримання чистого тексту без HTML для пошуку та анонсів
     * 
     * @param string $content Вихідний текст у Neasden форматі
     * @return string Текст без тегів
     */
    public function getPlainText($content) {
        $html = $this->parse($content);
        return strip_tags($html);
    }
    
    /**
     * Створення анонсу (перші N символів)
     * 
     * @param string $content Вихідний текст
     * @param int $length Максимальна довжина
     * @return string Скорочений текст з "..."
     */
    public function getExcerpt($content, $length = 300) {
        $plain = $this->getPlainText($content);
        
        if (mb_strlen($plain) <= $length) {
            return $plain;
        }
        
        // Обрізаємо по словах
        $excerpt = mb_substr($plain, 0, $length);
        $lastSpace = mb_strrpos($excerpt, ' ');
        
        if ($lastSpace !== false) {
            $excerpt = mb_substr($excerpt, 0, $lastSpace);
        }
        
        return $excerpt . '...';
    }
    
    /**
     * Налаштування конфігурації
     */
    public function setLanguage($lang) {
        $this->configuration->language = $lang;
        return $this;
    }
    
    public function setMediaPath($path) {
        $this->configuration->pathMedia = $path;
        return $this;
    }
    
    public function enableExtension($extension) {
        if (!in_array($extension, $this->configuration->extensions)) {
            $this->configuration->extensions[] = $extension;
        }
        return $this;
    }
}
