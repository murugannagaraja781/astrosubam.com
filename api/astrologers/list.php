<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';

$db = getDb();
$status = $_GET['status'] ?? 'all'; // available | all
$search = $_GET['search'] ?? '';

$sql = "SELECT id, name, avatar, languages, specialities, experience_years, audio_rate_min, video_rate_min, chat_rate_min, is_video_enabled, call_status, rating, total_reviews 
        FROM astrologers WHERE status = 'approved'";
$params = [];

if ($status === 'available') {
    $sql .= " AND call_status = 'available'";
}

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR specialities LIKE ? OR languages LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$sql .= " ORDER BY call_status = 'available' DESC, rating DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$formatted = [];
foreach ($rows as $a) {
    $formatted[] = [
        'id'               => (int)$a['id'],
        'name'             => (string)$a['name'],
        'avatar'           => (string)($a['avatar'] ?? 'assets/logo.jpg'),
        'languages'        => (string)($a['languages'] ?? 'Tamil, English'),
        'specialities'     => (string)($a['specialities'] ?? 'Vedic Astrology'),
        'experience_years' => (int)($a['experience_years'] ?? 10),
        'audio_rate_min'   => (float)($a['audio_rate_min'] ?? 20.0),
        'video_rate_min'   => (float)($a['video_rate_min'] ?? 30.0),
        'chat_rate_min'    => (float)($a['chat_rate_min'] ?? 10.0),
        'is_video_enabled' => (bool)((int)($a['is_video_enabled'] ?? 1) === 1),
        'call_status'      => strtolower(trim((string)($a['call_status'] ?? 'offline'))),
        'rating'           => (float)($a['rating'] ?? 4.9),
        'total_reviews'    => (int)($a['total_reviews'] ?? 100),
    ];
}

Response::json(true, 'Astrologers fetched successfully.', [
    'count'       => count($formatted),
    'astrologers' => $formatted
]);
