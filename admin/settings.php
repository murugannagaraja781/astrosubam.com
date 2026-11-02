<?php
// admin/settings.php - Super Admin Visual Settings Redirect
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['astro_admin_logged'])) {
    header('Location: index.php');
    exit;
}

header('Location: index.php?tab=agora');
exit;
