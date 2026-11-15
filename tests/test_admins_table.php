<?php
// tests/test_admins_table.php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

echo "Testing admins table setup...\n";
$db = getDb();
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
echo "Database driver: {$driver}\n";

if ($driver === 'sqlite') {
    $db->exec("CREATE TABLE IF NOT EXISTS `admins` (
        `id` INTEGER PRIMARY KEY AUTOINCREMENT,
        `username` TEXT UNIQUE NOT NULL,
        `email` TEXT,
        `password` TEXT NOT NULL,
        `name` TEXT NOT NULL,
        `role` TEXT DEFAULT 'super_admin',
        `status` TEXT DEFAULT 'active',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
} else {
    $db->exec("CREATE TABLE IF NOT EXISTS `admins` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(100) UNIQUE NOT NULL,
        `email` VARCHAR(150) NULL,
        `password` VARCHAR(255) NOT NULL,
        `name` VARCHAR(150) NOT NULL,
        `role` VARCHAR(50) DEFAULT 'super_admin',
        `status` VARCHAR(20) DEFAULT 'active',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");
}

// Check if default admin exists
$st = $db->query("SELECT COUNT(*) FROM admins");
$count = (int)$st->fetchColumn();
echo "Existing admins count: {$count}\n";

if ($count === 0) {
    $hashed = password_hash('Admin@2026', PASSWORD_BCRYPT);
    $ins = $db->prepare("INSERT INTO admins (username, email, password, name, role, status) VALUES (?, ?, ?, ?, ?, ?)");
    $ins->execute(['admin', 'admin@astrosubam.com', $hashed, 'Master Super Admin', 'super_admin', 'active']);
    echo "Default master admin inserted!\n";
}

$admins = $db->query("SELECT id, username, email, name, role, status, created_at FROM admins")->fetchAll();
print_r($admins);
echo "✔ Admins table verified successfully!\n";
