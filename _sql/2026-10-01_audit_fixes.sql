-- ============================================================
-- Melamart Admin — migration for the 2026-10 audit fixes
-- Run ONCE against an existing `store` database:
--   mysql -u <user> -p store < _sql/2026-10-01_audit_fixes.sql
-- Fresh installs: COMPLETE_SETUP.sql already includes all of this.
-- ============================================================

-- Zero dates are rejected by MySQL strict mode; "not returned" is NULL.
SET SESSION sql_mode = REPLACE(REPLACE(@@SESSION.sql_mode, 'NO_ZERO_DATE', ''), 'NO_ZERO_IN_DATE', '');
UPDATE `orders` SET `returned_date` = NULL WHERE `returned_date` = '0000-00-00';
SET SESSION sql_mode = DEFAULT;

-- Orders: late-return charge, payment reference (M-Pesa code / cheque no.)
ALTER TABLE `orders`
  ADD COLUMN `late_fee` DECIMAL(12,2) NOT NULL DEFAULT '0.00' AFTER `discount`,
  ADD COLUMN `payment_reference` VARCHAR(100) NOT NULL DEFAULT '' AFTER `payment_type`,
  MODIFY `returned_by` VARCHAR(255) NOT NULL DEFAULT '',
  MODIFY `returned_by_contact` VARCHAR(255) NOT NULL DEFAULT '',
  MODIFY `approved_by` VARCHAR(255) NOT NULL DEFAULT '',
  MODIFY `gstn` VARCHAR(255) NOT NULL DEFAULT '';

-- Order items: store rental days explicitly (was only derivable from total)
ALTER TABLE `order_item`
  ADD COLUMN `rental_days` INT(11) NOT NULL DEFAULT '1' AFTER `quantity`;

UPDATE `order_item`
   SET `rental_days` = GREATEST(1, ROUND(`total` / NULLIF(`rate` * `quantity`, 0)))
 WHERE `rate` > 0 AND `quantity` > 0;

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
