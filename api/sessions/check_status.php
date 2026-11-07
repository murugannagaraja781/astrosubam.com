<?php
// api/sessions/check_status.php - Devotee / Astrologer polls session status
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/AgoraToken.php';

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

if ($status === 'active') {
    if (empty($userToken)) {
        $userToken = AgoraToken::generateRtcToken($channelName, $userId);
    }
    if (empty($astroToken)) {
        $astroToken = AgoraToken::generateRtcToken($channelName, $astroId);
    }
}

Response::json(true, "Session status is {$status}", [
    'session_id'        => $sessionId,
    'status'            => $status, // 'requested' | 'ringing_user' | 'active' | 'cancelled' | 'completed'
    'channel_name'      => $channelName,
    'agora_user_token'  => $userToken,
    'agora_astro_token' => $astroToken,
    'type'              => $session['type'],
    'rate_per_min'      => (float)$session['rate_per_min'],
    'duration_seconds'  => (int)$session['duration_seconds'],
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
