<?php
// api/sessions/pending.php - Astrologer checks for incoming consultation requests
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';

$input = Response::getJsonInput();
$astroId = (int)($input['astrologer_id'] ?? ($_GET['astrologer_id'] ?? ($_SESSION['astrologer_id'] ?? 0)));
$astroPhone = trim($input['phone'] ?? ($_GET['phone'] ?? ($_SESSION['astrologer_phone'] ?? '')));

$db = getDb();

if ($astroId <= 0 && !empty($astroPhone)) {
    $aStmt = $db->prepare("SELECT id FROM astrologers WHERE phone = ?");
    $aStmt->execute([$astroPhone]);
    $astroId = (int)$aStmt->fetchColumn();
}

if ($astroId <= 0) {
    // Fallback to first astrologer
    $astroId = 1;
}

// Find pending sessions with status='requested' for this astrologer
$stmt = $db->prepare("SELECT s.*, u.name as user_name, u.phone as user_phone
    FROM consultation_sessions s
    LEFT JOIN users u ON s.user_id = u.id
    WHERE s.astrologer_id = ? AND s.status = 'requested'
    ORDER BY s.id DESC LIMIT 1");

$stmt->execute([$astroId]);
$pending = $stmt->fetch();

if (!$pending) {
    Response::json(true, 'No incoming consultations at this moment.', [
        'has_incoming' => false
    ]);
}

// Calculate elapsed seconds safely with UTC & Local normalization
$createdAtStr = (string)($pending['created_at'] ?? '');
$now = time();
$parsedUtc = !empty($createdAtStr) ? strtotime($createdAtStr . ' UTC') : $now;
$parsedLocal = !empty($createdAtStr) ? strtotime($createdAtStr) : $now;

$diffUtc = $now - $parsedUtc;
$diffLocal = $now - $parsedLocal;

$secondsElapsed = 0;
if ($diffUtc >= 0 && $diffUtc < 3600) {
    $secondsElapsed = $diffUtc;
} elseif ($diffLocal >= 0 && $diffLocal < 3600) {
    $secondsElapsed = $diffLocal;
} else {
    $secondsElapsed = max(0, min(abs($diffUtc), abs($diffLocal)));
}

// If request is older than 45 seconds and still pending, auto-cancel it
if ($secondsElapsed > 45) {
    $db->prepare("UPDATE consultation_sessions SET status = 'cancelled' WHERE id = ?")->execute([$pending['id']]);
    Response::json(true, 'Consultation request timed out.', [
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
        'seconds_elapsed' => $secondsElapsed,
        'seconds_left'    => max(1, 30 - $secondsElapsed),
        'created_at'      => $pending['created_at']
    ]
]);
