<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';

$input = Response::getJsonInput();
$sessionId = trim($input['session_id'] ?? '');
$senderType = in_array($input['sender_type'] ?? '', ['user', 'astrologer']) ? $input['sender_type'] : 'user';
$senderId = (int)($input['sender_id'] ?? 0);
$content = trim($input['content'] ?? '');
$messageType = $input['message_type'] ?? 'text';

if (empty($sessionId) || empty($content)) {
    Response::json(false, 'session_id and content are required.', null, 400);
}

$db = getDb();
$stmt = $db->prepare("INSERT INTO chat_messages (session_id, sender_type, sender_id, message_type, content) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$sessionId, $senderType, $senderId, $messageType, $content]);

Response::json(true, 'Message sent.', [
    'message_id' => (int)$db->lastInsertId(),
    'session_id' => $sessionId,
    'content'    => $content,
    'created_at' => date('Y-m-d H:i:s')
]);
