    </main>

    <footer>
        <div class="flex-between">
            <div>
                Powered by /\ogos — <?= date('Y') ?>
            </div>
            <div class="flex-center">
                <a href="/rss.php" title="RSS Feed" class="inline-flex-link">
                    <i class="fas fa-rss"></i>
                    RSS
                </a>
                <?php if ($isAdmin): ?>
                    <a href="/admin/settings.php" title="Налаштування" class="auth-icon">
                        <i class="fas fa-cog"></i>
                    </a>
                    <a href="/logout.php" title="Вийти" class="auth-icon logout-link">
                        <i class="fas fa-unlock"></i>
                    </a>
                <?php else: ?>
                    <a href="#" id="loginToggle" title="Адміністрування" class="auth-icon">
                        <i class="fas fa-lock"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </footer>
</div>

<!-- Модальне вікно входу -->
<div id="loginModal" class="modal">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <button class="modal-close" id="closeLoginModal">
            <i class="fas fa-times"></i>
        </button>
        
        <p class="modal-subtitle" style="margin-bottom: 24px;">Лише для адміністратора.<br>Якщо ти не він — бувай 👋</p>
        
        <div id="loginError" class="modal-error" style="display: none;">
            <i class="fas fa-exclamation-circle"></i>
            <span id="loginErrorText"></span>
        </div>
        
        <form id="loginForm" class="modal-form">
            <div class="form-group">
                <input 
                    type="password" 
                    id="modal-password" 
                    name="password" 
                    placeholder="Пароль"
                    required
                    autocomplete="current-password"
                    autofocus
                >
            </div>
            
            <button type="submit" class="btn-login">
                <i class="fas fa-unlock"></i>
                Увійти
            </button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="/assets/libs/fotorama/fotorama.js"></script>

<!-- Moment.js для роботи з датами -->
<script src="/assets/libs/momentjs/moment-with-locales.min.js"></script>

<!-- Image Upload (Drag & Drop) -->
<?php if (defined('ENV') && ENV === 'development'): ?>
    <script src="/assets/js/image-upload.js"></script>
<?php else: ?>
    <script src="/assets/minify.php?f=image-upload.js&t=js&v=<?= filemtime(__DIR__ . '/../assets/js/image-upload.js') ?>"></script>
<?php endif; ?>
<script>
    // Встановлюємо українську локаль
    moment.locale('uk');
</script>

<!-- Highlight.js для підсвічування коду -->
<script src="/assets/libs/highlight/highlight.js"></script>
<script>
    // Автоматичне підсвічування всіх блоків коду
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('pre code').forEach(function(block) {
            if (typeof hljs !== 'undefined' && hljs.highlightBlock) {
                hljs.highlightBlock(block);
            }
        });
    });
</script>

<!-- Login Modal -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loginToggle = document.getElementById('loginToggle');
        const loginModal = document.getElementById('loginModal');
        const closeModal = document.getElementById('closeLoginModal');
        const overlay = loginModal?.querySelector('.modal-overlay');
        const loginForm = document.getElementById('loginForm');
        const loginError = document.getElementById('loginError');
        const loginErrorText = document.getElementById('loginErrorText');
        
        if (loginToggle) {
            loginToggle.addEventListener('click', function(e) {
                e.preventDefault();
                loginModal.classList.add('active');
                setTimeout(() => document.getElementById('modal-password').focus(), 300);
            });
        }
        
        if (closeModal) {
            closeModal.addEventListener('click', function() {
                loginModal.classList.remove('active');
                loginError.style.display = 'none';
            });
        }
        
        if (overlay) {
            overlay.addEventListener('click', function() {
                loginModal.classList.remove('active');
                loginError.style.display = 'none';
            });
        }
        
        // Закрити при Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && loginModal?.classList.contains('active')) {
                loginModal.classList.remove('active');
                loginError.style.display = 'none';
            }
        });
        
        // Обробка форми входу
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(loginForm);
                
                fetch('/login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        loginErrorText.textContent = data.error || 'Помилка входу';
                        loginError.style.display = 'flex';
                    }
                })
                .catch(error => {
                    loginErrorText.textContent = 'Помилка з\'єднання';
                    loginError.style.display = 'flex';
                });
            });
        }
    });
</script>

<!-- Search toggle -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('searchToggle');
        const search = document.getElementById('headerSearch');
        const input = document.getElementById('searchInput');
        
        if (toggle && search && input) {
            toggle.addEventListener('click', function() {
                search.classList.toggle('active');
                if (search.classList.contains('active')) {
                    setTimeout(() => input.focus(), 300);
                }
            });
            
            // Закрити при кліку поза пошуком
            document.addEventListener('click', function(e) {
                if (!toggle.contains(e.target) && !search.contains(e.target)) {
                    search.classList.remove('active');
                }
            });
            
            // Закрити при натисканні Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    search.classList.remove('active');
                }
            });
        }
    });
</script>

</body>
</html>
