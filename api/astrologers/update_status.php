<?php
// api/astrologers/update_status.php - Update Astrologer Availability & Call Status
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';

$input = Response::getJsonInput();
$astroId = (int)($input['astrologer_id'] ?? ($_GET['astrologer_id'] ?? ($_SESSION['astrologer_id'] ?? 0)));
$status = trim((string)($input['status'] ?? 'available'));

if ($astroId <= 0) {
    Response::json(false, 'Astrologer ID is required');
}

if (!in_array($status, ['available', 'busy', 'offline'], true)) {
    $status = 'available';
}

$db = getDb();
$stmt = $db->prepare("UPDATE astrologers SET call_status = ? WHERE id = ?");
$stmt->execute([$status, $astroId]);

Response::json(true, "Status updated to {$status}", ['status' => $status]);
