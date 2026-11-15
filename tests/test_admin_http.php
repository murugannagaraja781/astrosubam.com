<?php
// tests/test_admin_http.php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Test executing admin/index.php in session-mocked environment
$_SESSION['astro_admin_logged'] = true;
$_SESSION['astro_admin_id'] = 1;
$_SESSION['astro_admin_user'] = 'admin';
$_SESSION['astro_admin_name'] = 'Master Super Admin';
$_SESSION['astro_admin_role'] = 'super_admin';

$_GET['tab'] = 'admins';

ob_start();
require __DIR__ . '/../admin/index.php';
$html = ob_get_clean();

$checks = [
    'நிர்வாகிகள் (Super Admins)' => str_contains($html, 'நிர்வாகிகள் (Super Admins)'),
    '?tab=admins' => str_contains($html, '?tab=admins'),
    'புதிய Super Admin சேர்க்க' => str_contains($html, 'புதிய Super Admin சேர்க்க'),
    'நிர்வாகிகள் பட்டியல்' => str_contains($html, 'நிர்வாகிகள் பட்டியல்'),
    'create_super_admin' => str_contains($html, 'create_super_admin'),
    'resetPasswordModal' => str_contains($html, 'resetPasswordModal'),
];

echo "HTML Render Tests for ?tab=admins:\n";
$allOk = true;
foreach ($checks as $title => $ok) {
    echo ($ok ? "  ✔ [PASS] " : "  ❌ [FAIL] ") . $title . "\n";
    if (!$ok) $allOk = false;
}

if ($allOk) {
    echo "\n🎉 Super Admin Dashboard HTML Rendering is 100% Verified!\n";
    exit(0);
} else {
    echo "\n❌ Some checks failed.\n";
    exit(1);
}
