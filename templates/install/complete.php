<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Встановлення завершено - CMS4Blog</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .install-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: #667eea;
            font-size: 2rem;
        }
        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 0 20px;
        }
        .step {
            text-align: center;
            flex: 1;
        }
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #4caf50;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
        }
        .step-label {
            font-size: 0.9rem;
            color: #999;
        }
        .content {
            text-align: center;
        }
        .success-icon {
            width: 100px;
            height: 100px;
            background: #4caf50;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 3rem;
            color: white;
        }
        .error-icon {
            width: 100px;
            height: 100px;
            background: #f44336;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 3rem;
            color: white;
        }
        .content h2 {
            color: #333;
            margin-bottom: 15px;
        }
        .content p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
        }
        .alert-danger {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #f44336;
        }
        .credentials {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
        }
        .credentials h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        .credentials p {
            margin: 10px 0;
            color: #555;
        }
        .credentials strong {
            color: #667eea;
        }
        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 20px;
        }
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="logo">
            <h1>CMS4Blog</h1>
        </div>

        <div class="steps">
            <div class="step">
                <div class="step-number">✓</div>
                <div class="step-label">Привітання</div>
            </div>
            <div class="step">
                <div class="step-number">✓</div>
                <div class="step-label">Перевірка</div>
            </div>
            <div class="step">
                <div class="step-number">✓</div>
                <div class="step-label">База даних</div>
            </div>
            <div class="step">
                <div class="step-number">✓</div>
                <div class="step-label">Завершення</div>
            </div>
        </div>

        <div class="content">
            <?php if ($success): ?>
                <div class="success-icon">✓</div>
                <h2>Встановлення успішно завершено!</h2>
                <p>CMS4Blog готова до використання. Всі таблиці бази даних створені, міграції виконані.</p>

                <div class="credentials">
                    <h3>📋 Дані для входу (за замовчуванням)</h3>
                    <p><strong>Адміністратор:</strong></p>
                    <p>Логін: <strong>admin</strong></p>
                    <p>Email: <strong>admin@cms4blog.local</strong></p>
                    <p>Пароль: <strong>admin123</strong></p>
                </div>

                <div class="warning">
                    <strong>⚠️ Важливо:</strong> Обов'язково змініть пароль адміністратора після першого входу!
                </div>

                <a href="/" class="btn">Перейти на сайт</a>
            <?php else: ?>
                <div class="error-icon">✗</div>
                <h2>Помилка встановлення</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <strong>Деталі помилки:</strong><br>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <p>Під час встановлення виникла помилка. Перевірте налаштування бази даних та спробуйте ще раз.</p>
                
                <a href="/install?step=database" class="btn">Повернутися назад</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
