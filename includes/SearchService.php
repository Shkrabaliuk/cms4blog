<?php
/**
 * Search Service — Fixed for 3 arguments
 */

require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../config/db.php';

use S2\Rose\Indexer;
use S2\Rose\Finder;
use S2\Rose\SnippetBuilder;
use S2\Rose\Storage\Database\PdoStorage;
use S2\Rose\Entity\Indexable;
use S2\Rose\Entity\Query;
use S2\Rose\Stemmer\StemmerInterface;

// ХАК: Локальний стемер
if (!class_exists('LocalStemmer')) {
    class LocalStemmer implements StemmerInterface {
        public function stemWord($word) {
            return (string)$word;
        }
    }
}

class SearchService
{
    private $pdo;
    private $stemmer;
    private $storage;
    
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->stemmer = new LocalStemmer();
        $this->storage = new PdoStorage($pdo, 'rose_');
    }
    
    // ... indexPost залишаємо без змін ...
    public function indexPost($post)
    {
        $indexer = new Indexer($this->storage, $this->stemmer);
        $externalId = 'post-' . $post['id'];
        $url = '?route=post&slug=' . $post['slug'];
        $content = strip_tags($post['content']);
        
        $indexable = new Indexable($externalId, $post['title'], $content);
        $indexable->setUrl($url)
                  ->setDescription(mb_substr($content, 0, 200))
                  ->setDate(new \DateTime($post['created_at']));
        
        $indexer->index($indexable);
        return true;
    }
    
    /**
     * Пошук по запиту
     */
    public function search($queryString, $limit = 10)
    {
        if (empty(trim($queryString))) {
            return [];
        }
        
        $finder = new Finder($this->storage, $this->stemmer);
        
        // Шаблон підсвітки (передамо його і в finder, і в snippetBuilder)
        $highlightTemplate = '<mark style="background: #fff3cd; padding: 2px;">%s</mark>';
        $finder->setHighlightTemplate($highlightTemplate);
        
        $snippetBuilder = new SnippetBuilder($this->stemmer);
        $snippetBuilder->setSnippetLineSeparator(' ... ');
        
        $query = new Query($queryString);
        $resultSet = $finder->find($query);
        
        $results = [];
        $items = $resultSet->getItems();
        $items = array_slice($items, 0, $limit);
        
        foreach ($items as $item) {
            $externalId = (string) $item->getId();
            
            if (preg_match('/^post[-_](\d+)$/', $externalId, $matches)) {
                $postId = $matches[1];
                
                $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE id = ?");
                $stmt->execute([$postId]);
                $post = $stmt->fetch();
                
                if ($post) {
                    // === ВИПРАВЛЕННЯ ТУТ ===
                    // Передаємо 3 аргументи: Query, Text, HighlightTemplate
                    $snippet = $snippetBuilder->buildSnippet(
                        $query, 
                        strip_tags($post['content']), 
                        $highlightTemplate // <--- Третій обов'язковий аргумент
                    );
                    
                    $results[] = [
                        'id'        => $post['id'],
                        'title'     => $post['title'],
                        'slug'      => $post['slug'],
                        'snippet'   => is_object($snippet) ? $snippet->toString() : (string)$snippet,
                        'relevance' => $item->getRelevance(),
                        'date'      => $post['created_at']
                    ];
                }
            }
        }
        
        return $results;
    }
}