<?php
// tests/test_admins_phone_migration.php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

$db = getDb();
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

// Check if phone column exists in admins
$hasPhone = false;
if ($driver === 'sqlite') {
    $cols = $db->query("PRAGMA table_info(admins)")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        if ($c['name'] === 'phone') $hasPhone = true;
    }
    if (!$hasPhone) {
        $db->exec("ALTER TABLE admins ADD COLUMN phone TEXT NULL");
        echo "Added phone column to SQLite admins table.\n";
    }
} else {
    $cols = $db->query("SHOW COLUMNS FROM admins LIKE 'phone'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE admins ADD COLUMN phone VARCHAR(20) NULL AFTER email");
        echo "Added phone column to MySQL admins table.\n";
    }
}

// Ensure 9944700648 is registered as Super Admin
$st = $db->prepare("SELECT * FROM admins WHERE phone = '9944700648' OR username = 'superadmin_9944700648'");
$st->execute();
$admin = $st->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    $hashed = password_hash('Admin@2026', PASSWORD_BCRYPT);
    $ins = $db->prepare("INSERT INTO admins (username, email, phone, password, name, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $ins->execute(['superadmin_9944700648', 'superadmin@astrosubam.com', '9944700648', $hashed, 'Super Admin (+91 99447 00648)', 'super_admin', 'active']);
    echo "Registered +91 99447 00648 as Super Admin in database!\n";
} else {
    $db->prepare("UPDATE admins SET phone = '9944700648', role = 'super_admin', status = 'active' WHERE id = ?")->execute([$admin['id']]);
    echo "Updated admin #{$admin['id']} with phone 9944700648.\n";
}

// Also ensure users table has role = 'admin' for 9944700648
$uSt = $db->prepare("SELECT * FROM users WHERE phone = '9944700648'");
$uSt->execute();
$u = $uSt->fetch(PDO::FETCH_ASSOC);
if (!$u) {
    $pwd = password_hash('Admin@2026', PASSWORD_BCRYPT);
    $db->prepare("INSERT INTO users (name, phone, password_hash, wallet_balance, role) VALUES (?, ?, ?, 10000.00, 'admin')")
       ->execute(['Super Admin', '9944700648', $pwd]);
    echo "Created user entry for 9944700648 with role admin in users table.\n";
} else {
    $db->prepare("UPDATE users SET role = 'admin', name = 'Super Admin' WHERE id = ?")->execute([$u['id']]);
    echo "Updated user #{$u['id']} with role admin.\n";
}

$fetchFinal = $db->query("SELECT id, username, phone, name, role, status FROM admins WHERE phone = '9944700648'")->fetch(PDO::FETCH_ASSOC);
print_r($fetchFinal);
