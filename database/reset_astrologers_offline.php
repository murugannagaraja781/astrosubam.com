<?php
// database/reset_astrologers_offline.php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

$db = getDb();
$db->exec("UPDATE astrologers SET call_status = 'offline'");
echo "All astrologers set to offline successfully!\n";

$rows = $db->query("SELECT id, name, call_status FROM astrologers")->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);

