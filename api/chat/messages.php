<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';

$sessionId = trim($_GET['session_id'] ?? ($_POST['session_id'] ?? ''));
$lastId = (int)($_GET['last_id'] ?? ($_POST['last_id'] ?? 0));

if (empty($sessionId)) {
    Response::json(false, 'session_id is required.', null, 400);
}

$db = getDb();

if ($lastId > 0) {
    $stmt = $db->prepare("SELECT id, session_id, sender_type, sender_id, message_type, content, file_url, created_at 
                          FROM chat_messages WHERE session_id = ? AND id > ? ORDER BY id ASC");
    $stmt->execute([$sessionId, $lastId]);
} else {
    $stmt = $db->prepare("SELECT id, session_id, sender_type, sender_id, message_type, content, file_url, created_at 
                          FROM chat_messages WHERE session_id = ? ORDER BY id ASC");
    $stmt->execute([$sessionId]);
}

$messages = $stmt->fetchAll() ?: [];

Response::json(true, 'Messages loaded.', [
    'messages' => $messages,
    'count'    => count($messages)
]);
