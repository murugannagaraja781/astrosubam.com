<?php
// api/sessions/reject.php - Astrologer declines or 30s timeout cancels session
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';

$input = Response::getJsonInput();
$sessionId = trim($input['session_id'] ?? ($_GET['session_id'] ?? ''));

if (empty($sessionId)) {
    Response::json(false, 'session_id is required.', null, 400);
}

$db = getDb();
$stmt = $db->prepare("SELECT id, astrologer_id, status FROM consultation_sessions WHERE session_id = ?");
$stmt->execute([$sessionId]);
$session = $stmt->fetch();

if (!$session) {
    Response::json(false, 'Session not found.', null, 404);
}

if ($session['status'] === 'requested' || $session['status'] === 'ringing_user' || $session['status'] === 'active') {
    $db->prepare("UPDATE consultation_sessions SET status = 'cancelled', end_time = CURRENT_TIMESTAMP WHERE id = ?")->execute([$session['id']]);
    $db->prepare("UPDATE astrologers SET call_status = 'available' WHERE id = ?")->execute([$session['astrologer_id']]);
}

Response::json(true, 'Session has been cancelled/declined.', [
    'session_id' => $sessionId,
    'status'     => 'cancelled'
]);
