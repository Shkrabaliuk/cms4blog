<?php
/**
 * Індексація всіх постів для пошуку
 * Запустіть цей скрипт після імпорту database.sql
 */

require_once __DIR__ . '/config/autoload.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/SearchService.php';

echo "🔍 Початок індексації постів...\n\n";

try {
    // Перевіряємо чи існують таблиці Rose Search
    $tablesExist = $pdo->query("SHOW TABLES LIKE 'rose_toc'")->rowCount() > 0;
    
    if (!$tablesExist) {
        echo "⚠️  Таблиці пошуку не знайдено. Створюю...\n";
        require_once __DIR__ . '/init_search_tables.php';
        echo "✅ Таблиці створено!\n\n";
    }
    
    $searchService = new SearchService($pdo);
    
    // Очищуємо старий індекс
    echo "Очищення старого індексу...\n";
    $pdo->exec("TRUNCATE TABLE rose_fulltext_index");
    $pdo->exec("TRUNCATE TABLE rose_keyword_index");
    $pdo->exec("TRUNCATE TABLE rose_toc");
    $pdo->exec("TRUNCATE TABLE rose_content");
    
    // Індексуємо всі пости
    $count = $searchService->reindexAll();
    
    echo "\n✅ Успішно проіндексовано постів: {$count}\n";
    
    // Показуємо статистику
    $stats = $pdo->query("
        SELECT 
            (SELECT COUNT(*) FROM rose_toc) as toc_count,
            (SELECT COUNT(*) FROM rose_fulltext_index) as fulltext_count,
            (SELECT COUNT(*) FROM rose_keyword_index) as keyword_count
    ")->fetch();
    
    echo "\n📊 Статистика індексу:\n";
    echo "   • Документів: {$stats['toc_count']}\n";
    echo "   • Fulltext записів: {$stats['fulltext_count']}\n";
    echo "   • Keyword записів: {$stats['keyword_count']}\n";
    
} catch (Exception $e) {
    echo "\n❌ Помилка: " . $e->getMessage() . "\n";
    echo "   Файл: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}

echo "\n🚀 Готово! Тепер можна використовувати пошук: http://localhost/cms4blog/search.php\n";
