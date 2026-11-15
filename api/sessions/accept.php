<?php
// api/sessions/accept.php - Astrologer accepts consultation request (status becomes 'ringing_user' & sends FCM)
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/AgoraToken.php';
require_once __DIR__ . '/../../helpers/Settings.php';
require_once __DIR__ . '/../../helpers/FCM.php';

$input = Response::getJsonInput();
$sessionId = trim($input['session_id'] ?? '');
$astroId = (int)($input['astrologer_id'] ?? 0);

if (empty($sessionId)) {
    Response::json(false, 'session_id is required.', null, 400);
}

$db = getDb();

$stmt = $db->prepare("SELECT s.*, u.name as user_name, u.phone as user_phone, u.fcm_token as user_fcm_token,
                             a.name as astrologer_name, a.avatar as astrologer_avatar
                      FROM consultation_sessions s
                      LEFT JOIN users u ON s.user_id = u.id
                      LEFT JOIN astrologers a ON s.astrologer_id = a.id
                      WHERE s.session_id = ?");
$stmt->execute([$sessionId]);
$session = $stmt->fetch();

if (!$session) {
    Response::json(false, 'Session not found.', null, 404);
}

if ($session['status'] === 'cancelled' || $session['status'] === 'completed') {
    Response::json(false, 'Session has already ended or expired.', ['status' => $session['status']], 409);
}

// Idempotency guard: prevent duplicate acceptance and duplicate FCM triggers
if ($session['status'] === 'ringing_user' || $session['status'] === 'active') {
    $channelName = $session['agora_channel'];
    $astrologerId = $astroId > 0 ? $astroId : (int)$session['astrologer_id'];
    $astroUid = AgoraToken::getAstroUid($astrologerId);
    $userUid = AgoraToken::getUserUid((int)$session['user_id']);
    $astroToken = !empty($session['agora_astro_token']) ? $session['agora_astro_token'] : AgoraToken::generateAstroToken($channelName, $astrologerId);
    Response::json(true, 'Consultation already accepted and ringing user.', [
        'session_id'          => $sessionId,
        'status'              => $session['status'],
        'channel_name'        => $channelName,
        'agora_app_id'        => Settings::get('agora_app_id', 'f5df168c9c7849ef892cd39843d1ba42'),
        'agora_astro_token'   => $astroToken,
        'agora_astro_uid'     => $astroUid,
        'agora_user_uid'      => $userUid,
        'type'                => $session['type'],
        'rate_per_min'        => (float)$session['rate_per_min'],
        'user_id'             => (int)$session['user_id'],
        'user_name'           => $session['user_name'] ?? 'Devotee',
        'user_phone'          => $session['user_phone'] ?? '',
        'fcm'                 => ['skipped_duplicate' => true]
    ]);
    exit;
}

$channelName = $session['agora_channel'];
$astrologerId = $astroId > 0 ? $astroId : (int)$session['astrologer_id'];
$astroUid = AgoraToken::getAstroUid($astrologerId);
$userUid = AgoraToken::getUserUid((int)$session['user_id']);

// 1. Generate Agora RTC Tokens for both Astrologer and Devotee ahead of time
$astroToken = AgoraToken::generateAstroToken($channelName, $astrologerId);
$userToken = AgoraToken::generateUserToken($channelName, (int)$session['user_id']);

// 2. Mark session status directly as 'active' (instant room connection)
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
if ($driver === 'sqlite') {
    $db->prepare("UPDATE consultation_sessions 
                  SET status = 'active', start_time = datetime('now'), user_accepted_at = datetime('now'),
                      agora_astro_token = ?, agora_user_token = ? 
                  WHERE id = ?")
       ->execute([$astroToken, $userToken, $session['id']]);
} else {
    $db->prepare("UPDATE consultation_sessions 
                  SET status = 'active', start_time = NOW(), user_accepted_at = NOW(),
                      agora_astro_token = ?, agora_user_token = ? 
                  WHERE id = ?")
       ->execute([$astroToken, $userToken, $session['id']]);
}

// 3. Mark astrologer busy in DB
$db->prepare("UPDATE astrologers SET call_status = 'busy' WHERE id = ?")->execute([$astrologerId]);

// 4. Dispatch FCM notification in background if token available
$fcmResult = FCM::sendIncomingCallToUser((int)$session['user_id'], [
    'session_id'        => $sessionId,
    'channel_name'      => $channelName,
    'astrologer_id'     => $astrologerId,
    'astrologer_name'   => $session['astrologer_name'] ?? 'Guruji S. Sundaramurthy',
    'astrologer_avatar' => $session['astrologer_avatar'] ?? 'assets/logo.jpg',
    'type'              => $session['type'],
    'rate_per_min'      => $session['rate_per_min']
]);

Response::json(true, 'Consultation accepted! Room connected successfully.', [
    'session_id'          => $sessionId,
    'status'              => 'active',
    'channel_name'        => $channelName,
    'agora_app_id'        => Settings::get('agora_app_id', 'f5df168c9c7849ef892cd39843d1ba42'),
    'agora_astro_token'   => $astroToken,
    'agora_user_token'    => $userToken,
    'agora_astro_uid'     => $astroUid,
    'agora_user_uid'      => $userUid,
    'type'                => $session['type'],
    'rate_per_min'        => (float)$session['rate_per_min'],
    'user_id'             => (int)$session['user_id'],
    'user_name'           => $session['user_name'] ?? 'Devotee',
    'user_phone'          => $session['user_phone'] ?? '',
    'fcm'                 => $fcmResult
]);
