<?php
// database/install.php - One-click Database Setup & Seeder
declare(strict_types=1);

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'astrosubam';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

header('Content-Type: text/html; charset=utf-8');

try {
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Create DB
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("USE `{$dbname}`;");

    // Execute Schema
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($schema);

    // Seed Sample Astrologers
    $checkAstro = $pdo->query("SELECT COUNT(*) FROM astrologers")->fetchColumn();
    if ($checkAstro == 0) {
        $defaultPassword = password_hash('subam123', PASSWORD_BCRYPT);
        $astrologers = [
            [
                'name' => 'Guruji S. Sundaramurthy',
                'phone' => '9876543210',
                'email' => 'sundaramurthy@astrosubam.com',
                'password_hash' => $defaultPassword,
                'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=300&q=80',
                'languages' => 'Tamil, English',
                'specialities' => 'Vedic Astrology, KP System, Marriage Compatibility',
                'experience_years' => 22,
                'audio_rate_min' => 20.00,
                'video_rate_min' => 35.00,
                'chat_rate_min' => 12.00,
                'call_status' => 'available',
                'rating' => 4.95,
                'total_reviews' => 450
            ],
            [
                'name' => 'Dr. K. Jayalakshmi (Gold Medalist)',
                'phone' => '9876543211',
                'email' => 'jayalakshmi@astrosubam.com',
                'password_hash' => $defaultPassword,
                'avatar' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=300&q=80',
                'languages' => 'Tamil, English, Telugu',
                'specialities' => 'Nadi Astrology, Career & Business Astro, Gemology',
                'experience_years' => 16,
                'audio_rate_min' => 18.00,
                'video_rate_min' => 30.00,
                'chat_rate_min' => 10.00,
                'call_status' => 'available',
                'rating' => 4.88,
                'total_reviews' => 312
            ],
            [
                'name' => 'Acharya Ramanan Sarma',
                'phone' => '9876543212',
                'email' => 'ramanan@astrosubam.com',
                'password_hash' => $defaultPassword,
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80',
                'languages' => 'Tamil, Sanskrit, Hindi',
                'specialities' => 'Prasanna Jyothidam, Dosha Nivarana, Vasthu Sastra',
                'experience_years' => 14,
                'audio_rate_min' => 15.00,
                'video_rate_min' => 25.00,
                'chat_rate_min' => 10.00,
                'call_status' => 'available',
                'rating' => 4.92,
                'total_reviews' => 280
            ]
        ];

        $ins = $pdo->prepare("INSERT INTO astrologers 
            (name, phone, email, password_hash, avatar, languages, specialities, experience_years, audio_rate_min, video_rate_min, chat_rate_min, call_status, rating, total_reviews) 
            VALUES (:name, :phone, :email, :password_hash, :avatar, :languages, :specialities, :experience_years, :audio_rate_min, :video_rate_min, :chat_rate_min, :call_status, :rating, :total_reviews)");
        
        foreach ($astrologers as $astro) {
            $ins->execute($astro);
        }
    }

    // Seed Demo User
    $checkUser = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($checkUser == 0) {
        $userPass = password_hash('client123', PASSWORD_BCRYPT);
        $pdo->prepare("INSERT INTO users (name, phone, email, password_hash, gender, wallet_balance) VALUES (?, ?, ?, ?, 'Male', 500.00)")
            ->execute(['AstroSubam Dev User', '9998887770', 'client@astrosubam.com', $userPass]);
    }

    echo "<h1 style='color:green;font-family:sans-serif;'>✅ AstroSubam Database Installed Successfully!</h1>";
    echo "<p style='font-family:sans-serif;'>Database: <b>astrosubam</b> | Host: <b>{$host}:{$port}</b></p>";
    echo "<p style='font-family:sans-serif;'>Tables created: users, astrologers, consultation_sessions, wallet_transactions, chat_messages</p>";
    echo "<p style='font-family:sans-serif;'>Demo Astrologers & Demo User (Balance: ₹500) seeded!</p>";
} catch (Exception $e) {
    echo "<h1 style='color:red;font-family:sans-serif;'>❌ Installation Error</h1>";
    echo "<p style='font-family:sans-serif;'>" . htmlspecialchars($e->getMessage()) . "</p>";
}
