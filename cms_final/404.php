<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
$pageTitle = "404 — Сторінку не знайдено";
require 'includes/templates/header.php';
?>

<main>
    <div class="container">
        <div class="empty-state" style="padding: 100px 20px;">
            <h1 style="font-size: 72px; margin-bottom: 16px;">404</h1>
            <p>Сторінку не знайдено</p>
            <br>
            <a href="/index.php" class="btn btn-primary">← На головну</a>
        </div>
    </div>
</main>

<?php require 'includes/templates/footer.php'; ?>
