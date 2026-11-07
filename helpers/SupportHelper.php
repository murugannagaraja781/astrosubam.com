<?php
// helpers/SupportHelper.php
// SageTalkz Workflow 8: Customer Support & Ticketing Lifecycle Engine
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class SupportHelper {
    public const VALID_CATEGORIES = [
        'Wallet & Payments',
        'Call Drop / Audio Issue',
        'Astrologer Consultation',
        'Kundali / Birth Chart',
        'Profile & Login',
        'General Inquiry'
    ];

    public const STATUS_WAITING = 'WAITING'; // Customer waiting for agent response (Blue)
    public const STATUS_OPEN    = 'OPEN';    // Under review / Active conversation (Green)
    public const STATUS_CLOSED  = 'CLOSED';  // Resolved & Closed (Red)

    public static function ensureTables(PDO $db): void {
        $driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $db->exec("CREATE TABLE IF NOT EXISTS `support_tickets` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `ticket_number` TEXT UNIQUE NOT NULL,
                `user_id` INTEGER NOT NULL,
                `user_phone` TEXT NOT NULL,
                `category` TEXT NOT NULL,
                `subject` TEXT NOT NULL,
                `description` TEXT NOT NULL,
                `status` TEXT DEFAULT 'WAITING',
                `priority` TEXT DEFAULT 'MEDIUM',
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            )");

            $db->exec("CREATE TABLE IF NOT EXISTS `support_ticket_messages` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `ticket_id` INTEGER NOT NULL,
                `sender_type` TEXT NOT NULL,
                `sender_name` TEXT NOT NULL,
                `message` TEXT NOT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
        } else {
            // MySQL Production
            $db->exec("CREATE TABLE IF NOT EXISTS `support_tickets` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `ticket_number` VARCHAR(32) UNIQUE NOT NULL,
                `user_id` INT NOT NULL,
                `user_phone` VARCHAR(20) NOT NULL,
                `category` VARCHAR(100) NOT NULL,
                `subject` VARCHAR(255) NOT NULL,
                `description` TEXT NOT NULL,
                `status` ENUM('WAITING', 'OPEN', 'CLOSED') DEFAULT 'WAITING',
                `priority` ENUM('LOW', 'MEDIUM', 'HIGH') DEFAULT 'MEDIUM',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX (`user_id`),
                INDEX (`status`)
            ) ENGINE=InnoDB");

            $db->exec("CREATE TABLE IF NOT EXISTS `support_ticket_messages` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `ticket_id` INT NOT NULL,
                `sender_type` ENUM('user', 'support', 'admin') NOT NULL,
                `sender_name` VARCHAR(120) NOT NULL,
                `message` TEXT NOT NULL,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (`ticket_id`),
                FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB");
        }
    }

    public static function createTicket(
        PDO $db,
        int $userId,
        string $userPhone,
        string $category,
        string $description,
        string $subject = ''
    ): array {
        self::ensureTables($db);

        $descLen = mb_strlen(trim($description), 'UTF-8');
        if ($descLen < 30 || $descLen > 400) {
            throw new InvalidArgumentException("Description must be between 30 and 400 characters (Current: {$descLen}).");
        }

        if (empty($subject)) {
            $subject = $category . ' - ' . mb_substr($description, 0, 35) . '...';
        }

        $ticketNumber = 'TKT-' . strtoupper(substr(uniqid(), -6));

        $stmt = $db->prepare("INSERT INTO support_tickets 
            (ticket_number, user_id, user_phone, category, subject, description, status, priority) 
            VALUES (?, ?, ?, ?, ?, ?, 'WAITING', 'MEDIUM')");
        $stmt->execute([$ticketNumber, $userId, $userPhone, $category, $subject, $description]);
        $ticketId = (int)$db->lastInsertId();

        // Initial user query message
        $msgStmt = $db->prepare("INSERT INTO support_ticket_messages (ticket_id, sender_type, sender_name, message) VALUES (?, 'user', 'Devotee', ?)");
        $msgStmt->execute([$ticketId, $description]);

        // Auto greeting from AstroSubam Support
        $botStmt = $db->prepare("INSERT INTO support_ticket_messages (ticket_id, sender_type, sender_name, message) VALUES (?, 'support', 'AstroSubam Support Desk', ?)");
        $botGreeting = "வணக்கம்! Your query regarding '{$category}' has been assigned Ticket ID {$ticketNumber}. Our support team will assist you shortly.";
        $botStmt->execute([$ticketId, $botGreeting]);

        return [
            'ticket_id'     => $ticketId,
            'ticket_number' => $ticketNumber,
            'category'      => $category,
            'subject'       => $subject,
            'description'   => $description,
            'status'        => self::STATUS_WAITING,
            'created_at'    => date('Y-m-d H:i:s')
        ];
    }
}

