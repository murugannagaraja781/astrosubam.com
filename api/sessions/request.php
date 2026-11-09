<?php
// api/sessions/request.php - User initiates consultation request (status='requested')
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/JWT.php';

$input = Response::getJsonInput();
$user = JWT::getAuthUser();

$userId = (int)($input['user_id'] ?? ($user['user_id'] ?? 0));
$astrologerId = (int)($input['astrologer_id'] ?? 1);
$type = in_array($input['type'] ?? '', ['audio', 'video', 'chat']) ? $input['type'] : 'audio';
$userName = trim($input['user_name'] ?? '');
$userPhone = trim($input['user_phone'] ?? '');

if ($userId <= 0) {
    $userId = 2; // Test devotee default
    if (empty($userName)) $userName = 'பக்தர் (User 1)';
    if (empty($userPhone)) $userPhone = '8000000001';
}

$db = getDb();

// 1. Fetch Astrologer
$stmt = $db->prepare("SELECT id, name, phone, audio_rate_min, video_rate_min, chat_rate_min, call_status FROM astrologers WHERE id = ?");
$stmt->execute([$astrologerId]);
$astro = $stmt->fetch();

if (!$astro) {
    // If id not found, fallback to first astrologer
    $fStmt = $db->query("SELECT id, name, phone, audio_rate_min, video_rate_min, chat_rate_min, call_status FROM astrologers LIMIT 1");
    $astro = $fStmt->fetch();
    if ($astro) {
        $astrologerId = (int)$astro['id'];
    } else {
        Response::json(false, 'Astrologer not found.', null, 404);
    }
}

if (($astro['call_status'] ?? '') === 'offline') {
    Response::json(false, "Astrologer {$astro['name']} is currently offline. Please check their schedule.", null, 409);
}

$rate = ($type === 'video') ? (float)$astro['video_rate_min'] : (($type === 'chat') ? (float)$astro['chat_rate_min'] : (float)$astro['audio_rate_min']);
if ($rate <= 0) $rate = 20.0;

// 2. Check User Wallet: Requires at least 5 minutes balance
$minRequired = $rate * 5;

$uStmt = $db->prepare("SELECT id, name, phone, wallet_balance FROM users WHERE id = ?");
$uStmt->execute([$userId]);
$userData = $uStmt->fetch();

$currentBalance = $userData ? (float)$userData['wallet_balance'] : 500.00;
if (empty($userName) && $userData) $userName = $userData['name'] ?? 'Devotee';
if (empty($userPhone) && $userData) $userPhone = $userData['phone'] ?? '8000000001';

if ($currentBalance < $minRequired) {
    Response::json(false, "Insufficient balance! Minimum 5 minutes balance (₹{$minRequired}) is required for this consultation. Current balance: ₹{$currentBalance}", [
        'current_balance'  => $currentBalance,
        'minimum_required' => $minRequired,
        'rate_per_min'     => $rate
    ], 402);
}

// 3. Create Session with status='requested'
$sessionId = "AS_" . time() . "_" . mt_rand(1000, 9999);
$channelName = "astrosubam_" . $sessionId;

// Ensure table exists
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
if ($driver === 'sqlite') {
    $db->exec("CREATE TABLE IF NOT EXISTS `consultation_sessions` (
        `id` INTEGER PRIMARY KEY AUTOINCREMENT,
        `session_id` TEXT UNIQUE NOT NULL,
        `user_id` INTEGER NOT NULL,
        `astrologer_id` INTEGER NOT NULL,
        `type` TEXT NOT NULL,
        `status` TEXT DEFAULT 'requested',
        `agora_channel` TEXT NOT NULL,
        `start_time` DATETIME NULL,
        `end_time` DATETIME NULL,
        `duration_seconds` INTEGER DEFAULT 0,
        `rate_per_min` REAL NOT NULL,
        `total_deducted` REAL DEFAULT 0.00,
        `astrologer_share` REAL DEFAULT 0.00,
        `rating` INTEGER DEFAULT NULL,
        `review` TEXT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
} else {
    $db->exec("CREATE TABLE IF NOT EXISTS `consultation_sessions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `session_id` VARCHAR(64) UNIQUE NOT NULL,
        `user_id` INT NOT NULL,
        `astrologer_id` INT NOT NULL,
        `type` ENUM('audio', 'video', 'chat') NOT NULL,
        `status` ENUM('requested', 'active', 'completed', 'cancelled') DEFAULT 'requested',
        `agora_channel` VARCHAR(100) NOT NULL,
        `start_time` DATETIME NULL,
        `end_time` DATETIME NULL,
        `duration_seconds` INT DEFAULT 0,
        `rate_per_min` DECIMAL(8, 2) NOT NULL,
        `total_deducted` DECIMAL(10, 2) DEFAULT 0.00,
        `astrologer_share` DECIMAL(10, 2) DEFAULT 0.00,
        `rating` INT DEFAULT NULL,
        `review` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (`session_id`),
        INDEX (`astrologer_id`),
        INDEX (`status`)
    ) ENGINE=InnoDB");
}

$nowUtc = gmdate('Y-m-d H:i:s');
$ins = $db->prepare("INSERT INTO consultation_sessions 
    (session_id, user_id, astrologer_id, type, status, agora_channel, rate_per_min, created_at) 
    VALUES (?, ?, ?, ?, 'requested', ?, ?, ?)");
$ins->execute([$sessionId, $userId, $astrologerId, $type, $channelName, $rate, $nowUtc]);

Response::json(true, "Consultation request placed! Ringing astrologer...", [
    'session_id'          => $sessionId,
    'channel_name'        => $channelName,
    'status'              => 'requested',
    'type'                => $type,
    'rate_per_min'        => $rate,
    'user_id'             => $userId,
    'user_name'           => $userName,
    'user_phone'          => $userPhone,
    'astrologer_id'       => $astrologerId,
    'astrologer_name'     => $astro['name'],
    'timeout_seconds'     => 30
], 201);

