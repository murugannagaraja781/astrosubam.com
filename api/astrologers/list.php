<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';

$db = getDb();
$status = $_GET['status'] ?? 'all'; // available | all
$search = $_GET['search'] ?? '';

$sql = "SELECT id, name, avatar, languages, specialities, experience_years, audio_rate_min, video_rate_min, chat_rate_min, call_status, rating, total_reviews 
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
$astrologers = $stmt->fetchAll();

Response::json(true, 'Astrologers fetched successfully.', [
    'count'       => count($astrologers),
    'astrologers' => $astrologers
]);
