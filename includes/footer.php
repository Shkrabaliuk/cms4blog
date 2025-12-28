<footer>
        © <?= date('Y'); ?> Всі права захищені. <br>
        Powered by <a href="#" style="color:inherit">SimpleBlog</a>
    </footer>
</div> <div id="loginModal" class="modal-overlay">
    <div class="modal-box">
        <h3 style="margin-bottom: 20px;">Вхід для автора</h3>
        <form action="login.php" method="POST">
            <input type="password" name="password" class="form-control" placeholder="Пароль..." required>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Увійти</button>
        </form>
        <button onclick="closeLoginModal()" class="btn btn-outline" style="width: 100%; margin-top: 10px; border:none;">Скасувати</button>
    </div>
</div>

<script>
    function openLoginModal() {
        document.getElementById('loginModal').classList.add('open');
        document.querySelector('input[name="password"]').focus();
    }
    function closeLoginModal() {
        document.getElementById('loginModal').classList.remove('open');
    }
    // Закриття по кліку на фон
    document.getElementById('loginModal').addEventListener('click', function(e) {
        if (e.target === this) closeLoginModal();
    });
</script>

</body>
</html>