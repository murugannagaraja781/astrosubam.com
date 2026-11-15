<?php
// tests/test_super_admin_flow.php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

echo "========================================================\n";
echo "🛡️ ASTROSUBAM - SUPER ADMIN CREATION & AUTH WORKFLOW TEST\n";
echo "========================================================\n\n";

$db = getDb();

// 1. Ensure admins table exists
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
echo "1. Checking database connection (Driver: {$driver})...\n";
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
}

$st = $db->query("SELECT COUNT(*) FROM admins");
$count = (int)$st->fetchColumn();
echo "  ✔ Admins count: {$count}\n";

// 2. Test inserting a new super admin
$testUser = 'karthik_super_' . time();
$testPass = 'Karthik@2026';
$testName = 'Karthik Subramanian';
$testEmail = $testUser . '@astrosubam.com';
$hashed = password_hash($testPass, PASSWORD_BCRYPT);

echo "\n2. Creating new Super Admin: {$testName} (@{$testUser})...\n";
$ins = $db->prepare("INSERT INTO admins (username, email, password, name, role, status) VALUES (?, ?, ?, ?, ?, ?)");
$ins->execute([$testUser, $testEmail, $hashed, $testName, 'super_admin', 'active']);
$newId = (int)$db->lastInsertId();
echo "  ✔ Successfully created Admin ID #{$newId}\n";

// 3. Verify Login authentication for the new Super Admin
echo "\n3. Testing Login verification with new credentials...\n";
$st = $db->prepare("SELECT * FROM admins WHERE (LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?)) AND status = 'active' LIMIT 1");
$st->execute([$testUser, $testUser]);
$row = $st->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo "  ❌ FAILED: User not found in database!\n";
    exit(1);
}

if (!password_verify($testPass, $row['password'])) {
    echo "  ❌ FAILED: Password verification failed!\n";
    exit(1);
}
echo "  ✔ Login Success! Name: {$row['name']}, Role: {$row['role']}, Status: {$row['status']}\n";

// 4. Test Toggle Status
echo "\n4. Testing Status Toggle (Active -> Inactive -> Active)...\n";
$db->prepare("UPDATE admins SET status = 'inactive' WHERE id = ?")->execute([$newId]);
$statusAfter = $db->query("SELECT status FROM admins WHERE id = {$newId}")->fetchColumn();
if ($statusAfter !== 'inactive') {
    echo "  ❌ FAILED: Status did not toggle to inactive!\n";
    exit(1);
}
echo "  ✔ Status toggled to: {$statusAfter}\n";

$db->prepare("UPDATE admins SET status = 'active' WHERE id = ?")->execute([$newId]);
$statusBack = $db->query("SELECT status FROM admins WHERE id = {$newId}")->fetchColumn();
echo "  ✔ Status restored to: {$statusBack}\n";

// 5. Test Password Reset
echo "\n5. Testing Password Reset...\n";
$newPassword = 'NewSecret@2026';
$newHashed = password_hash($newPassword, PASSWORD_BCRYPT);
$db->prepare("UPDATE admins SET password = ? WHERE id = ?")->execute([$newHashed, $newId]);

$fetchUpdated = $db->query("SELECT password FROM admins WHERE id = {$newId}")->fetchColumn();
if (!password_verify($newPassword, $fetchUpdated)) {
    echo "  ❌ FAILED: Reset password verification failed!\n";
    exit(1);
}
echo "  ✔ Password reset verified successfully!\n";

// Clean up test admin
$db->prepare("DELETE FROM admins WHERE id = ?")->execute([$newId]);
echo "  ✔ Cleanup: Test admin ID #{$newId} removed cleanly.\n";

echo "\n--------------------------------------------------------\n";
echo "🎉 ALL SUPER ADMIN WORKFLOW TESTS PASSED 100%!\n";
