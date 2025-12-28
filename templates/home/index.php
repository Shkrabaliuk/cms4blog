<?php $this->startSection('styles'); ?>
<style>
    .hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 80px 20px;
        text-align: center;
        border-radius: 12px;
        margin-bottom: 40px;
    }
    .hero h2 {
        font-size: 2.5rem;
        margin-bottom: 20px;
    }
    .hero p {
        font-size: 1.2rem;
        opacity: 0.9;
    }
    .features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        margin: 40px 0;
    }
    .feature-card {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .feature-icon {
        font-size: 3rem;
        margin-bottom: 15px;
    }
    .feature-card h3 {
        color: #667eea;
        margin-bottom: 10px;
    }
    .feature-card p {
        color: #666;
        line-height: 1.6;
    }
    .stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin: 40px 0;
    }
    .stat-card {
        background: white;
        padding: 30px;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: #667eea;
        margin-bottom: 10px;
    }
    .stat-label {
        color: #666;
        font-size: 1rem;
    }
    .cta-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 60px 30px;
        text-align: center;
        border-radius: 12px;
        margin: 40px 0;
    }
    .cta-section h3 {
        font-size: 2rem;
        margin-bottom: 20px;
    }
    .btn-primary {
        display: inline-block;
        padding: 15px 40px;
        background: white;
        color: #667eea;
        text-decoration: none;
        border-radius: 30px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 20px rgba(255,255,255,0.3);
    }
</style>
<?php $this->endSection(); ?>

<div class="hero">
    <h2>Ласкаво просимо до <?= $siteName ?></h2>
    <p>Легка, швидка та захищена CMS для блогінгу на PHP 8.x + MySQL</p>
</div>

<div class="features">
    <div class="feature-card">
        <div class="feature-icon">⚡</div>
        <h3>Швидкість</h3>
        <p>Оптимізована архітектура для максимальної продуктивності. Мінімальні залежності для швидкої роботи.</p>
    </div>
    
    <div class="feature-card">
        <div class="feature-icon">🔒</div>
        <h3>Безпека</h3>
        <p>CSRF захист, безпечні сесії, XSS захист, підготовлені SQL-запити та захищені заголовки.</p>
    </div>
    
    <div class="feature-card">
        <div class="feature-icon">🎨</div>
        <h3>Гнучкість</h3>
        <p>MVC архітектура, система шаблонів, DI Container та роутинг з middleware підтримкою.</p>
    </div>
    
    <div class="feature-card">
        <div class="feature-icon">🚀</div>
        <h3>Простота</h3>
        <p>Зрозумілий код, чітка структура, легко розширюється та модифікується під ваші потреби.</p>
    </div>
    
    <div class="feature-card">
        <div class="feature-icon">💾</div>
        <h3>База даних</h3>
        <p>PDO для безпечної роботи з MySQL, система міграцій, транзакції та захист від SQL-ін'єкцій.</p>
    </div>
    
    <div class="feature-card">
        <div class="feature-icon">📱</div>
        <h3>Адаптивність</h3>
        <p>Адаптивний дизайн, який відмінно виглядає на всіх пристроях - від мобільних до десктопів.</p>
    </div>
</div>

<div class="stats">
    <div class="stat-card">
        <div class="stat-number">PHP 8.x</div>
        <div class="stat-label">Сучасна версія</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-number">100%</div>
        <div class="stat-label">Open Source</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-number">MIT</div>
        <div class="stat-label">Ліцензія</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-number">0</div>
        <div class="stat-label">Залежностей</div>
    </div>
</div>

<div class="cta-section">
    <h3>Готові почати?</h3>
    <p style="margin-bottom: 30px; font-size: 1.1rem;">Система встановлена та готова до використання</p>
    <a href="/about" class="btn-primary">Дізнатися більше</a>
</div>
