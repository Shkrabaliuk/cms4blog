</main>

<footer>
    <p>
        © <span><?= htmlspecialchars($blogSettings['blog_author'] ?? 'Автор') ?></span>, <?= date('Y') ?>
    </p>

    <div class="footer-icons">
        <?php if (!\App\Services\Auth::check()): ?>
            <a href="#" id="loginToggle" class="icon-btn" title="Вхід">
                <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
            </a>
        <?php endif; ?>

        <a href="/rss.php" class="icon-btn" title="RSS Feed">
            <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 11a9 9 0 0 1 9 9"></path>
                <path d="M4 4a16 16 0 0 1 16 16"></path>
                <circle cx="5" cy="19" r="1"></circle>
            </svg>
        </a>
    </div>
</footer>

<!-- Login Modal -->

<dialog id="loginDialog">
    <form method="dialog">
        <button class="close-btn">✕</button>
    </form>

    <h3>Вхід для адміністратора</h3>

    <form id="loginForm">
        <label>
            Пароль
            <input type="password" name="password" required autocomplete="current-password" autofocus>
        </label>
        <button type="submit">Увійти</button>
    </form>

    <p id="loginError" class="error-text" hidden></p>
</dialog>

<script src="/assets/js/libs/fotorama/fotorama.js"></script>
<script src="/assets/js/libs/momentjs/moment-with-locales.min.js"></script>

<script>
    // Moment.js locale
    if (typeof moment !== 'undefined') {
        moment.locale('uk');
    }

    // Login Dialog Logic
    const loginDialog = document.getElementById('loginDialog');
    const loginToggle = document.getElementById('loginToggle');
    const loginForm = document.getElementById('loginForm');
    const loginError = document.getElementById('loginError');

    if (loginToggle && loginDialog) {
        loginToggle.addEventListener('click', (e) => {
            e.preventDefault();
            loginDialog.showModal();
        });
    }

    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(loginForm);

            fetch('/api/login.php', {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        loginError.textContent = data.error || 'Помилка входу';
                        loginError.hidden = false;
                    }
                })
                .catch(() => {
                    loginError.textContent = 'Помилка з\'єднання';
                    loginError.hidden = false;
                });
        });
    }
</script>

</body>

</html>