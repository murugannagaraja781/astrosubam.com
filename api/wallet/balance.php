<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/JWT.php';

$user = JWT::getAuthUser();
$userId = $user['user_id'] ?? (int)($_GET['user_id'] ?? 0);

if ($userId <= 0) {
    Response::json(false, 'User ID required.', null, 400);
}

$db = getDb();
$stmt = $db->prepare("SELECT id, name, phone, wallet_balance FROM users WHERE id = ?");
$stmt->execute([$userId]);
$userData = $stmt->fetch();

if (!$userData) {
    Response::json(false, 'User not found.', null, 404);
}

// Fetch last 10 transactions
$tStmt = $db->prepare("SELECT id, type, amount, balance_after, description, reference_id, created_at 
                       FROM wallet_transactions WHERE user_id = ? ORDER BY id DESC LIMIT 10");
$tStmt->execute([$userId]);
$transactions = $tStmt->fetchAll();

Response::json(true, 'Wallet details fetched.', [
    'wallet_balance' => (float)$userData['wallet_balance'],
    'transactions'   => $transactions
]);
