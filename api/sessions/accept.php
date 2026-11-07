<?php
// api/sessions/accept.php - Astrologer accepts consultation request (status becomes 'ringing_user' & sends FCM)
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/AgoraToken.php';
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

$channelName = $session['agora_channel'];
$astrologerId = $astroId > 0 ? $astroId : (int)$session['astrologer_id'];

// 1. Generate Agora RTC Token for Astrologer ahead of time
$astroToken = AgoraToken::generateRtcToken($channelName, $astrologerId);

// 2. Mark session status as 'ringing_user'
$db->prepare("UPDATE consultation_sessions 
              SET status = 'ringing_user', agora_astro_token = ? 
              WHERE id = ?")
   ->execute([$astroToken, $session['id']]);

// 3. Dispatch Full Screen FCM Call Alert to Devotee Device
$fcmResult = FCM::sendIncomingCallToUser((int)$session['user_id'], [
    'session_id'        => $sessionId,
    'channel_name'      => $channelName,
    'astrologer_id'     => $astrologerId,
    'astrologer_name'   => $session['astrologer_name'] ?? 'Guruji S. Sundaramurthy',
    'astrologer_avatar' => $session['astrologer_avatar'] ?? 'assets/logo.jpg',
    'type'              => $session['type'],
    'rate_per_min'      => $session['rate_per_min']
]);

Response::json(true, 'Consultation accepted! Ringing devotee phone with full screen call alert...', [
    'session_id'          => $sessionId,
    'status'              => 'ringing_user',
    'channel_name'        => $channelName,
    'agora_astro_token'   => $astroToken,
    'type'                => $session['type'],
    'rate_per_min'        => (float)$session['rate_per_min'],
    'user_id'             => (int)$session['user_id'],
    'user_name'           => $session['user_name'] ?? 'Devotee',
    'user_phone'          => $session['user_phone'] ?? '',
    'fcm'                 => $fcmResult
]);
