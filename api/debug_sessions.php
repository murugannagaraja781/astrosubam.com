<?php
// api/debug_sessions.php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/Response.php';

$db = getDb();
$stmt = $db->query("SELECT id, session_id, user_id, astrologer_id, type, status, agora_channel, 
                           created_at, start_time, end_time, duration_seconds, total_deducted,
                           CASE WHEN agora_user_token IS NOT NULL AND LENGTH(agora_user_token) > 0 THEN 1 ELSE 0 END as has_user_token,
                           CASE WHEN agora_astro_token IS NOT NULL AND LENGTH(agora_astro_token) > 0 THEN 1 ELSE 0 END as has_astro_token
                    FROM consultation_sessions 
                    ORDER BY id DESC LIMIT 10");
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$errorLogLines = [];
$candidates = [
    __DIR__ . '/../error_log',
    __DIR__ . '/../../error_log',
    ini_get('error_log')
];
foreach ($candidates as $cand) {
    if (!empty($cand) && file_exists($cand) && is_readable($cand)) {
        $lines = file($cand);
        $errorLogLines = array_slice($lines, -30);
        break;
    }
}

$config = require __DIR__ . '/../config/agora.php';

$s80Stmt = $db->prepare("SELECT * FROM consultation_sessions ORDER BY id DESC LIMIT 1");
$s80Stmt->execute();
$latestDetail = $s80Stmt->fetch(PDO::FETCH_ASSOC);

Response::json(true, 'Recent sessions', [
    'sessions' => $sessions,
    'latest_session_detail' => $latestDetail,
    'agora_config' => [
        'app_id' => $config['app_id'],
        'app_cert_len' => strlen($config['app_certificate']),
        'app_cert_prefix' => substr($config['app_certificate'], 0, 4) . '...'
    ],
    'error_log_tail' => $errorLogLines
]);

