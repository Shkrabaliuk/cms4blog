<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Перевірка вимог - CMS4Blog</title>
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
        .step.complete .step-number {
            background: #4caf50;
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
        .content h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .requirements {
            margin: 20px 0;
        }
        .requirement {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .requirement:last-child {
            border-bottom: none;
        }
        .requirement-name {
            color: #555;
            font-weight: 500;
        }
        .requirement-status {
            font-weight: bold;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
        .requirement-status.pass {
            background: #e8f5e9;
            color: #4caf50;
        }
        .requirement-status.fail {
            background: #ffebee;
            color: #f44336;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #4caf50;
        }
        .alert-danger {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #f44336;
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
            margin-top: 20px;
        }
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="logo">
            <h1>CMS4Blog</h1>
        </div>

        <div class="steps">
            <div class="step complete">
                <div class="step-number">1</div>
                <div class="step-label">Привітання</div>
            </div>
            <div class="step active">
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
            <h2>Перевірка системних вимог</h2>

            <?php if ($allPassed): ?>
                <div class="alert alert-success">
                    ✓ Всі вимоги виконані! Ви можете продовжити встановлення.
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    ✗ Деякі вимоги не виконані. Будь ласка, виправте їх перед продовженням.
                </div>
            <?php endif; ?>

            <div class="requirements">
                <?php foreach ($requirements as $name => $passed): ?>
                    <div class="requirement">
                        <span class="requirement-name"><?= htmlspecialchars($name) ?></span>
                        <span class="requirement-status <?= $passed ? 'pass' : 'fail' ?>">
                            <?= $passed ? '✓ Так' : '✗ Ні' ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <a href="/install?step=database" class="btn" <?= !$allPassed ? 'style="pointer-events: none; opacity: 0.5;"' : '' ?>>
                Продовжити
            </a>
        </div>
    </div>
</body>
</html>
