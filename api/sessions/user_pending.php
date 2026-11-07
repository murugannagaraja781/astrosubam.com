<?php
// api/sessions/user_pending.php - Devotee checks if there is an incoming call for them (status='ringing_user')
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';

$userId = (int)($_GET['user_id'] ?? ($_POST['user_id'] ?? 0));

if ($userId <= 0) {
    Response::json(false, 'user_id is required.', null, 400);
}

$db = getDb();
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

if ($driver === 'sqlite') {
    $stmt = $db->prepare("SELECT s.*, 
                                 a.name as astrologer_name, a.avatar as astrologer_avatar,
                                 a.specialities as astrologer_specialities, a.languages as astrologer_languages,
                                 (strftime('%s', 'now') - strftime('%s', s.created_at)) as seconds_elapsed
                          FROM consultation_sessions s
                          LEFT JOIN astrologers a ON s.astrologer_id = a.id
                          WHERE s.user_id = ? AND s.status = 'ringing_user'
                          AND (strftime('%s', 'now') - strftime('%s', s.created_at)) <= 45
                          ORDER BY s.id DESC LIMIT 1");
} else {
    $stmt = $db->prepare("SELECT s.*, 
                                 a.name as astrologer_name, a.avatar as astrologer_avatar,
                                 a.specialities as astrologer_specialities, a.languages as astrologer_languages,
                                 TIMESTAMPDIFF(SECOND, s.created_at, NOW()) as seconds_elapsed
                          FROM consultation_sessions s
                          LEFT JOIN astrologers a ON s.astrologer_id = a.id
                          WHERE s.user_id = ? AND s.status = 'ringing_user'
                          AND TIMESTAMPDIFF(SECOND, s.created_at, NOW()) <= 45
                          ORDER BY s.id DESC LIMIT 1");
}

$stmt->execute([$userId]);
$incoming = $stmt->fetch();

if (!$incoming) {
    Response::json(true, 'No incoming calls.', ['has_incoming' => false]);
}

Response::json(true, 'Incoming call detected for devotee!', [
    'has_incoming' => true,
    'session'      => [
        'session_id'        => $incoming['session_id'],
        'channel_name'      => $incoming['agora_channel'],
        'type'              => $incoming['type'],
        'rate_per_min'      => (float)$incoming['rate_per_min'],
        'seconds_elapsed'   => (int)($incoming['seconds_elapsed'] ?? 0),
        'seconds_left'      => max(0, 30 - (int)($incoming['seconds_elapsed'] ?? 0)),
        'astrologer'        => [
            'id'            => (int)$incoming['astrologer_id'],
            'name'          => $incoming['astrologer_name'] ?? 'Guruji S. Sundaramurthy',
            'avatar'        => $incoming['astrologer_avatar'] ?? 'assets/logo.jpg',
            'specialities'  => $incoming['astrologer_specialities'] ?? 'Vedic Astrology',
            'languages'     => $incoming['astrologer_languages'] ?? 'Tamil, English'
        ]
    ]
]);
