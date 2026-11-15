<?php
require_once __DIR__ . '/../config/database.php';
$db = getDb();
$stmt = $db->query("SELECT id, session_id, user_id, astrologer_id, type, status, agora_channel, LENGTH(agora_user_token) as u_tok_len, LENGTH(agora_astro_token) as a_tok_len, created_at FROM consultation_sessions ORDER BY id DESC LIMIT 5");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($rows, JSON_PRETTY_PRINT);

