<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/JWT.php';

$user = JWT::getAuthUser();
$input = Response::getJsonInput();
$userId = $user['user_id'] ?? (int)($input['user_id'] ?? 0);
$amount = (float)($input['amount'] ?? 0);
$refId = trim($input['reference_id'] ?? ('PAY_' . time() . '_' . mt_rand(100, 999)));

if ($userId <= 0 || $amount < 50) {
    Response::json(false, 'Minimum recharge amount is ₹50.', null, 400);
}

$db = getDb();
$db->beginTransaction();

try {
    $uStmt = $db->prepare("SELECT wallet_balance FROM users WHERE id = ? FOR UPDATE");
    $uStmt->execute([$userId]);
    $userData = $uStmt->fetch();

    if (!$userData) {
        $db->rollBack();
        Response::json(false, 'User not found.', null, 404);
    }

    $newBalance = (float)$userData['wallet_balance'] + $amount;
    $db->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?")->execute([$newBalance, $userId]);

    $db->prepare("INSERT INTO wallet_transactions (user_id, type, amount, balance_after, description, reference_id) 
                  VALUES (?, 'credit', ?, ?, 'Wallet Recharge via UPI/Card', ?)")
       ->execute([$userId, $amount, $newBalance, $refId]);

    $db->commit();

    Response::json(true, "Recharge of ₹{$amount} successful!", [
        'new_balance'  => $newBalance,
        'reference_id' => $refId
    ]);
} catch (Exception $e) {
    $db->rollBack();
    Response::json(false, 'Recharge failed: ' . $e->getMessage(), null, 500);
}
