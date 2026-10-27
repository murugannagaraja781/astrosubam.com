<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    Response::json(false, 'Valid Astrologer ID required.', null, 400);
}

$db = getDb();
$stmt = $db->prepare("SELECT id, name, avatar, languages, specialities, experience_years, audio_rate_min, video_rate_min, chat_rate_min, call_status, rating, total_reviews, created_at FROM astrologers WHERE id = ?");
$stmt->execute([$id]);
$astrologer = $stmt->fetch();

if (!$astrologer) {
    Response::json(false, 'Astrologer not found.', null, 404);
}

Response::json(true, 'Astrologer profile fetched.', [
    'astrologer' => $astrologer
]);
