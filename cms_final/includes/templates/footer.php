<div class="footer">
  © <?= htmlspecialchars(get_setting('footer_text', 'Автор блогу')) ?>, <?= date('Y') ?>

  <?php if (!is_admin()): ?>
    <a class="e2-visual-login nu" href="/admin/admin.php" title="Вхід">
      <span class="e2-admin-link e2-svgi">
        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
          <path fill-rule="evenodd" stroke="none" clip-rule="evenodd" d="M11 6h-1V4a4 4 0 0 0-8 0v2H1C0 6 0 7 0 7v7.999C0 15.998 1 16 1 16h10s1 0 1-1V7s0-1-1-1zM8 6H4V4a2 2 0 0 1 4 0v2z"/>
        </svg>
      </span>
    </a>
  <?php endif; ?>

  <div class="engine">
    <?= htmlspecialchars(get_setting('footer_engine', 'Рушій — Егея')) ?>
  </div>
</div>

</div>

</body>
</html>
