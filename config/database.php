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
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    $pdo = null;
    $dbError = $e->getMessage();
}

function getDb(): PDO {
    global $pdo, $dbError;
    if ($pdo === null) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed. Please run database/install.php first to initialize MySQL.',
            'error'   => $dbError ?? 'Unknown error'
        ]);
        exit;
    }
    return $pdo;
}
