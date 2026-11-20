<?php
// tests/test_video_call_toggle_migration.php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

$db = getDb();
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

echo "Database driver: {$driver}\n";

$hasCol = false;
if ($driver === 'sqlite') {
    $cols = $db->query("PRAGMA table_info(astrologers)")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        if ($c['name'] === 'is_video_enabled') {
            $hasCol = true;
            break;
        }
    }
    if (!$hasCol) {
        $db->exec("ALTER TABLE astrologers ADD COLUMN is_video_enabled INTEGER DEFAULT 1");
        echo "✅ Added is_video_enabled column to SQLite astrologers table.\n";
    } else {
        echo "ℹ️ is_video_enabled column already exists in SQLite.\n";
    }
} else {
    $cols = $db->query("SHOW COLUMNS FROM astrologers LIKE 'is_video_enabled'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE astrologers ADD COLUMN is_video_enabled TINYINT(1) DEFAULT 1 AFTER chat_rate_min");
        echo "✅ Added is_video_enabled column to MySQL astrologers table.\n";
    } else {
        echo "ℹ️ is_video_enabled column already exists in MySQL.\n";
    }
}

// Ensure default value 1 for existing astrologers
$db->exec("UPDATE astrologers SET is_video_enabled = 1 WHERE is_video_enabled IS NULL");
echo "✅ Default video_enabled set to 1 for existing astrologers.\n";

$st = $db->query("SELECT id, name, is_video_enabled, audio_rate_min, video_rate_min FROM astrologers LIMIT 5");
$list = $st->fetchAll(PDO::FETCH_ASSOC);
print_r($list);
