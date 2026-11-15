<?php
// api/sessions/check_status.php - Devotee / Astrologer polls session status
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/AgoraToken.php';
require_once __DIR__ . '/../../helpers/Settings.php';

$sessionId = trim($_GET['session_id'] ?? ($_POST['session_id'] ?? ''));

if (empty($sessionId)) {
    Response::json(false, 'session_id is required.', null, 400);
}

$db = getDb();
$stmt = $db->prepare("SELECT s.*, 
                             a.name as astrologer_name, a.avatar as astrologer_avatar,
                             u.name as user_name, u.phone as user_phone
                      FROM consultation_sessions s
                      LEFT JOIN astrologers a ON s.astrologer_id = a.id
                      LEFT JOIN users u ON s.user_id = u.id
                      WHERE s.session_id = ?");
$stmt->execute([$sessionId]);
$session = $stmt->fetch();

if (!$session) {
    Response::json(false, 'Session not found.', null, 404);
}

$status = $session['status'];
$channelName = $session['agora_channel'];
$userId = (int)$session['user_id'];
$astroId = (int)$session['astrologer_id'];

$userToken = $session['agora_user_token'] ?? '';
$astroToken = $session['agora_astro_token'] ?? '';
$userUid = AgoraToken::getUserUid($userId);
$astroUid = AgoraToken::getAstroUid($astroId);

if (empty($userToken)) {
    $userToken = AgoraToken::generateUserToken($channelName, $userId);
    try {
        $db->prepare("UPDATE consultation_sessions SET agora_user_token = ? WHERE id = ?")->execute([$userToken, $session['id']]);
    } catch (\Throwable $t) {}
}
if (empty($astroToken)) {
    $astroToken = AgoraToken::generateAstroToken($channelName, $astroId);
    try {
        $db->prepare("UPDATE consultation_sessions SET agora_astro_token = ? WHERE id = ?")->execute([$astroToken, $session['id']]);
    } catch (\Throwable $t) {}
}

Response::json(true, "Session status is {$status}", [
    'session_id'        => $sessionId,
    'status'            => $status, // 'requested' | 'ringing_user' | 'active' | 'cancelled' | 'completed'
    'channel_name'      => $channelName,
    'agora_app_id'      => Settings::get('agora_app_id', 'f5df168c9c7849ef892cd39843d1ba42'),
    'agora_user_token'  => $userToken,
    'agora_astro_token' => $astroToken,
    'agora_user_uid'    => $userUid,
    'agora_astro_uid'   => $astroUid,
    'type'              => $session['type'],
    'rate_per_min'      => (float)$session['rate_per_min'],
    'duration_seconds'  => (int)$session['duration_seconds'],
    'total_deducted'    => (float)($session['total_deducted'] ?? 0.0),
    'astrologer_share'  => (float)($session['astrologer_share'] ?? 0.0),
    'user'              => [
        'id'    => $userId,
        'name'  => $session['user_name'] ?? 'Devotee',
        'phone' => $session['user_phone'] ?? ''
    ],
    'astrologer'        => [
        'id'     => $astroId,
        'name'   => $session['astrologer_name'] ?? 'Guruji S. Sundaramurthy',
        'avatar' => $session['astrologer_avatar'] ?? 'assets/logo.jpg'
    ]
]);
