<?php
// helpers/Response.php
declare(strict_types=1);

class Response {
    public static function json(bool $success, string $message, $data = null, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            exit(0);
        }

        echo json_encode([
            'success'   => $success,
            'ok'        => $success,
            'message'   => $message,
            'data'      => $data,
            'timestamp' => time()
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function getJsonInput(): array {
        if (!empty($_POST)) {
            return $_POST;
        }
        if (php_sapi_name() === 'cli' && empty($_SERVER['HTTP_HOST'])) {
            return [];
        }
        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
