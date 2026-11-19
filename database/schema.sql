-- ============================================================
-- Проєкт «МійДім ОСББ»
-- База даних: MySQL 8.0+ / MariaDB 10.4+
-- Таблиці, зовнішні зв'язки, індекси
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Таблиця будинків (базовий запис нашого будинку)
CREATE TABLE IF NOT EXISTS `buildings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL DEFAULT 'ОСББ Затишний Дім',
    `address` VARCHAR(255) NOT NULL,
    `edrpou` VARCHAR(10) NOT NULL,
    `iban` VARCHAR(34) NOT NULL,
    `bank_name` VARCHAR(100) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Квартири
CREATE TABLE IF NOT EXISTS `apartments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `building_id` INT NOT NULL DEFAULT 1,
    `number` INT NOT NULL,
    `entrance` INT NOT NULL DEFAULT 1,
    `floor` INT NOT NULL DEFAULT 1,
    `area_m2` DECIMAL(7, 2) NOT NULL,
    `rooms` INT NOT NULL DEFAULT 1,
    `residents_count` INT NOT NULL DEFAULT 1,
    `account_number` VARCHAR(30) UNIQUE NOT NULL,
    `notes` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_apt_number` (`number`),
    INDEX `idx_apt_account` (`account_number`),
    CONSTRAINT `fk_apt_building` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Користувачі системи
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(150) UNIQUE NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'accountant', 'master', 'resident') NOT NULL DEFAULT 'resident',
    `full_name` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(25) NOT NULL,
    `telegram_chat_id` BIGINT NULL,
    `telegram_username` VARCHAR(50) NULL,
    `telegram_token` VARCHAR(64) UNIQUE NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_login` DATETIME NULL,
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_tg_chat` (`telegram_chat_id`),
    INDEX `idx_users_tg_token` (`telegram_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Зв'язок користувачів із квартирами (Мешканці)
CREATE TABLE IF NOT EXISTS `residents` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `apartment_id` INT NOT NULL,
    `is_owner` TINYINT(1) NOT NULL DEFAULT 1,
    `ownership_share` DECIMAL(5, 2) NOT NULL DEFAULT 100.00,
    `is_primary_contact` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_res_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_res_apt` FOREIGN KEY (`apartment_id`) REFERENCES `apartments` (`id`) ON DELETE CASCADE,
    INDEX `idx_res_user` (`user_id`),
    INDEX `idx_res_apt` (`apartment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Типи лічильників
CREATE TABLE IF NOT EXISTS `meter_types` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(30) UNIQUE NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `unit` VARCHAR(20) NOT NULL DEFAULT 'м³',
    `icon` VARCHAR(50) NOT NULL DEFAULT 'droplet',
    `default_tariff` DECIMAL(10, 2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Лічильники, закріплені за квартирою
CREATE TABLE IF NOT EXISTS `apartment_meters` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `apartment_id` INT NOT NULL,
    `meter_type_id` INT NOT NULL,
    `serial_number` VARCHAR(50) NOT NULL,
    `initial_reading` DECIMAL(10, 3) NOT NULL DEFAULT 0.000,
    `installed_at` DATE NULL,
    `next_verification_date` DATE NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT `fk_am_apt` FOREIGN KEY (`apartment_id`) REFERENCES `apartments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_am_type` FOREIGN KEY (`meter_type_id`) REFERENCES `meter_types` (`id`) ON DELETE CASCADE,
    INDEX `idx_am_apt` (`apartment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Показники лічильників
CREATE TABLE IF NOT EXISTS `meter_readings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `apartment_meter_id` INT NOT NULL,
    `reading_value` DECIMAL(10, 3) NOT NULL,
    `consumption` DECIMAL(10, 3) NOT NULL DEFAULT 0.000,
    `reading_date` DATE NOT NULL,
    `submitted_by` INT NULL,
    `source` ENUM('web', 'telegram', 'admin') NOT NULL DEFAULT 'web',
    `photo_url` VARCHAR(255) NULL,
    `notes` VARCHAR(255) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_mr_meter` FOREIGN KEY (`apartment_meter_id`) REFERENCES `apartment_meters` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_mr_user` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    INDEX `idx_mr_meter_date` (`apartment_meter_id`, `reading_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Тарифи
CREATE TABLE IF NOT EXISTS `tariffs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `service_name` VARCHAR(100) NOT NULL,
    `service_code` VARCHAR(50) UNIQUE NOT NULL,
    `rate` DECIMAL(10, 2) NOT NULL,
    `unit` VARCHAR(20) NOT NULL,
    `description` VARCHAR(255) NULL,
    `valid_from` DATE NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Рахунки / Нарахування (Bills)
CREATE TABLE IF NOT EXISTS `bills` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `apartment_id` INT NOT NULL,
    `bill_number` VARCHAR(30) UNIQUE NOT NULL,
    `billing_year` SMALLINT NOT NULL,
    `billing_month` TINYINT NOT NULL,
    `maintenance_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `meters_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `previous_debt` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `paid_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `status` ENUM('unpaid', 'partial', 'paid') NOT NULL DEFAULT 'unpaid',
    `generated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `due_date` DATE NOT NULL,
    CONSTRAINT `fk_bill_apt` FOREIGN KEY (`apartment_id`) REFERENCES `apartments` (`id`) ON DELETE CASCADE,
    INDEX `idx_bill_apt_period` (`apartment_id`, `billing_year`, `billing_month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Деталізація рядків рахунку
CREATE TABLE IF NOT EXISTS `bill_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `bill_id` INT NOT NULL,
    `service_name` VARCHAR(100) NOT NULL,
    `tariff_rate` DECIMAL(10, 2) NOT NULL,
    `unit` VARCHAR(20) NOT NULL,
    `quantity` DECIMAL(10, 3) NOT NULL DEFAULT 1.000,
    `total_amount` DECIMAL(10, 2) NOT NULL,
    CONSTRAINT `fk_bi_bill` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Платежі
CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `bill_id` INT NULL,
    `apartment_id` INT NOT NULL,
    `user_id` INT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `payment_method` ENUM('qr_bank', 'bank_transfer', 'cash', 'card') NOT NULL DEFAULT 'qr_bank',
    `transaction_ref` VARCHAR(100) NULL,
    `notes` VARCHAR(255) NULL,
    `receipt_photo` VARCHAR(255) NULL,
    `status` ENUM('pending', 'confirmed', 'rejected') NOT NULL DEFAULT 'confirmed',
    `payment_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `confirmed_by` INT NULL,
    `confirmed_at` DATETIME NULL,
    CONSTRAINT `fk_pay_bill` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_pay_apt` FOREIGN KEY (`apartment_id`) REFERENCES `apartments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pay_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_pay_admin` FOREIGN KEY (`confirmed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    INDEX `idx_pay_apt` (`apartment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Заявки на ремонт / диспетчерська
CREATE TABLE IF NOT EXISTS `requests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `apartment_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `category` ENUM('plumbing', 'electrical', 'intercom', 'cleaning', 'heating', 'other') NOT NULL DEFAULT 'other',
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NOT NULL,
    `priority` ENUM('low', 'normal', 'urgent') NOT NULL DEFAULT 'normal',
    `status` ENUM('new', 'in_progress', 'done', 'cancelled') NOT NULL DEFAULT 'new',
    `assigned_to` INT NULL,
    `photo_url` VARCHAR(255) NULL,
    `resolution` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_req_apt` FOREIGN KEY (`apartment_id`) REFERENCES `apartments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_req_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_req_master` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    INDEX `idx_req_status` (`status`),
    INDEX `idx_req_apt` (`apartment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Новини та Оголошення
CREATE TABLE IF NOT EXISTS `news` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `author_id` INT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `body` TEXT NOT NULL,
    `type` ENUM('info', 'warning', 'emergency', 'event') NOT NULL DEFAULT 'info',
    `is_pinned` TINYINT(1) NOT NULL DEFAULT 0,
    `is_published` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_news_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Опитування мешканців
CREATE TABLE IF NOT EXISTS `polls` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `created_by` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `expires_at` DATE NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_poll_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Варіанти відповідей в опитуванні
CREATE TABLE IF NOT EXISTS `poll_options` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `poll_id` INT NOT NULL,
    `option_text` VARCHAR(255) NOT NULL,
    CONSTRAINT `fk_po_poll` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Голоси в опитуваннях
CREATE TABLE IF NOT EXISTS `poll_votes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `poll_id` INT NOT NULL,
    `poll_option_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `voted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_poll_user` (`poll_id`, `user_id`),
    CONSTRAINT `fk_pv_poll` FOREIGN KEY (`poll_id`) REFERENCES `polls` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pv_option` FOREIGN KEY (`poll_option_id`) REFERENCES `poll_options` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pv_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Журнал витрат ОСББ (фінансова прозорість для мешканців)
CREATE TABLE IF NOT EXISTS `expenses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `expense_date` DATE NOT NULL,
    `contractor` VARCHAR(150) NULL,
    `receipt_url` VARCHAR(255) NULL,
    `created_by` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_exp_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
