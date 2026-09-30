-- ============================================================
-- Melamart Admin — migration for the 2026-10 audit fixes
--
-- Safe to run more than once: every change checks first.
-- Run against an existing `store` database (select it first):
--   mysql -u <admin_user> -p store < _sql/2026-10-01_audit_fixes.sql
-- or in phpMyAdmin: select the database → Import → this file.
--
-- Needs a MySQL user allowed to ALTER/CREATE (not the app's
-- restricted user). Fresh installs: use COMPLETE_SETUP.sql instead.
-- ============================================================

-- Zero dates are rejected by MySQL strict mode; "not returned" is NULL.
SET @old_sql_mode := @@SESSION.sql_mode;
SET SESSION sql_mode = REPLACE(REPLACE(@@SESSION.sql_mode, 'NO_ZERO_DATE', ''), 'NO_ZERO_IN_DATE', '');
UPDATE `orders` SET `returned_date` = NULL WHERE `returned_date` = '0000-00-00';
SET SESSION sql_mode = @old_sql_mode;

-- orders.late_fee
SET @missing := (SELECT COUNT(*) = 0 FROM information_schema.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'late_fee');
SET @sql := IF(@missing, "ALTER TABLE `orders` ADD COLUMN `late_fee` DECIMAL(12,2) NOT NULL DEFAULT '0.00' AFTER `discount`", 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- orders.payment_reference (M-Pesa code / cheque no.)
SET @missing := (SELECT COUNT(*) = 0 FROM information_schema.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'payment_reference');
SET @sql := IF(@missing, "ALTER TABLE `orders` ADD COLUMN `payment_reference` VARCHAR(100) NOT NULL DEFAULT '' AFTER `payment_type`", 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Defaults for optional text columns (idempotent)
ALTER TABLE `orders`
  MODIFY `returned_by` VARCHAR(255) NOT NULL DEFAULT '',
  MODIFY `returned_by_contact` VARCHAR(255) NOT NULL DEFAULT '',
  MODIFY `approved_by` VARCHAR(255) NOT NULL DEFAULT '',
  MODIFY `gstn` VARCHAR(255) NOT NULL DEFAULT '';

-- order_item.rental_days — backfilled from total / (rate × qty) only when first added,
-- so re-running never overwrites days entered since.
SET @missing := (SELECT COUNT(*) = 0 FROM information_schema.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'order_item' AND COLUMN_NAME = 'rental_days');
SET @sql := IF(@missing, "ALTER TABLE `order_item` ADD COLUMN `rental_days` INT(11) NOT NULL DEFAULT '1' AFTER `quantity`", 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql := IF(@missing,
    "UPDATE `order_item` SET `rental_days` = GREATEST(1, ROUND(`total` / NULLIF(`rate` * `quantity`, 0))) WHERE `rate` > 0 AND `quantity` > 0",
    'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Payment history (one row per payment received)
CREATE TABLE IF NOT EXISTS `payment_history` (
  `payment_id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `payment_type` INT(11) NOT NULL,
  `reference` VARCHAR(100) NOT NULL DEFAULT '',
  `payment_date` DATETIME NOT NULL,
  `received_by` INT(11) NOT NULL,
  PRIMARY KEY (`payment_id`),
  INDEX `idx_payment_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SMS log
CREATE TABLE IF NOT EXISTS `sms_logs` (
  `sms_id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NULL DEFAULT NULL,
  `recipient` VARCHAR(30) NOT NULL,
  `message` TEXT NOT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT '',
  `sent_date` DATETIME NOT NULL,
  PRIMARY KEY (`sms_id`),
  INDEX `idx_sms_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SELECT 'Melamart audit migration complete' AS result;
