<?php
// api/auth/update_fcm_token.php - Register or update user/astrologer device FCM token
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/JWT.php';

$input = Response::getJsonInput();
$user = JWT::getAuthUser();

$userId = (int)($input['user_id'] ?? ($user['user_id'] ?? 0));
$astroId = (int)($input['astrologer_id'] ?? 0);
$fcmToken = trim($input['fcm_token'] ?? '');

if (empty($fcmToken)) {
    Response::json(false, 'fcm_token is required.', null, 400);
}

$db = getDb();

if ($astroId > 0) {
    $stmt = $db->prepare("UPDATE astrologers SET fcm_token = ? WHERE id = ?");
    $stmt->execute([$fcmToken, $astroId]);
    Response::json(true, 'Astrologer FCM token updated.', ['astrologer_id' => $astroId]);
} else if ($userId > 0) {
    $stmt = $db->prepare("UPDATE users SET fcm_token = ? WHERE id = ?");
    $stmt->execute([$fcmToken, $userId]);
    Response::json(true, 'Devotee FCM token updated.', ['user_id' => $userId]);
} else {
    Response::json(false, 'user_id or astrologer_id is required.', null, 400);
}
