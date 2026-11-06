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
                ]);
                $dbError = null;
            } catch (PDOException $e2) {
                $dbError = $lastErr ?? $e2->getMessage();
            }
        } else {
            $dbError = $lastErr;
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
