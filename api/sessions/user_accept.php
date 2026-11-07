<?php
// api/sessions/user_accept.php - Devotee accepts incoming full-screen call (room connects, status='active')
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/AgoraToken.php';

$input = Response::getJsonInput();
$sessionId = trim($input['session_id'] ?? ($_GET['session_id'] ?? ''));
$userId = (int)($input['user_id'] ?? 0);

if (empty($sessionId)) {
    Response::json(false, 'session_id is required.', null, 400);
}

$db = getDb();
$stmt = $db->prepare("SELECT s.*, a.name as astrologer_name, a.avatar as astrologer_avatar, a.audio_rate_min, a.video_rate_min
                      FROM consultation_sessions s
                      LEFT JOIN astrologers a ON s.astrologer_id = a.id
                      WHERE s.session_id = ?");
$stmt->execute([$sessionId]);
$session = $stmt->fetch();

if (!$session) {
    Response::json(false, 'Session not found.', null, 404);
}

if ($session['status'] === 'cancelled' || $session['status'] === 'completed') {
    Response::json(false, 'Call has ended or timed out.', ['status' => $session['status']], 410);
}

$channelName = $session['agora_channel'];
$userId = $userId > 0 ? $userId : (int)$session['user_id'];
$astrologerId = (int)$session['astrologer_id'];

// Generate Agora RTC Token for Devotee
$userToken = AgoraToken::generateRtcToken($channelName, $userId);

// Generate Astrologer Token if not exists
$astroToken = !empty($session['agora_astro_token']) ? $session['agora_astro_token'] : AgoraToken::generateRtcToken($channelName, $astrologerId);

// Mark session ACTIVE and record connection start time!
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
if ($driver === 'sqlite') {
    $db->prepare("UPDATE consultation_sessions 
                  SET status = 'active', start_time = datetime('now'), user_accepted_at = datetime('now'),
                      agora_user_token = ?, agora_astro_token = ? 
                  WHERE id = ?")
       ->execute([$userToken, $astroToken, $session['id']]);
} else {
    $db->prepare("UPDATE consultation_sessions 
                  SET status = 'active', start_time = NOW(), user_accepted_at = NOW(),
                      agora_user_token = ?, agora_astro_token = ? 
                  WHERE id = ?")
       ->execute([$userToken, $astroToken, $session['id']]);
}

// Mark astrologer busy
$db->prepare("UPDATE astrologers SET call_status = 'busy' WHERE id = ?")->execute([$astrologerId]);

Response::json(true, 'Devotee accepted call! Room connected successfully.', [
    'session_id'        => $sessionId,
    'status'            => 'active',
    'channel_name'      => $channelName,
    'agora_user_token'  => $userToken,
    'agora_astro_token' => $astroToken,
    'type'              => $session['type'],
    'rate_per_min'      => (float)$session['rate_per_min'],
    'user_id'           => $userId,
    'astrologer'        => [
        'id'     => $astrologerId,
        'name'   => $session['astrologer_name'] ?? 'Guruji S. Sundaramurthy',
        'avatar' => $session['astrologer_avatar'] ?? 'assets/logo.jpg'
    ]
]);
