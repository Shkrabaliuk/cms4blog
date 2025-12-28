<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Встановлення - CMS4Blog</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica', 'Arial', sans-serif;
            background: #fafafa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 480px;
            width: 100%;
        }
        
        .logo {
            width: 64px;
            height: 64px;
            margin: 0 0 24px 0;
        }
        
        .logo svg {
            width: 100%;
            height: 100%;
        }
        
        h1 {
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 16px;
            color: #1a1a1a;
        }
        
        .subtitle {
            font-size: 14px;
            color: #737373;
            margin-bottom: 32px;
            line-height: 1.5;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        label {
            display: block;
            font-size: 14px;
            color: #737373;
            margin-bottom: 8px;
            font-weight: 400;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            font-size: 14px;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            background: #f5f5f5;
            transition: all 0.2s ease;
            font-family: inherit;
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #a3a3a3;
            background: #fff;
        }
        
        input[type="text"]::placeholder,
        input[type="password"]::placeholder {
            color: #a3a3a3;
        }
        
        .hint {
            font-size: 12px;
            color: #a3a3a3;
            margin-top: 6px;
            line-height: 1.4;
        }
        
        .submit-group {
            margin-top: 40px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        button {
            padding: 14px 32px;
            font-size: 15px;
            font-weight: 500;
            color: white;
            background: #ea580c;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
        }
        
        button:hover {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(234, 88, 12, 0.3);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        button:disabled {
            background: #d1d5db;
            cursor: not-allowed;
            transform: none;
        }
        
        .keyboard-hint {
            font-size: 12px;
            color: #a3a3a3;
        }
        
        .error-message {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 24px;
            display: none;
        }
        
        .error-message.show {
            display: block;
        }
        
        .success-message {
            background: #dcfce7;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 16px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 24px;
            display: none;
        }
        
        .success-message.show {
            display: block;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 40px;
        }
        
        .loading.show {
            display: block;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #f5f5f5;
            border-top-color: #ea580c;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 16px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        @media (max-width: 640px) {
            h1 {
                font-size: 28px;
            }
            
            .container {
                padding: 0 16px;
            }
            
            .submit-group {
                flex-direction: column;
                align-items: flex-start;
            }
            
            button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($alreadyInstalled) && $alreadyInstalled): ?>
            <!-- Вже встановлено -->
            <div class="logo">
                <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="50" cy="50" r="48" fill="#FDB022" stroke="#F59E0B" stroke-width="2"/>
                    <path d="M50 10 L50 50 L75 65 M50 50 L25 65 M50 50 L35 25 M50 50 L65 25" stroke="white" stroke-width="3" stroke-linecap="round"/>
                    <circle cx="50" cy="50" r="6" fill="white"/>
                </svg>
            </div>
            <h1>Вже встановлено!</h1>
            <div class="success-message show">
                <strong>✓ Система вже встановлена</strong><br>
                CMS4Blog готова до використання!
            </div>
            <a href="/" style="display: inline-block; padding: 14px 32px; background: #ea580c; color: white; text-decoration: none; border-radius: 8px; font-weight: 500; margin-top: 20px;">
                Перейти на головну
            </a>
        <?php else: ?>
        <!-- Форма встановлення -->
        <div class="logo">
            <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="48" fill="#FDB022" stroke="#F59E0B" stroke-width="2"/>
                <path d="M50 10 L50 50 L75 65 M50 50 L25 65 M50 50 L35 25 M50 50 L65 25" stroke="white" stroke-width="3" stroke-linecap="round"/>
                <circle cx="50" cy="50" r="6" fill="white"/>
            </svg>
        </div>
        
        <!-- Заголовок -->
        <h1>Встановлення</h1>
        <p class="subtitle">Database parameters that your hosting provider has given you:</p>
        
        <!-- Повідомлення про помилки -->
        <div class="error-message" id="errorMessage"></div>
        
        <!-- Повідомлення про успіх -->
        <div class="success-message" id="successMessage">
            <strong>✓ Успішно встановлено!</strong><br>
            Переадресація на головну сторінку...
        </div>
        
        <!-- Форма -->
        <form id="installForm">
            <!-- Server -->
            <div class="form-group">
                <label for="server">Server</label>
                <input type="text" id="server" name="server" value="localhost" required>
            </div>
            
            <!-- User name and password -->
            <div class="form-group">
                <label for="username">User name and password</label>
                <input type="text" id="username" name="username" value="root" required>
                <input type="password" id="password" name="password" placeholder="" style="margin-top: 8px;">
            </div>
            
            <!-- Database name -->
            <div class="form-group">
                <label for="database">Database name</label>
                <input type="text" id="database" name="database" placeholder="" required>
                <div class="hint">Ask your hosting provider how to create database, if necessary</div>
            </div>
            
            <!-- Admin password -->
            <div class="form-group">
                <label for="admin_password">Password you'd like to use to access your blog:</label>
                <input type="password" id="admin_password" name="admin_password" placeholder="" required>
            </div>
            
            <!-- Submit -->
            <div class="submit-group">
                <button type="submit" id="submitBtn">Start blogging</button>
                <span class="keyboard-hint">Ctrl + Enter</span>
            </div>
        </form>
        
        <!-- Loading -->
        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p style="color: #737373;">Встановлення системи...</p>
        </div>
    </div>
    
    <script>
        const form = document.getElementById('installForm');
        const submitBtn = document.getElementById('submitBtn');
        const errorMessage = document.getElementById('errorMessage');
        const successMessage = document.getElementById('successMessage');
        const loading = document.getElementById('loading');
        
        // Keyboard shortcut Ctrl + Enter
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                form.requestSubmit();
            }
        });
        
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Приховуємо повідомлення
            errorMessage.classList.remove('show');
            successMessage.classList.remove('show');
            
            // Показуємо loading
            form.style.display = 'none';
            loading.classList.add('show');
            submitBtn.disabled = true;
            
            // Збираємо дані
            const formData = new FormData(form);
            
            try {
                const response = await fetch('/', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                loading.classList.remove('show');
                
                if (result.success) {
                    successMessage.classList.add('show');
                    setTimeout(() => {
                        window.location.href = '/';
                    }, 2000);
                } else {
                    form.style.display = 'block';
                    submitBtn.disabled = false;
                    errorMessage.textContent = result.error || 'Помилка встановлення';
                    errorMessage.classList.add('show');
                }
            } catch (error) {
                loading.classList.remove('show');
                form.style.display = 'block';
                submitBtn.disabled = false;
                errorMessage.textContent = 'Помилка підключення до сервера: ' + error.message;
                errorMessage.classList.add('show');
            }
        });
    </script>
        <?php endif; ?>
    </div>
</body>
</html>
