<?php
// api/sessions/pending.php - Astrologer checks for incoming consultation requests
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';

$input = Response::getJsonInput();
$astroId = (int)($input['astrologer_id'] ?? ($_GET['astrologer_id'] ?? 0));
$astroPhone = trim($input['phone'] ?? ($_GET['phone'] ?? ''));

$db = getDb();

if ($astroId <= 0 && !empty($astroPhone)) {
    $aStmt = $db->prepare("SELECT id FROM astrologers WHERE phone = ?");
    $aStmt->execute([$astroPhone]);
    $astroId = (int)$aStmt->fetchColumn();
}

if ($astroId <= 0) {
    // Default to first astrologer (Guruji Sundaramurthy)
    $astroId = 1;
}

// Find pending sessions created within the last 45 seconds
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

if ($driver === 'sqlite') {
    $stmt = $db->prepare("SELECT s.*, u.name as user_name, u.phone as user_phone,
        (strftime('%s', 'now') - strftime('%s', s.created_at)) as seconds_elapsed
        FROM consultation_sessions s
        LEFT JOIN users u ON s.user_id = u.id
        WHERE s.astrologer_id = ? AND s.status = 'requested'
        AND (strftime('%s', 'now') - strftime('%s', s.created_at)) <= 45
        ORDER BY s.id DESC LIMIT 1");
} else {
    $stmt = $db->prepare("SELECT s.*, u.name as user_name, u.phone as user_phone,
        TIMESTAMPDIFF(SECOND, s.created_at, NOW()) as seconds_elapsed
        FROM consultation_sessions s
        LEFT JOIN users u ON s.user_id = u.id
        WHERE s.astrologer_id = ? AND s.status = 'requested'
        AND TIMESTAMPDIFF(SECOND, s.created_at, NOW()) <= 45
        ORDER BY s.id DESC LIMIT 1");
}

$stmt->execute([$astroId]);
$pending = $stmt->fetch();

if (!$pending) {
    Response::json(true, 'No incoming consultations at this moment.', [
        'has_incoming' => false
    ]);
}

$uName = !empty($pending['user_name']) ? $pending['user_name'] : ('பக்தர் (' . ($pending['user_phone'] ?? 'User') . ')');

Response::json(true, 'Incoming consultation detected!', [
    'has_incoming'    => true,
    'session'         => [
        'session_id'      => $pending['session_id'],
        'channel_name'    => $pending['agora_channel'],
        'type'            => $pending['type'],
        'rate_per_min'    => (float)$pending['rate_per_min'],
        'user_id'         => (int)$pending['user_id'],
        'user_name'       => $uName,
        'user_phone'      => $pending['user_phone'] ?? '8000000001',
        'seconds_elapsed' => (int)($pending['seconds_elapsed'] ?? 0),
        'seconds_left'    => max(0, 30 - (int)($pending['seconds_elapsed'] ?? 0)),
        'created_at'      => $pending['created_at']
    ]
]);

