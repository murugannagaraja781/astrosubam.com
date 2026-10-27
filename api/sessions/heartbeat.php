<?php
// api/sessions/heartbeat.php - Real-time 60-Second Consultation Ticker
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';

$input = Response::getJsonInput();
$sessionId = trim($input['session_id'] ?? '');

if (empty($sessionId)) {
    Response::json(false, 'session_id is required.', null, 400);
}

$db = getDb();
$db->beginTransaction();

try {
    $stmt = $db->prepare("SELECT id, user_id, astrologer_id, rate_per_min, status, total_deducted, duration_seconds 
                          FROM consultation_sessions WHERE session_id = ? FOR UPDATE");
    $stmt->execute([$sessionId]);
    $session = $stmt->fetch();

    if (!$session || $session['status'] !== 'active') {
        $db->rollBack();
        Response::json(false, 'Session is not active or already finished.', ['terminate' => true], 200);
    }

    $userId = (int)$session['user_id'];
    $rate = (float)$session['rate_per_min'];

    // Check user current balance
    $uStmt = $db->prepare("SELECT wallet_balance FROM users WHERE id = ? FOR UPDATE");
    $uStmt->execute([$userId]);
    $user = $uStmt->fetch();

    $currentBalance = (float)$user['wallet_balance'];

    // If balance is less than 1 minute rate, terminate call
    if ($currentBalance < $rate) {
        // End the session
        $db->prepare("UPDATE consultation_sessions SET status = 'completed', end_time = NOW() WHERE id = ?")->execute([$session['id']]);
        $db->prepare("UPDATE astrologers SET call_status = 'available' WHERE id = ?")->execute([$session['astrologer_id']]);
        $db->commit();

        Response::json(true, 'Wallet balance exhausted! Consultation auto-terminated.', [
            'terminate'       => true,
            'current_balance' => $currentBalance,
            'reason'          => 'insufficient_funds'
        ]);
    }

    // Deduct 1 minute
    $newBalance = $currentBalance - $rate;
    $db->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?")->execute([$newBalance, $userId]);

    // Record wallet transaction
    $db->prepare("INSERT INTO wallet_transactions (user_id, type, amount, balance_after, description, reference_id) 
                  VALUES (?, 'debit', ?, ?, 'Consultation 1-min billing', ?)")
       ->execute([$userId, $rate, $newBalance, $sessionId]);

    // Update session duration and total deducted
    $newDuration = (int)$session['duration_seconds'] + 60;
    $newTotalDeducted = (float)$session['total_deducted'] + $rate;
    $astroShare = $newTotalDeducted * 0.80; // 80% to Astrologer, 20% to Platform

    $db->prepare("UPDATE consultation_sessions 
                  SET duration_seconds = ?, total_deducted = ?, astrologer_share = ? 
                  WHERE id = ?")
       ->execute([$newDuration, $newTotalDeducted, $astroShare, $session['id']]);

    $db->commit();

    $remainingMinutes = floor($newBalance / $rate);

    Response::json(true, 'Heartbeat recorded successfully.', [
        'terminate'         => false,
        'deducted'          => $rate,
        'remaining_balance' => $newBalance,
        'remaining_minutes' => (int)$remainingMinutes,
        'duration_seconds'  => $newDuration
    ]);
} catch (Exception $e) {
    $db->rollBack();
    Response::json(false, 'Heartbeat processing error: ' . $e->getMessage(), null, 500);
}
