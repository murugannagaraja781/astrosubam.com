<?php
// api/support/create_ticket.php
// SageTalkz Workflow 8: Customer Support Ticket Creation
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/JWT.php';
require_once __DIR__ . '/../../helpers/SupportHelper.php';

$input = Response::getJsonInput();

$userId = (int)($input['user_id'] ?? 0);
$userPhone = trim($input['user_phone'] ?? '');
$category = trim($input['category'] ?? '');
$subject = trim($input['subject'] ?? '');
$description = trim($input['description'] ?? '');

// Try reading auth user if available
if ($userId <= 0) {
    $authUser = JWT::getAuthUser();
    if ($authUser && isset($authUser['user_id'])) {
        $userId = (int)$authUser['user_id'];
        $userPhone = $authUser['phone'] ?? $userPhone;
    }
}

if ($userId <= 0) {
    // Fallback devotee user ID for testing
    $userId = 2;
    if (empty($userPhone)) $userPhone = '8000000001';
}

if (empty($category)) {
    Response::json(false, 'Please select a support category (e.g. Wallet Issue, Call Drop).', null, 422);
}

// Workflow 8 Rule: 30 to 400 characters validation
$descLen = mb_strlen($description, 'UTF-8');
if ($descLen < 30) {
    Response::json(false, "Description must be at least 30 characters (Current: {$descLen}).", [
        'min_length' => 30,
        'max_length' => 400,
        'current_length' => $descLen
    ], 422);
}

if ($descLen > 400) {
    Response::json(false, "Description cannot exceed 400 characters (Current: {$descLen}).", [
        'min_length' => 30,
        'max_length' => 400,
        'current_length' => $descLen
    ], 422);
}

if (empty($subject)) {
    $subject = $category . ' - ' . mb_substr($description, 0, 35) . '...';
}

$db = getDb();
SupportHelper::ensureTables($db);

// Generate unique ticket number
$ticketNumber = 'TKT-' . strtoupper(substr(uniqid(), -6));

$stmt = $db->prepare("INSERT INTO support_tickets 
    (ticket_number, user_id, user_phone, category, subject, description, status, priority) 
    VALUES (?, ?, ?, ?, ?, ?, 'WAITING', 'MEDIUM')");

$stmt->execute([$ticketNumber, $userId, $userPhone, $category, $subject, $description]);
$ticketId = (int)$db->lastInsertId();

// Insert initial message into conversation thread
$msgStmt = $db->prepare("INSERT INTO support_ticket_messages 
    (ticket_id, sender_type, sender_name, message) 
    VALUES (?, 'user', 'Devotee', ?)");
$msgStmt->execute([$ticketId, $description]);

// Insert automated welcome reply from AstroSubam Support
$botStmt = $db->prepare("INSERT INTO support_ticket_messages 
    (ticket_id, sender_type, sender_name, message) 
    VALUES (?, 'support', 'AstroSubam Support Desk', ?)");
$botGreeting = "வணக்கம்! Your query regarding '{$category}' has been assigned ticket ID {$ticketNumber}. Our support team will assist you shortly.";
$botStmt->execute([$ticketId, $botGreeting]);

Response::json(true, "Support query submitted successfully! Your Ticket ID is {$ticketNumber}.", [
    'ticket_id'      => $ticketId,
    'ticket_number'  => $ticketNumber,
    'category'       => $category,
    'subject'        => $subject,
    'description'    => $description,
    'status'         => 'WAITING',
    'created_at'     => date('Y-m-d H:i:s')
], 201);
