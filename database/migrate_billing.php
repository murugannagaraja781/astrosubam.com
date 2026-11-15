<?php
require_once __DIR__ . '/../config/database.php';

$db = getDb();
echo "Migrating DB for RTC & Billing...\n";

// 1. Add wallet_balance to astrologers if not exists
try {
    $db->exec("ALTER TABLE astrologers ADD COLUMN wallet_balance DECIMAL(10,2) DEFAULT 0.00");
    echo "✔ Added wallet_balance to astrologers.\n";
} catch (\Throwable $e) {
    echo "ℹ astrologers.wallet_balance: " . $e->getMessage() . "\n";
}

// 2. Add agora_app_id to system_settings if not exists
try {
    $db->exec("INSERT OR IGNORE INTO system_settings (setting_key, setting_value, description) 
               VALUES ('agora_app_id', '8c0ca8c1b8bc403d814f8f86653e07c3', 'Agora RTC App ID')");
    echo "✔ Ensured agora_app_id in system_settings.\n";
} catch (\Throwable $e) {
    try {
        $db->exec("INSERT IGNORE INTO system_settings (setting_key, setting_value, description) 
                   VALUES ('agora_app_id', '8c0ca8c1b8bc403d814f8f86653e07c3', 'Agora RTC App ID')");
    } catch (\Throwable $e2) {}
}

echo "Migration finished.\n";

