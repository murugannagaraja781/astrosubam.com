<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';

$input = Response::getJsonInput();
$sessionId = trim($input['session_id'] ?? '');
$rating = isset($input['rating']) ? (int)$input['rating'] : null;
$review = trim($input['review'] ?? '');

if (empty($sessionId)) {
    Response::json(false, 'session_id is required.', null, 400);
}

$db = getDb();
$stmt = $db->prepare("SELECT id, astrologer_id, duration_seconds, total_deducted, status FROM consultation_sessions WHERE session_id = ?");
$stmt->execute([$sessionId]);
$session = $stmt->fetch();

if (!$session) {
    Response::json(false, 'Session not found.', null, 404);
}

$db->prepare("UPDATE consultation_sessions 
              SET status = 'completed', end_time = NOW(), rating = ?, review = ? 
              WHERE id = ?")
   ->execute([$rating, $review, $session['id']]);

// Free astrologer
$db->prepare("UPDATE astrologers SET call_status = 'available' WHERE id = ?")->execute([$session['astrologer_id']]);

Response::json(true, 'Consultation session ended cleanly.', [
    'session_id'     => $sessionId,
    'duration'       => (int)$session['duration_seconds'],
    'total_deducted' => (float)$session['total_deducted'],
    'status'         => 'completed'
]);
