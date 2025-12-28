<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Встановлення CMS4Blog</title>
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
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .logo p {
            color: #666;
            font-size: 1.1rem;
        }
        .content {
            margin: 30px 0;
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
        .features {
            list-style: none;
            margin: 20px 0;
        }
        .features li {
            padding: 10px 0;
            padding-left: 30px;
            position: relative;
            color: #555;
        }
        .features li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #667eea;
            font-weight: bold;
            font-size: 1.2rem;
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
            text-align: center;
            width: 100%;
        }
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
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
            position: relative;
        }
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #999;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
        }
        .step.active .step-number {
            background: #667eea;
            color: white;
        }
        .step-label {
            font-size: 0.9rem;
            color: #999;
        }
        .step.active .step-label {
            color: #667eea;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="logo">
            <h1>CMS4Blog</h1>
            <p>Майстер встановлення</p>
        </div>

        <div class="steps">
            <div class="step active">
                <div class="step-number">1</div>
                <div class="step-label">Привітання</div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-label">Перевірка</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-label">База даних</div>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-label">Завершення</div>
            </div>
        </div>

        <div class="content">
            <h2>Ласкаво просимо до CMS4Blog!</h2>
            <p>Дякуємо, що обрали нашу CMS. Цей майстер допоможе вам швидко налаштувати систему.</p>
            
            <ul class="features">
                <li>Легка та швидка CMS для блогінгу</li>
                <li>Написана на чистому PHP 8.x</li>
                <li>Безпечна архітектура з CSRF захистом</li>
                <li>Проста в розширенні та модифікації</li>
                <li>Підтримка міграцій бази даних</li>
            </ul>

            <p>Натисніть "Продовжити" щоб розпочати встановлення.</p>
        </div>

        <a href="/install?step=requirements" class="btn">Продовжити</a>
    </div>
</body>
</html>
