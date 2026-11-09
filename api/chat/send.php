<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';

$input = Response::getJsonInput();
$sessionId = trim($input['session_id'] ?? ($_POST['session_id'] ?? ''));
$senderType = in_array($input['sender_type'] ?? ($_POST['sender_type'] ?? ''), ['user', 'astrologer']) ? ($input['sender_type'] ?? $_POST['sender_type']) : 'user';
$senderId = (int)($input['sender_id'] ?? ($_POST['sender_id'] ?? 0));
$content = trim($input['content'] ?? ($_POST['content'] ?? ''));
$messageType = $input['message_type'] ?? ($_POST['message_type'] ?? 'text');

if (empty($sessionId) || empty($content)) {
    Response::json(false, 'session_id and content are required.', null, 400);
}

$db = getDb();
$stmt = $db->prepare("INSERT INTO chat_messages (session_id, sender_type, sender_id, message_type, content) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$sessionId, $senderType, $senderId, $messageType, $content]);
$msgId = (int)$db->lastInsertId();

Response::json(true, 'Message sent.', [
    'message_id'  => $msgId,
    'session_id'  => $sessionId,
    'sender_type' => $senderType,
    'sender_id'   => $senderId,
    'content'     => $content,
    'created_at'  => date('Y-m-d H:i:s')
]);
