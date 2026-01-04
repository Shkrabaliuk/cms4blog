<?php
/**
 * 404 Not Found Template
 */

// Ensure blogSettings are loaded for the header title if needed
if (!isset($blogSettings)) {
    if (!isset($pdo)) {
        $pdo = \App\Config\Database::connect();
    }
    $stmt = $pdo->query("SELECT `key`, `value` FROM settings");
    $blogSettings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $blogSettings[$row['key']] = $row['value'];
    }
}

$pageTitle = "404 — Сторінку не знайдено";
require __DIR__ . '/../partials/header.php';
?>

<div class="error-container">
    <h1 class="error-code">404</h1>
    <p class="error-message">Тут нічого немає.</p>
    <p class="error-joke">Можливо, ця сторінка втекла з екрану, щоб не платити податки.</p>
</div>


<?php require __DIR__ . '/../partials/footer.php'; ?>