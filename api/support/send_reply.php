<?php
// api/support/send_reply.php
// SageTalkz Workflow 8: Send reply message in support ticket thread
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';

$input = Response::getJsonInput();

$ticketId = (int)($input['ticket_id'] ?? 0);
$senderType = trim($input['sender_type'] ?? 'user'); // 'user' | 'support' | 'admin'
$senderName = trim($input['sender_name'] ?? ($senderType === 'user' ? 'Devotee' : 'Support Desk'));
$message = trim($input['message'] ?? '');
$updateStatus = trim($input['status'] ?? ''); // optional status update: 'WAITING', 'OPEN', 'CLOSED'

if ($ticketId <= 0) {
    Response::json(false, 'Ticket ID is required.', null, 400);
}

if (empty($message) && empty($updateStatus)) {
    Response::json(false, 'Message cannot be empty.', null, 422);
}

$db = getDb();
$chk = $db->prepare("SELECT id, status FROM support_tickets WHERE id = ?");
$chk->execute([$ticketId]);
$ticket = $chk->fetch();

if (!$ticket) {
    Response::json(false, 'Support ticket not found.', null, 404);
}

if (!empty($message)) {
    $ins = $db->prepare("INSERT INTO support_ticket_messages (ticket_id, sender_type, sender_name, message) VALUES (?, ?, ?, ?)");
    $ins->execute([$ticketId, $senderType, $senderName, $message]);
}

// Update ticket status
$newStatus = $ticket['status'];
if (!empty($updateStatus) && in_array(strtoupper($updateStatus), ['WAITING', 'OPEN', 'CLOSED'], true)) {
    $newStatus = strtoupper($updateStatus);
} else if ($senderType === 'support' || $senderType === 'admin') {
    if ($ticket['status'] === 'WAITING') $newStatus = 'OPEN';
} else if ($senderType === 'user' && $ticket['status'] === 'CLOSED') {
    $newStatus = 'WAITING'; // Re-open ticket if user messages again
}

$upd = $db->prepare("UPDATE support_tickets SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
$upd->execute([$newStatus, $ticketId]);

// Fetch updated conversation
$msgStmt = $db->prepare("SELECT * FROM support_ticket_messages WHERE ticket_id = ? ORDER BY id ASC");
$msgStmt->execute([$ticketId]);
$messages = $msgStmt->fetchAll();

Response::json(true, 'Reply sent successfully.', [
    'ticket_id' => $ticketId,
    'status'    => $newStatus,
    'messages'  => $messages
]);

