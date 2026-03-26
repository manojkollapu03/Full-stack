-- ============================================================
--  TrackWise — MySQL Database Schema
--  Import this file via phpMyAdmin:
--  Go to phpMyAdmin → Import → Choose File → Select this file
-- ============================================================

CREATE DATABASE IF NOT EXISTS `trackwise_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `trackwise_db`;

-- ─── USERS TABLE ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `users` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `full_name`     VARCHAR(120)    NOT NULL,
    `email`         VARCHAR(180)    NOT NULL UNIQUE,
    `password_hash` VARCHAR(255)    NOT NULL,
    `avatar_initials` VARCHAR(4)   DEFAULT NULL,
    `currency`      VARCHAR(5)      NOT NULL DEFAULT 'USD',
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─── CATEGORIES TABLE ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `categories` (
    `id`        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`   INT UNSIGNED    NOT NULL,
    `name`      VARCHAR(80)     NOT NULL,
    `icon`      VARCHAR(10)     DEFAULT '📌',
    `color`     VARCHAR(10)     DEFAULT '#94a3b8',
    `type`      ENUM('expense','income','both') NOT NULL DEFAULT 'expense',
    `created_at` DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_categories` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─── TRANSACTIONS TABLE ──────────────────────────────────────
CREATE TABLE IF NOT EXISTS `transactions` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`       INT UNSIGNED    NOT NULL,
    `description`   VARCHAR(255)    NOT NULL,
    `category`      VARCHAR(80)     NOT NULL DEFAULT 'Others',
    `amount`        DECIMAL(12,2)   NOT NULL COMMENT 'Positive=income, Negative=expense',
    `type`          ENUM('income','expense') NOT NULL DEFAULT 'expense',
    `payment_method` VARCHAR(60)    NOT NULL DEFAULT 'Card',
    `tx_date`       DATE            NOT NULL,
    `notes`         TEXT            DEFAULT NULL,
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_transactions` (`user_id`),
    INDEX `idx_tx_date`           (`tx_date`),
    INDEX `idx_tx_category`       (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─── BUDGETS TABLE ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `budgets` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED    NOT NULL,
    `category`    VARCHAR(80)     NOT NULL,
    `amount`      DECIMAL(12,2)   NOT NULL,
    `month`       TINYINT(2)      NOT NULL COMMENT '1-12',
    `year`        SMALLINT(4)     NOT NULL,
    `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_budget` (`user_id`, `category`, `month`, `year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ─── SEED: DEFAULT CATEGORIES (system-level, user_id=0) ─────
-- These are inserted per user on registration via PHP, so this is just reference.
-- See register.php for the seeding logic.


-- ─── SAMPLE VIEW: Monthly Summary ───────────────────────────
CREATE OR REPLACE VIEW `v_monthly_summary` AS
SELECT
    user_id,
    YEAR(tx_date)  AS yr,
    MONTH(tx_date) AS mo,
    SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) AS total_income,
    SUM(CASE WHEN type='expense' THEN ABS(amount) ELSE 0 END) AS total_expenses,
    COUNT(*) AS tx_count
FROM `transactions`
GROUP BY user_id, yr, mo;
