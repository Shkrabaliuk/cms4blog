<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Налаштування бази даних - CMS4Blog</title>
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
            margin-bottom: 10px;
        }
        .content p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            color: #555;
            font-weight: 500;
            margin-bottom: 8px;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
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
        }
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .help-text {
            font-size: 0.85rem;
            color: #999;
            margin-top: 5px;
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
            <div class="step complete">
                <div class="step-number">2</div>
                <div class="step-label">Перевірка</div>
            </div>
            <div class="step active">
                <div class="step-number">3</div>
                <div class="step-label">База даних</div>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-label">Завершення</div>
            </div>
        </div>

        <div class="content">
            <h2>Налаштування бази даних</h2>
            <p>Введіть дані для підключення до MySQL бази даних. База буде створена автоматично.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <strong>Помилка:</strong> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="db_host">Хост бази даних</label>
                    <input type="text" id="db_host" name="db_host" value="<?= htmlspecialchars($config['host']) ?>" required>
                    <div class="help-text">Зазвичай: localhost або 127.0.0.1</div>
                </div>

                <div class="form-group">
                    <label for="db_port">Порт</label>
                    <input type="text" id="db_port" name="db_port" value="<?= htmlspecialchars($config['port']) ?>" required>
                    <div class="help-text">Стандартний порт MySQL: 3306</div>
                </div>

                <div class="form-group">
                    <label for="db_database">Назва бази даних</label>
                    <input type="text" id="db_database" name="db_database" value="<?= htmlspecialchars($config['database']) ?>" required>
                    <div class="help-text">Буде створена автоматично</div>
                </div>

                <div class="form-group">
                    <label for="db_username">Користувач</label>
                    <input type="text" id="db_username" name="db_username" value="<?= htmlspecialchars($config['username']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="db_password">Пароль</label>
                    <input type="password" id="db_password" name="db_password" value="<?= htmlspecialchars($config['password']) ?>">
                    <div class="help-text">Залиште порожнім якщо паролю немає</div>
                </div>

                <button type="submit" class="btn">Перевірити та продовжити</button>
            </form>
        </div>
    </div>
</body>
</html>
