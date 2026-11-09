<?php
// api/astrologers/dashboard.php - Real-Time Astrologer Dashboard Metrics & Live Queue
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';

$input = Response::getJsonInput();
$astroId = (int)($input['astrologer_id'] ?? ($_GET['astrologer_id'] ?? ($_SESSION['astrologer_id'] ?? 0)));
$astroPhone = trim((string)($input['phone'] ?? ($_GET['phone'] ?? ($_SESSION['astrologer_phone'] ?? ''))));

$db = getDb();
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

if ($astroId <= 0 && !empty($astroPhone)) {
    $aStmt = $db->prepare("SELECT id FROM astrologers WHERE phone = ?");
    $aStmt->execute([$astroPhone]);
    $astroId = (int)$aStmt->fetchColumn();
}

if ($astroId <= 0) {
    // Default to first astrologer in DB
    $fStmt = $db->query("SELECT id FROM astrologers ORDER BY id ASC LIMIT 1");
    $astroId = (int)($fStmt->fetchColumn() ?: 1);
}

// 1. Fetch Astrologer Profile
$stmt = $db->prepare("SELECT id, name, phone, email, call_status, audio_rate_min, video_rate_min, chat_rate_min, rating, total_reviews 
                      FROM astrologers WHERE id = ?");
$stmt->execute([$astroId]);
$astrologer = $stmt->fetch();

if (!$astrologer) {
    Response::json(false, 'Astrologer profile not found');
}

// 2. Calculate Today's Earnings (Strictly real data from completed sessions today)
if ($driver === 'sqlite') {
    $teStmt = $db->prepare("SELECT COALESCE(SUM(astrologer_share), 0) FROM consultation_sessions 
                            WHERE astrologer_id = ? AND status = 'completed' AND DATE(created_at) = DATE('now')");
} else {
    $teStmt = $db->prepare("SELECT COALESCE(SUM(astrologer_share), 0) FROM consultation_sessions 
                            WHERE astrologer_id = ? AND status = 'completed' AND DATE(created_at) = CURRENT_DATE");
}
$teStmt->execute([$astroId]);
$todayEarnings = (float)$teStmt->fetchColumn();

// 3. Calculate Withdrawable Wallet Balance & Total Completed Consultations
$totStmt = $db->prepare("SELECT COALESCE(SUM(astrologer_share), 0) as total_earned, COUNT(*) as total_count 
                         FROM consultation_sessions 
                         WHERE astrologer_id = ? AND status = 'completed'");
$totStmt->execute([$astroId]);
$totRow = $totStmt->fetch();
$walletBalance = (float)($totRow['total_earned'] ?? 0.0);
$totalConsultations = (int)($totRow['total_count'] ?? 0);

// 4. Fetch Real Pending Queue (Strictly real user requests, NO dummy data)
$qStmt = $db->prepare("SELECT s.id, s.session_id, s.user_id, s.type, s.rate_per_min, s.created_at, s.status, s.agora_channel,
                              COALESCE(u.name, 'Devotee') as user_name,
                              COALESCE(u.phone, '') as user_phone
                       FROM consultation_sessions s
                       LEFT JOIN users u ON s.user_id = u.id
                       WHERE s.astrologer_id = ? AND s.status = 'requested'
                       ORDER BY s.id ASC");
$qStmt->execute([$astroId]);
$queueRows = $qStmt->fetchAll();

$pendingQueue = [];
$now = time();

foreach ($queueRows as $row) {
    $createdStr = (string)$row['created_at'];
    $parsedTime = !empty($createdStr) ? strtotime($createdStr . ' UTC') : $now;
    if ($parsedTime === false || $parsedTime <= 0) $parsedTime = $now;
    $waitSecs = max(0, $now - $parsedTime);
    $waitMins = (int)ceil($waitSecs / 60);

    $pendingQueue[] = [
        'id' => (int)$row['id'],
        'session_id' => $row['session_id'],
        'user_id' => (int)$row['user_id'],
        'user_name' => $row['user_name'],
        'user_phone' => $row['user_phone'],
        'type' => $row['type'],
        'rate_per_min' => (float)$row['rate_per_min'],
        'created_at' => $row['created_at'],
        'wait_seconds' => $waitSecs,
        'wait_label' => $waitMins <= 1 ? 'Just now' : "{$waitMins} mins",
        'status' => $row['status'],
        'channel_name' => $row['agora_channel'],
    ];
}

Response::json(true, 'Astrologer dashboard loaded successfully', [
    'astrologer' => [
        'id' => (int)$astrologer['id'],
        'name' => $astrologer['name'],
        'phone' => $astrologer['phone'],
        'call_status' => $astrologer['call_status'] ?? 'available',
        'audio_rate_min' => (float)$astrologer['audio_rate_min'],
        'video_rate_min' => (float)$astrologer['video_rate_min'],
        'chat_rate_min' => (float)$astrologer['chat_rate_min'],
        'rating' => (float)($astrologer['rating'] ?? 5.0),
        'total_reviews' => (int)($astrologer['total_reviews'] ?? 0),
    ],
    'today_earnings' => $todayEarnings,
    'wallet_balance' => $walletBalance,
    'total_consultations' => $totalConsultations,
    'pending_queue' => $pendingQueue,
    'waiting_count' => count($pendingQueue),
    'has_incoming' => count($pendingQueue) > 0,
    'latest_session' => count($pendingQueue) > 0 ? $pendingQueue[0] : null,
]);
