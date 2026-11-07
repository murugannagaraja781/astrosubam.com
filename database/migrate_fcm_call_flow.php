<?php
// database/migrate_fcm_call_flow.php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

try {
    $db = getDb();
    $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    echo "Using DB Driver: {$driver}\n";

    if ($driver === 'mysql') {
        // 1. Add fcm_token to users if not exists
        try {
            $db->exec("ALTER TABLE users ADD COLUMN fcm_token VARCHAR(255) NULL AFTER phone");
            echo "Added fcm_token to users.\n";
        } catch (\PDOException $e) {
            echo "users.fcm_token might already exist: " . $e->getMessage() . "\n";
        }

        // 2. Add fcm_token to astrologers if not exists
        try {
            $db->exec("ALTER TABLE astrologers ADD COLUMN fcm_token VARCHAR(255) NULL AFTER phone");
            echo "Added fcm_token to astrologers.\n";
        } catch (\PDOException $e) {
            echo "astrologers.fcm_token might already exist: " . $e->getMessage() . "\n";
        }

        // 3. Update status ENUM in consultation_sessions
        try {
            $db->exec("ALTER TABLE consultation_sessions 
                       MODIFY COLUMN status ENUM('requested', 'ringing_user', 'active', 'completed', 'cancelled') DEFAULT 'requested'");
            echo "Updated consultation_sessions.status ENUM.\n";
        } catch (\PDOException $e) {
            echo "Note updating status ENUM: " . $e->getMessage() . "\n";
        }

        // 4. Add token columns and user_accepted_at if not exist
        try {
            $db->exec("ALTER TABLE consultation_sessions ADD COLUMN agora_user_token TEXT NULL AFTER agora_channel");
        } catch (\PDOException $e) {}
        try {
            $db->exec("ALTER TABLE consultation_sessions ADD COLUMN agora_astro_token TEXT NULL AFTER agora_user_token");
        } catch (\PDOException $e) {}
        try {
            $db->exec("ALTER TABLE consultation_sessions ADD COLUMN user_accepted_at DATETIME NULL AFTER start_time");
        } catch (\PDOException $e) {}

    } else if ($driver === 'sqlite') {
        // SQLite: check columns with PRAGMA table_info
        $userCols = $db->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array('fcm_token', $userCols)) {
            $db->exec("ALTER TABLE users ADD COLUMN fcm_token TEXT NULL");
            echo "Added fcm_token to users (sqlite).\n";
        }

        $astroCols = $db->query("PRAGMA table_info(astrologers)")->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array('fcm_token', $astroCols)) {
            $db->exec("ALTER TABLE astrologers ADD COLUMN fcm_token TEXT NULL");
            echo "Added fcm_token to astrologers (sqlite).\n";
        }

        $sessionCols = $db->query("PRAGMA table_info(consultation_sessions)")->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array('agora_user_token', $sessionCols)) {
            $db->exec("ALTER TABLE consultation_sessions ADD COLUMN agora_user_token TEXT NULL");
        }
        if (!in_array('agora_astro_token', $sessionCols)) {
            $db->exec("ALTER TABLE consultation_sessions ADD COLUMN agora_astro_token TEXT NULL");
        }
        if (!in_array('user_accepted_at', $sessionCols)) {
            $db->exec("ALTER TABLE consultation_sessions ADD COLUMN user_accepted_at DATETIME NULL");
        }
        echo "SQLite columns updated.\n";
    }

    echo "Migration completed successfully!\n";
} catch (\Throwable $t) {
    echo "Migration error: " . $t->getMessage() . "\n";
}
