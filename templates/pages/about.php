<div class="about-container">
    <h1>
        <?= htmlspecialchars($blogSettings['site_title'] ?? '/\\ogos') ?>
    </h1>

    <div class="about-content">
        <p>
            Це особистий блог про розробку, дизайн та інші цікаві теми.
        </p>

        <p class="about-signature">
            —
            <?= htmlspecialchars($blogSettings['blog_author'] ?? 'Автор') ?>
        </p>
    </div>
</div>