<?php require __DIR__ . '/../partials/header.php'; ?>

<?php
if (is_string($childView) && file_exists(__DIR__ . '/../' . $childView)) {
    // Якщо $childView - шлях до файлу
    include __DIR__ . '/../' . $childView;
} else {
    // Якщо $childView - HTML рядок
    echo $childView;
}
?>

<?php require __DIR__ . '/../partials/footer.php'; ?>