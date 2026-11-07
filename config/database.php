<?php
// config/database.php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/Env.php';
Env::load();

$host = Env::get('DB_HOST', '127.0.0.1');
$port = Env::get('DB_PORT', '3306');
$dbname = Env::get('DB_NAME', 'astrosubam');
$user = Env::get('DB_USER', 'root');
$pass = Env::get('DB_PASS', '');

$pdo = null;
$dbError = null;

try {
    $pdo = null;
    $hostsToTry = array_unique([$host, 'localhost', '127.0.0.1']);
    $lastErr = null;

    foreach ($hostsToTry as $h) {
        try {
            $dsn = "mysql:host={$h};port={$port};dbname={$dbname};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 1,
            ]);
            $dbError = null;
            break;
        } catch (PDOException $ex) {
            $lastErr = $ex->getMessage();
        }
    }

    if ($pdo === null) {
        if ($user !== 'root') {
            try {
                $dsnFallback = "mysql:host=127.0.0.1;port=3306;dbname=astrosubam;charset=utf8mb4";
                $pdo = new PDO($dsnFallback, 'root', '', [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_TIMEOUT            => 1,
                ]);
                $dbError = null;
            } catch (PDOException $e2) {
                $dbError = $lastErr ?? $e2->getMessage();
            }
        } else {
            $dbError = $lastErr;
        }

        // Local dev zero-downtime fallback: SQLite
        if ($pdo === null) {
            try {
                $sqliteDir = __DIR__ . '/../database';
                if (!is_dir($sqliteDir)) {
                    @mkdir($sqliteDir, 0777, true);
                }
                $sqlitePath = $sqliteDir . '/astrosubam_dev.sqlite';
                $pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
                $dbError = null;

                // Ensure tables exist in SQLite
                $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
                    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                    `name` TEXT NOT NULL,
                    `phone` TEXT UNIQUE NOT NULL,
                    `email` TEXT,
                    `password_hash` TEXT NOT NULL,
                    `gender` TEXT DEFAULT 'Male',
                    `dob` TEXT NULL,
                    `tob` TEXT NULL,
                    `pob` TEXT NULL,
                    `wallet_balance` REAL DEFAULT 500.00,
                    `role` TEXT DEFAULT 'client',
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
                )");

                $pdo->exec("CREATE TABLE IF NOT EXISTS `astrologers` (
                    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                    `name` TEXT NOT NULL,
                    `phone` TEXT UNIQUE NOT NULL,
                    `email` TEXT,
                    `password_hash` TEXT NOT NULL,
                    `avatar` TEXT DEFAULT 'assets/logo.jpg',
                    `languages` TEXT DEFAULT 'Tamil, English',
                    `specialities` TEXT DEFAULT 'Vedic Astrology, KP System',
                    `experience_years` INTEGER DEFAULT 20,
                    `audio_rate_min` REAL DEFAULT 20.00,
                    `video_rate_min` REAL DEFAULT 35.00,
                    `chat_rate_min` REAL DEFAULT 12.00,
                    `call_status` TEXT DEFAULT 'available',
                    `rating` REAL DEFAULT 4.95,
                    `total_reviews` INTEGER DEFAULT 450,
                    `status` TEXT DEFAULT 'approved',
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
                )");

                $pdo->exec("CREATE TABLE IF NOT EXISTS `consultation_sessions` (
                    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                    `session_id` TEXT UNIQUE NOT NULL,
                    `user_id` INTEGER NOT NULL,
                    `astrologer_id` INTEGER NOT NULL,
                    `type` TEXT NOT NULL,
                    `status` TEXT DEFAULT 'requested',
                    `agora_channel` TEXT NOT NULL,
                    `start_time` DATETIME NULL,
                    `end_time` DATETIME NULL,
                    `duration_seconds` INTEGER DEFAULT 0,
                    `rate_per_min` REAL NOT NULL,
                    `total_deducted` REAL DEFAULT 0.00,
                    `astrologer_share` REAL DEFAULT 0.00,
                    `rating` INTEGER DEFAULT NULL,
                    `review` TEXT NULL,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
                )");

                $pdo->exec("CREATE TABLE IF NOT EXISTS `wallet_transactions` (
                    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                    `user_id` INTEGER NOT NULL,
                    `type` TEXT NOT NULL,
                    `amount` REAL NOT NULL,
                    `balance_after` REAL NOT NULL,
                    `description` TEXT NOT NULL,
                    `reference_id` TEXT NULL,
                    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
                )");

                // Seed test user and astrologer if empty
                $cnt = $pdo->query("SELECT COUNT(*) FROM astrologers")->fetchColumn();
                if ($cnt == 0) {
                    $defPass = password_hash('subam123', PASSWORD_BCRYPT);
                    $pdo->exec("INSERT INTO astrologers (id, name, phone, email, password_hash, avatar, languages, specialities, experience_years, audio_rate_min, video_rate_min, chat_rate_min, call_status, rating, total_reviews)
                        VALUES (1, 'Guruji S. Sundaramurthy', '9876543210', 'sundaramurthy@astrosubam.com', '{$defPass}', 'assets/logo.jpg', 'Tamil, English', 'Vedic Astrology, KP System', 22, 20.00, 35.00, 12.00, 'available', 4.95, 450)");
                    $pdo->exec("INSERT INTO astrologers (id, name, phone, email, password_hash, avatar, languages, specialities, experience_years, audio_rate_min, video_rate_min, chat_rate_min, call_status, rating, total_reviews)
                        VALUES (2, 'Dr. K. Jayalakshmi', '9876543211', 'jayalakshmi@astrosubam.com', '{$defPass}', 'assets/logo.jpg', 'Tamil, English, Telugu', 'Nadi Astrology, Gemology', 16, 18.00, 30.00, 10.00, 'available', 4.88, 312)");
                }

                $uCnt = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
                if ($uCnt == 0) {
                    $defPass = password_hash('subam123', PASSWORD_BCRYPT);
                    $pdo->exec("INSERT INTO users (id, name, phone, email, password_hash, wallet_balance, role)
                        VALUES (1, 'Super Admin', '9000000001', 'admin@astrosubam.com', '{$defPass}', 1000.00, 'admin')");
                    $pdo->exec("INSERT INTO users (id, name, phone, email, password_hash, wallet_balance, role)
                        VALUES (2, 'பக்தர் (User 1)', '8000000001', 'devotee1@astrosubam.com', '{$defPass}', 500.00, 'client')");
                }
            } catch (\Throwable $e3) {
                $dbError = $lastErr ?? $e3->getMessage();
            }
        }
    }
} catch (\Throwable $t) {
    $pdo = null;
    $dbError = $t->getMessage();
}

function getDb(): PDO {
    global $pdo, $dbError;
    if ($pdo === null) {
        throw new RuntimeException("Database connection failed. " . ($dbError ?? 'Please verify database settings.'));
    }
    return $pdo;
}
