<?php $this->startSection('styles'); ?>
<style>
    .about-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 60px 20px;
        text-align: center;
        border-radius: 12px;
        margin-bottom: 40px;
    }
    .about-hero h2 {
        font-size: 2.5rem;
        margin-bottom: 15px;
    }
    .content-section {
        background: white;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    .content-section h3 {
        color: #667eea;
        margin-bottom: 20px;
        font-size: 1.8rem;
    }
    .content-section p {
        color: #666;
        line-height: 1.8;
        margin-bottom: 15px;
    }
    .content-section ul {
        margin: 20px 0;
        padding-left: 0;
        list-style: none;
    }
    .content-section li {
        padding: 10px 0 10px 30px;
        position: relative;
        color: #555;
    }
    .content-section li:before {
        content: "✓";
        position: absolute;
        left: 0;
        color: #667eea;
        font-weight: bold;
        font-size: 1.2rem;
    }
    .tech-stack {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin: 30px 0;
    }
    .tech-item {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        border: 2px solid #e0e0e0;
    }
    .tech-item strong {
        color: #667eea;
        display: block;
        margin-bottom: 5px;
        font-size: 1.1rem;
    }
    .back-btn {
        display: inline-block;
        padding: 12px 30px;
        background: #667eea;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .back-btn:hover {
        background: #5568d3;
        transform: translateY(-2px);
    }
</style>
<?php $this->endSection(); ?>

<div class="about-hero">
    <h2>Про CMS4Blog</h2>
    <p>Легка та потужна система управління контентом</p>
</div>

<div class="content-section">
    <h3>📖 Що таке CMS4Blog?</h3>
    <p>
        CMS4Blog — це сучасна система управління контентом (CMS), створена спеціально для блогінгу. 
        Вона поєднує в собі простоту використання, високу продуктивність та надійну безпеку.
    </p>
    <p>
        Система розроблена на чистому PHP 8.x без використання важких фреймворків, що робить її 
        надзвичайно швидкою та легко зрозумілою для розробників будь-якого рівня.
    </p>
</div>

<div class="content-section">
    <h3>✨ Основні можливості</h3>
    <ul>
        <li>MVC архітектура для чіткої організації коду</li>
        <li>DI Container для управління залежностями</li>
        <li>Гнучкий роутинг з підтримкою middleware</li>
        <li>Система шаблонів з автоматичним екрануванням</li>
        <li>CSRF захист для всіх форм</li>
        <li>Безпечні сесії з автоматичною ротацією</li>
        <li>PDO для роботи з базою даних</li>
        <li>Система міграцій для версіонування БД</li>
        <li>Обробка помилок та логування</li>
        <li>Конфігурація через .env файли</li>
    </ul>
</div>

<div class="content-section">
    <h3>🔧 Технологічний стек</h3>
    <div class="tech-stack">
        <div class="tech-item">
            <strong>Backend</strong>
            PHP 8.x
        </div>
        <div class="tech-item">
            <strong>Database</strong>
            MySQL 5.7+
        </div>
        <div class="tech-item">
            <strong>Web Server</strong>
            Apache / Nginx
        </div>
        <div class="tech-item">
            <strong>Architecture</strong>
            MVC Pattern
        </div>
        <div class="tech-item">
            <strong>Security</strong>
            CSRF, XSS Protection
        </div>
        <div class="tech-item">
            <strong>License</strong>
            MIT Open Source
        </div>
    </div>
</div>

<div class="content-section">
    <h3>🎯 Для кого ця CMS?</h3>
    <p>CMS4Blog ідеально підходить для:</p>
    <ul>
        <li>Особистих блогів та онлайн-щоденників</li>
        <li>Корпоративних блогів та новинних сайтів</li>
        <li>Портфоліо та персональних сайтів</li>
        <li>Навчальних проектів та стартапів</li>
        <li>Розробників, які хочуть мати повний контроль над кодом</li>
    </ul>
</div>

<div class="content-section">
    <h3>🚀 Етапи розробки</h3>
    <p><strong>✅ Етап 1 — Архітектурний каркас</strong></p>
    <ul>
        <li>PSR-4 Autoloader</li>
        <li>DI Container</li>
        <li>Router з middleware</li>
        <li>MVC структура</li>
        <li>View система</li>
        <li>Security (CSRF, Sessions)</li>
    </ul>
    
    <p style="margin-top: 30px;"><strong>✅ Етап 2 — Інфраструктура</strong></p>
    <ul>
        <li>Database клас (PDO)</li>
        <li>Migration система</li>
        <li>Install wizard</li>
        <li>Environment config</li>
        <li>Error handling</li>
    </ul>
    
    <p style="margin-top: 30px;"><strong>🚧 Етап 3 — Модуль Blog (В розробці)</strong></p>
    <ul>
        <li>Управління постами</li>
        <li>Категорії та теги</li>
        <li>Коментарі</li>
        <li>Медіа-бібліотека</li>
        <li>Адміністративна панель</li>
    </ul>
</div>

<div style="text-align: center; margin-top: 40px;">
    <a href="/" class="back-btn">← Повернутися на головну</a>
</div>
