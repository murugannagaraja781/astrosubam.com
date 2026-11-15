<?php
// api/sessions/end.php - Complete Call Termination, Duration Calculation, Billing & Astrologer Payout
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';

$input = Response::getJsonInput();
$sessionId = trim($input['session_id'] ?? ($_GET['session_id'] ?? ''));
$durationSeconds = isset($input['duration_seconds']) ? (int)$input['duration_seconds'] : null;
$rating = isset($input['rating']) ? (int)$input['rating'] : null;
$review = trim($input['review'] ?? '');
$endedBy = trim($input['ended_by'] ?? 'user'); // 'user' or 'astrologer'

if (empty($sessionId)) {
    Response::json(false, 'session_id is required.', null, 400);
}

$db = getDb();
$stmt = $db->prepare("SELECT s.*, u.wallet_balance as user_wallet_balance, 
                             a.name as astrologer_name, a.call_status as astrologer_call_status
                      FROM consultation_sessions s
                      LEFT JOIN users u ON s.user_id = u.id
                      LEFT JOIN astrologers a ON s.astrologer_id = a.id
                      WHERE s.session_id = ?");
$stmt->execute([$sessionId]);
$session = $stmt->fetch();

if (!$session) {
    Response::json(false, 'Session not found.', null, 404);
}

// If already completed, return existing final data cleanly
if ($session['status'] === 'completed') {
    Response::json(true, 'Consultation already completed.', [
        'session_id'            => $sessionId,
        'duration_seconds'      => (int)$session['duration_seconds'],
        'total_deducted'        => (float)$session['total_deducted'],
        'astrologer_share'      => (float)$session['astrologer_share'],
        'user_remaining_balance'=> (float)($session['user_wallet_balance'] ?? 0.0),
        'status'                => 'completed'
    ]);
}

$userId = (int)$session['user_id'];
$astroId = (int)$session['astrologer_id'];
$ratePerMin = (float)$session['rate_per_min'];
$alreadyDeducted = (float)$session['total_deducted'];
$existingDuration = (int)$session['duration_seconds'];

// 1. Determine Actual Connected Duration
$finalDuration = 0;
if ($durationSeconds !== null && $durationSeconds >= 0) {
    $finalDuration = $durationSeconds;
} elseif ($existingDuration > 0) {
    $finalDuration = $existingDuration;
} elseif (!empty($session['start_time']) && $session['status'] === 'active') {
    $startTs = strtotime($session['start_time']);
    if ($startTs > 0) {
        $finalDuration = max(0, time() - $startTs);
    }
}

// 2. Atomic Database Transaction: Deduct User Wallet & Credit Astrologer
$isSqlite = ($db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
$forUpdate = $isSqlite ? '' : ' FOR UPDATE';

$db->beginTransaction();

try {
    // 2.1 Fetch Fresh User Balance with Row Lock
    $uStmt = $db->prepare("SELECT id, wallet_balance FROM users WHERE id = ?" . $forUpdate);
    $uStmt->execute([$userId]);
    $userRow = $uStmt->fetch();

    $currentUserBalance = $userRow ? (float)$userRow['wallet_balance'] : 0.0;
    $totalDeducted = $alreadyDeducted;
    $astroShare = (float)$session['astrologer_share'];

    if ($finalDuration <= 0) {
        // Unconnected / Cancelled before media connected -> No charges, zero payout
        $finalDuration = 0;
        $totalDeducted = 0.00;
        $astroShare = 0.00;
    } else {
        // Media connected: calculate billable minutes & cost
        $billedMinutes = (int)ceil($finalDuration / 60.0);
        $totalCallCost = round($billedMinutes * $ratePerMin, 2);

        // Deduct remaining difference from user wallet
        $toDeduct = max(0.0, $totalCallCost - $alreadyDeducted);

        if ($toDeduct > 0 && $userRow) {
            $actualDeduct = min($currentUserBalance, $toDeduct);
            $newBalance = max(0.0, $currentUserBalance - $actualDeduct);

            // ⚡ User Wallet Deduction (Debit)
            $db->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?")
               ->execute([$newBalance, $userId]);

            // ⚡ User Transaction Ledger Log
            $db->prepare("INSERT INTO wallet_transactions (user_id, type, amount, balance_after, description, reference_id) 
                          VALUES (?, 'debit', ?, ?, 'Consultation call settlement', ?)")
               ->execute([$userId, $actualDeduct, $newBalance, $sessionId]);

            $currentUserBalance = $newBalance;
            $totalDeducted = round($alreadyDeducted + $actualDeduct, 2);
        }

        // ⚡ Astrologer Payout: 80% Revenue Share
        $finalAstroShare = round($totalDeducted * 0.80, 2);
        $newAstroShareDiff = max(0.0, $finalAstroShare - $astroShare);

        if ($newAstroShareDiff > 0) {
            $db->prepare("UPDATE astrologers SET wallet_balance = COALESCE(wallet_balance, 0) + ? WHERE id = ?")
               ->execute([$newAstroShareDiff, $astroId]);
        }

        $astroShare = $finalAstroShare;
    }

    // 3. Mark session COMPLETED and record final metrics
    $db->prepare("UPDATE consultation_sessions 
                  SET status = 'completed', end_time = CURRENT_TIMESTAMP, 
                      duration_seconds = ?, total_deducted = ?, astrologer_share = ?, 
                      rating = ?, review = ? 
                  WHERE id = ?")
       ->execute([$finalDuration, $totalDeducted, $astroShare, $rating, $review, $session['id']]);

    // 4. Free Astrologer to 'available'
    $db->prepare("UPDATE astrologers SET call_status = 'available' WHERE id = ?")->execute([$astroId]);

    $db->commit();

} catch (\Throwable $e) {
    $db->rollBack();
    Response::json(false, 'Consultation settlement error: ' . $e->getMessage(), null, 500);
}

Response::json(true, 'Consultation session completed and settled cleanly.', [
    'session_id'            => $sessionId,
    'status'                => 'completed',
    'duration_seconds'      => $finalDuration,
    'billed_minutes'        => (int)ceil($finalDuration / 60.0),
    'rate_per_min'          => $ratePerMin,
    'total_deducted'        => $totalDeducted,
    'astrologer_share'      => $astroShare,
    'user_remaining_balance'=> $currentUserBalance,
    'ended_by'              => $endedBy
]);
