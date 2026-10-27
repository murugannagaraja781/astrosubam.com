-- AstroSubam Database Schema
CREATE DATABASE IF NOT EXISTS `astrosubam` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `astrosubam`;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(120) NOT NULL,
    `phone` VARCHAR(20) UNIQUE NOT NULL,
    `email` VARCHAR(150) UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `gender` ENUM('Male', 'Female', 'Other') DEFAULT 'Male',
    `dob` DATE NULL,
    `tob` TIME NULL,
    `pob` VARCHAR(150) NULL,
    `wallet_balance` DECIMAL(10, 2) DEFAULT 100.00,
    `role` ENUM('client', 'admin') DEFAULT 'client',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Astrologers Table
CREATE TABLE IF NOT EXISTS `astrologers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(120) NOT NULL,
    `phone` VARCHAR(20) UNIQUE NOT NULL,
    `email` VARCHAR(150) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `avatar` VARCHAR(255) DEFAULT 'assets/astrologers/astro_default.png',
    `languages` VARCHAR(255) DEFAULT 'Tamil, English',
    `specialities` VARCHAR(255) DEFAULT 'Vedic Astrology, KP System, Prasanna Jyothidam',
    `experience_years` INT DEFAULT 8,
    `audio_rate_min` DECIMAL(8, 2) DEFAULT 15.00,
    `video_rate_min` DECIMAL(8, 2) DEFAULT 25.00,
    `chat_rate_min` DECIMAL(8, 2) DEFAULT 10.00,
    `call_status` ENUM('available', 'busy', 'offline') DEFAULT 'available',
    `rating` DECIMAL(3, 2) DEFAULT 4.90,
    `total_reviews` INT DEFAULT 128,
    `status` ENUM('pending', 'approved', 'suspended') DEFAULT 'approved',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Consultation Sessions Table
CREATE TABLE IF NOT EXISTS `consultation_sessions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `session_id` VARCHAR(64) UNIQUE NOT NULL,
    `user_id` INT NOT NULL,
    `astrologer_id` INT NOT NULL,
    `type` ENUM('audio', 'video', 'chat') NOT NULL,
    `status` ENUM('requested', 'active', 'completed', 'cancelled') DEFAULT 'requested',
    `agora_channel` VARCHAR(100) NOT NULL,
    `start_time` DATETIME NULL,
    `end_time` DATETIME NULL,
    `duration_seconds` INT DEFAULT 0,
    `rate_per_min` DECIMAL(8, 2) NOT NULL,
    `total_deducted` DECIMAL(10, 2) DEFAULT 0.00,
    `astrologer_share` DECIMAL(10, 2) DEFAULT 0.00,
    `rating` INT DEFAULT NULL,
    `review` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`astrologer_id`) REFERENCES `astrologers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Wallet Transactions Table
CREATE TABLE IF NOT EXISTS `wallet_transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `type` ENUM('credit', 'debit') NOT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `balance_after` DECIMAL(10, 2) NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `reference_id` VARCHAR(100) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Chat Messages Table
CREATE TABLE IF NOT EXISTS `chat_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `session_id` VARCHAR(64) NOT NULL,
    `sender_type` ENUM('user', 'astrologer') NOT NULL,
    `sender_id` INT NOT NULL,
    `message_type` ENUM('text', 'image', 'audio', 'kundli') DEFAULT 'text',
    `content` TEXT NOT NULL,
    `file_url` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (`session_id`)
) ENGINE=InnoDB;

-- 6. System Settings Table (Super Admin Configurable)
CREATE TABLE IF NOT EXISTS `system_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) UNIQUE NOT NULL,
    `setting_value` TEXT NOT NULL,
    `description` VARCHAR(255) NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
