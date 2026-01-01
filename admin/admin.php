<?php
// Редирект на login.php або settings.php
session_start();

if (!file_exists('../config.php')) {
    header("Location: ../install/install.php");
    exit;
}

require '../includes/db.php';
require '../includes/functions.php';

if (is_admin()) {
    header("Location: settings.php");
    exit;
} else {
    header("Location: login.php");
    exit;
}

