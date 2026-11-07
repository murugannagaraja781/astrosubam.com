<?php
// api/support/ticket_detail.php
// SageTalkz Workflow 8: Fetch single ticket conversation thread
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';

$ticketId = (int)($_GET['ticket_id'] ?? 0);
$ticketNumber = trim($_GET['ticket_number'] ?? '');

$db = getDb();

if ($ticketId > 0) {
    $stmt = $db->prepare("SELECT * FROM support_tickets WHERE id = ?");
    $stmt->execute([$ticketId]);
} else if (!empty($ticketNumber)) {
    $stmt = $db->prepare("SELECT * FROM support_tickets WHERE ticket_number = ?");
    $stmt->execute([$ticketNumber]);
} else {
    Response::json(false, 'Ticket ID or Ticket Number required.', null, 400);
}

$ticket = $stmt->fetch();
if (!$ticket) {
    Response::json(false, 'Support ticket not found.', null, 404);
}

$msgStmt = $db->prepare("SELECT * FROM support_ticket_messages WHERE ticket_id = ? ORDER BY id ASC");
$msgStmt->execute([(int)$ticket['id']]);
$messages = $msgStmt->fetchAll();

Response::json(true, 'Ticket details retrieved successfully.', [
    'ticket'   => $ticket,
    'messages' => $messages
]);
