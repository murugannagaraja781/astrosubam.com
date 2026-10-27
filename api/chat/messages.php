<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';

$sessionId = trim($_GET['session_id'] ?? '');
if (empty($sessionId)) {
    Response::json(false, 'session_id is required.', null, 400);
}

$db = getDb();
$stmt = $db->prepare("SELECT id, session_id, sender_type, sender_id, message_type, content, file_url, created_at 
                      FROM chat_messages WHERE session_id = ? ORDER BY id ASC");
$stmt->execute([$sessionId]);
$messages = $stmt->fetchAll();

Response::json(true, 'Messages loaded.', [
    'messages' => $messages
]);
