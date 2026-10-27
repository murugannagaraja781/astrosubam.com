<?php
// api/sessions/initiate.php - Start Agora Consultation & Verify Minimum Balance
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/JWT.php';
require_once __DIR__ . '/../../helpers/AgoraToken.php';

$user = JWT::getAuthUser();
$input = Response::getJsonInput();

$userId = $user['user_id'] ?? (int)($input['user_id'] ?? 0);
$astrologerId = (int)($input['astrologer_id'] ?? 0);
$type = in_array($input['type'] ?? '', ['audio', 'video', 'chat']) ? $input['type'] : 'audio';

if ($userId <= 0 || $astrologerId <= 0) {
    Response::json(false, 'User ID and Astrologer ID are required.', null, 400);
}

$db = getDb();

// 1. Check Astrologer
$stmt = $db->prepare("SELECT id, name, audio_rate_min, video_rate_min, chat_rate_min, call_status FROM astrologers WHERE id = ?");
$stmt->execute([$astrologerId]);
$astro = $stmt->fetch();

if (!$astro) {
    Response::json(false, 'Astrologer not found.', null, 404);
}

if ($astro['call_status'] === 'busy') {
    Response::json(false, 'Astrologer is currently busy on another consultation.', null, 409);
}

$rate = ($type === 'video') ? (float)$astro['video_rate_min'] : (($type === 'chat') ? (float)$astro['chat_rate_min'] : (float)$astro['audio_rate_min']);

// 2. Check User Wallet: Requires at least 5 minutes balance
$minMinutes = 5;
$minRequired = $rate * $minMinutes;

$uStmt = $db->prepare("SELECT wallet_balance FROM users WHERE id = ?");
$uStmt->execute([$userId]);
$userData = $uStmt->fetch();

if (!$userData || (float)$userData['wallet_balance'] < $minRequired) {
    $current = $userData ? (float)$userData['wallet_balance'] : 0.00;
    Response::json(false, "Insufficient balance! Minimum ₹{$minRequired} (for 5 mins @ ₹{$rate}/min) is required. Current balance: ₹{$current}", [
        'current_balance'  => $current,
        'minimum_required' => $minRequired,
        'rate_per_min'     => $rate
    ], 402);
}

// 3. Create Session
$sessionId = "AS_" . time() . "_" . mt_rand(1000, 9999);
$channelName = "astrosubam_" . $sessionId;
$agoraToken = AgoraToken::generateRtcToken($channelName, $userId);

$ins = $db->prepare("INSERT INTO consultation_sessions 
    (session_id, user_id, astrologer_id, type, status, agora_channel, start_time, rate_per_min) 
    VALUES (?, ?, ?, ?, 'active', ?, NOW(), ?)");
$ins->execute([$sessionId, $userId, $astrologerId, $type, $channelName, $rate]);

// Mark Astrologer busy
$db->prepare("UPDATE astrologers SET call_status = 'busy' WHERE id = ?")->execute([$astrologerId]);

$maxAvailableMinutes = floor((float)$userData['wallet_balance'] / $rate);

Response::json(true, 'Consultation session initiated successfully.', [
    'session_id'            => $sessionId,
    'channel_name'          => $channelName,
    'agora_token'           => $agoraToken,
    'type'                  => $type,
    'rate_per_min'          => $rate,
    'user_wallet_balance'   => (float)$userData['wallet_balance'],
    'max_duration_seconds'  => (int)($maxAvailableMinutes * 60),
    'astrologer'            => [
        'id'   => $astro['id'],
        'name' => $astro['name']
    ]
]);
