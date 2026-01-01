<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$pageTitle = "404 — Сторінку не знайдено";
require 'includes/templates/header.php';
?>

<div class="empty-state">
    <h1 style="font-size: 72px; color: #ccc;">404</h1>
    <p style="font-size: 20px; margin: 20px 0;">Сторінку не знайдено</p>
    <p><a href="/index.php" class="button"><i class="fa-solid fa-home"></i> На головну</a></p>
</div>

<?php require 'includes/templates/footer.php'; ?>
