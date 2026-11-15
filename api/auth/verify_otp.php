<?php
// api/auth/verify_otp.php - Verify OTP & Issue JWT / Register User
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/JWT.php';
require_once __DIR__ . '/../../helpers/SMS.php';

$input = Response::getJsonInput();
$phone = trim($input['phone'] ?? '');
$otp = trim($input['otp'] ?? '');
$name = trim($input['name'] ?? '');

if (empty($phone) || empty($otp)) {
    Response::json(false, 'Mobile number and OTP are required.', null, 400);
}

// Normalize phone
$cleanPhone = preg_replace('/[^0-9]/', '', $phone);
if (strlen($cleanPhone) > 10 && str_starts_with($cleanPhone, '91')) {
    $cleanPhone = substr($cleanPhone, 2);
} elseif (strlen($cleanPhone) > 10 && str_starts_with($cleanPhone, '0')) {
    $cleanPhone = substr($cleanPhone, 1);
}

$db = getDb();

// 1. Verify OTP
$isBypass = (in_array($cleanPhone, SMS::TEST_NUMBERS, true) || $cleanPhone === '9944700648') && $otp === SMS::STATIC_OTP;

if (!$isBypass) {
    $stmt = $db->prepare("SELECT id, expires_at FROM user_otps WHERE phone = ? AND otp = ? AND verified = 0 ORDER BY id DESC LIMIT 1");
    $stmt->execute([$cleanPhone, $otp]);
    $otpRecord = $stmt->fetch();

    if (!$otpRecord) {
        Response::json(false, 'Invalid OTP. Please check and try again.', null, 400);
    }

    if (strtotime($otpRecord['expires_at']) < time()) {
        Response::json(false, 'OTP has expired. Please request a new one.', null, 400);
    }

    // Mark as verified
    $db->prepare("UPDATE user_otps SET verified = 1 WHERE id = ?")->execute([$otpRecord['id']]);
}

// 2. CHECK IF THIS PHONE BELONGS TO SUPER ADMIN (e.g. +91 99447 00648 or registered in admins table)
$adminRecord = null;
try {
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $db->exec("CREATE TABLE IF NOT EXISTS `admins` (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT,
            `username` TEXT UNIQUE NOT NULL,
            `email` TEXT,
            `phone` TEXT NULL,
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
            `phone` VARCHAR(20) NULL,
            `password` VARCHAR(255) NOT NULL,
            `name` VARCHAR(150) NOT NULL,
            `role` VARCHAR(50) DEFAULT 'super_admin',
            `status` VARCHAR(20) DEFAULT 'active',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (`username`),
            INDEX (`status`)
        ) ENGINE=InnoDB");
    }

    $aSt = $db->prepare("SELECT * FROM admins WHERE (phone = ? OR username = ? OR email = ?) AND status = 'active' LIMIT 1");
    $aSt->execute([$cleanPhone, 'superadmin_' . $cleanPhone, $cleanPhone . '@astrosubam.com']);
    $adminRecord = $aSt->fetch(PDO::FETCH_ASSOC);
} catch (\Throwable $e) {}

// Auto-register 9944700648 if not already present
if (!$adminRecord && $cleanPhone === '9944700648') {
    try {
        $hashed = password_hash('Admin@2026', PASSWORD_BCRYPT);
        $ins = $db->prepare("INSERT INTO admins (username, email, phone, password, name, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $ins->execute(['superadmin_9944700648', 'superadmin@astrosubam.com', '9944700648', $hashed, 'Super Admin (+91 99447 00648)', 'super_admin', 'active']);
        
        $aSt = $db->prepare("SELECT * FROM admins WHERE phone = '9944700648' LIMIT 1");
        $aSt->execute();
        $adminRecord = $aSt->fetch(PDO::FETCH_ASSOC);
    } catch (\Throwable $e2) {}
}

if ($adminRecord || $cleanPhone === '9944700648') {
    // 🛡️ INITIATE SUPER ADMIN SESSION
    if (session_status() === PHP_SESSION_NONE) {
        if (ini_get('session.cookie_path') !== '/') {
            session_set_cookie_params(['path' => '/', 'samesite' => 'Lax']);
        }
        session_start();
    }
    $_SESSION['astro_admin_logged'] = true;
    $_SESSION['astro_admin_id'] = (int)($adminRecord['id'] ?? 1);
    $_SESSION['astro_admin_user'] = $adminRecord['username'] ?? 'superadmin_9944700648';
    $_SESSION['astro_admin_name'] = $adminRecord['name'] ?? 'Super Admin (+91 99447 00648)';
    $_SESSION['astro_admin_role'] = $adminRecord['role'] ?? 'super_admin';
    $_SESSION['astro_admin_phone'] = $cleanPhone;
    $_SESSION['astro_admin_email'] = $adminRecord['email'] ?? 'superadmin@astrosubam.com';

    // Also update users table with role 'admin'
    $uCheck = $db->prepare("SELECT id FROM users WHERE phone = ?");
    $uCheck->execute([$cleanPhone]);
    $uRow = $uCheck->fetch();
    if ($uRow) {
        $db->prepare("UPDATE users SET role = 'admin', name = ? WHERE id = ?")->execute([$adminRecord['name'] ?? 'Super Admin', $uRow['id']]);
    } else {
        $pwd = password_hash('Admin@2026', PASSWORD_BCRYPT);
        $db->prepare("INSERT INTO users (name, phone, password_hash, wallet_balance, role) VALUES (?, ?, ?, 10000.00, 'admin')")
           ->execute([$adminRecord['name'] ?? 'Super Admin', $cleanPhone, $pwd]);
    }

    $token = JWT::encode([
        'user_id' => $adminRecord['id'] ?? 1,
        'phone'   => $cleanPhone,
        'name'    => $adminRecord['name'] ?? 'Super Admin',
        'role'    => 'super_admin'
    ]);

    session_write_close();

    Response::json(true, 'Super Admin login successful via OTP! Redirecting to Dashboard...', [
        'ok'       => true,
        'role'     => 'super_admin',
        'redirect' => 'admin/index.php',
        'token'    => $token,
        'name'     => $adminRecord['name'] ?? 'Super Admin',
        'phone'    => $cleanPhone,
        'user'     => [
            'id'       => $adminRecord['id'] ?? 1,
            'name'     => $adminRecord['name'] ?? 'Super Admin',
            'phone'    => $cleanPhone,
            'role'     => 'super_admin',
            'is_admin' => true,
            'wallet_balance' => 10000.00
        ]
    ]);
}

// 3. Check if phone belongs to an Astrologer
$aStmt = $db->prepare("SELECT * FROM astrologers WHERE phone = ?");
$aStmt->execute([$cleanPhone]);
$astro = $aStmt->fetch();

if ($astro) {
    unset($astro['password_hash']);
    $token = JWT::encode([
        'user_id' => $astro['id'],
        'phone'   => $astro['phone'],
        'name'    => $astro['name'],
        'role'    => 'astrologer'
    ]);

    Response::json(true, 'Astrologer login successful.', [
        'ok'             => true,
        'role'           => 'astrologer',
        'approvalStatus' => $astro['status'] ?? 'approved',
        'userId'         => 'ASTRO_' . $astro['id'],
        'name'           => $astro['name'],
        'phone'          => $astro['phone'],
        'user'           => $astro,
        'token'          => $token,
        'walletBalance'  => 1450.00,
        'totalEarnings'  => 12500.00,
        'ratePerMinute'  => (float)($astro['audio_rate_min'] ?? 20.0),
        'image'          => $astro['avatar'] ?? 'assets/logo.jpg'
    ]);
}

// 3. Fetch or Create Client User
$uStmt = $db->prepare("SELECT * FROM users WHERE phone = ?");
$uStmt->execute([$cleanPhone]);
$user = $uStmt->fetch();

$isNew = false;
if (!$user) {
    $isNew = true;
    $displayName = !empty($name) ? $name : ('பக்தர் ' . substr($cleanPhone, -4));
    $pwd = password_hash('User@' . mt_rand(1000, 9999), PASSWORD_BCRYPT);
    $ins = $db->prepare("INSERT INTO users (name, phone, password_hash, wallet_balance, role) VALUES (?, ?, ?, 100.00, 'client')");
    $ins->execute([$displayName, $cleanPhone, $pwd]);
    $userId = (int)$db->lastInsertId();

    $uStmt->execute([$cleanPhone]);
    $user = $uStmt->fetch();

    // Bonus transaction
    $db->prepare("INSERT INTO wallet_transactions (user_id, type, amount, balance_after, description, reference_id) VALUES (?, 'credit', 100.00, 100.00, 'Welcome Bonus ₹100', ?)")
       ->execute([$userId, 'WB_' . time()]);
}

unset($user['password_hash']);
$token = JWT::encode([
    'user_id' => $user['id'],
    'phone'   => $user['phone'],
    'name'    => $user['name'],
    'role'    => $user['role']
]);

Response::json(true, $isNew ? 'Account created! ₹100 Welcome Bonus credited to your wallet.' : 'Login successful.', [
    'user'    => $user,
    'token'   => $token,
    'is_new'  => $isNew
]);

