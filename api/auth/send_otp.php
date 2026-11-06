<?php
// api/auth/send_otp.php - Request OTP via Ping4SMS
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/SMS.php';

$input = Response::getJsonInput();
$phone = trim($input['phone'] ?? '');

if (empty($phone)) {
    Response::json(false, 'Mobile number is required.', null, 400);
}

// Clean phone number
$cleanPhone = preg_replace('/[^0-9]/', '', $phone);
if (strlen($cleanPhone) > 10 && str_starts_with($cleanPhone, '91')) {
    $cleanPhone = substr($cleanPhone, 2);
} elseif (strlen($cleanPhone) > 10 && str_starts_with($cleanPhone, '0')) {
    $cleanPhone = substr($cleanPhone, 1);
}

if (strlen($cleanPhone) !== 10) {
    Response::json(false, 'Please enter a valid 10-digit mobile number.', null, 400);
}

// Check for test number bypass
$isBypass = in_array($cleanPhone, SMS::TEST_NUMBERS, true);
$otp = $isBypass ? SMS::STATIC_OTP : (string)mt_rand(1000, 9999);

$db = getDb();

// Ensure user_otps table exists
$db->exec("CREATE TABLE IF NOT EXISTS `user_otps` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `phone` VARCHAR(20) NOT NULL,
    `otp` VARCHAR(10) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `verified` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (`phone`)
) ENGINE=InnoDB;");

// Save OTP valid for 10 minutes
$expiresAt = date('Y-m-d H:i:s', time() + 600);
$ins = $db->prepare("INSERT INTO user_otps (phone, otp, expires_at) VALUES (?, ?, ?)");
$ins->execute([$cleanPhone, $otp, $expiresAt]);

// Dispatch SMS
$smsResult = SMS::sendOtp($cleanPhone, $otp);

Response::json(true, $isBypass ? 'Test OTP applied. Use ' . SMS::STATIC_OTP : 'OTP sent successfully to your mobile number.', [
    'phone'     => $cleanPhone,
    'bypass'    => $isBypass,
    'otp'       => $isBypass ? SMS::STATIC_OTP : null, // Only reveal in bypass mode for testers
    'expires_in'=> 600
]);

