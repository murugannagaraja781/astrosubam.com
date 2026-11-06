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
$isBypass = in_array($cleanPhone, SMS::TEST_NUMBERS, true) && $otp === SMS::STATIC_OTP;

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

// 2. Fetch or Create User
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

