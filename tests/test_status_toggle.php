<?php
$ch = curl_init('https://subam.navanagari.com/api/astrologers/update_status.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['astrologer_id' => 1, 'status' => 'available']));
$res = curl_exec($ch);
curl_close($ch);
echo "UPDATE STATUS RESULT: " . $res . "\n";

$list = file_get_contents('https://subam.navanagari.com/api/astrologers/list.php');
echo "LIST RESULT: " . $list . "\n";

