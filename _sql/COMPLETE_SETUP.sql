-- Complete Database Setup for Construction Equipment Rental System
-- This file contains the complete database schema including all updates
-- Run this file if you're setting up a fresh database

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE IF NOT EXISTS `brands` (
  `brand_id` int(11) NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(255) NOT NULL,
  `brand_active` int(11) NOT NULL DEFAULT '0',
  `brand_status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`brand_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `categories_id` int(11) NOT NULL AUTO_INCREMENT,
  `categories_name` varchar(255) NOT NULL,
  `categories_active` int(11) NOT NULL DEFAULT '0',
  `categories_status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`categories_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
-- Updated for rental system with nullable returned_date
--

CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_date` date NOT NULL,
  `expect_return_date` date NOT NULL,
  `returned_date` date NULL DEFAULT NULL,
  `site_location` varchar(255) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `client_contact` varchar(255) NOT NULL,
  `driver_name` varchar(255) NOT NULL,
  `driver_contact` varchar(255) NOT NULL,
  `returned_by` varchar(255) NOT NULL DEFAULT '',
  `returned_by_contact` varchar(255) NOT NULL DEFAULT '',
  `approved_by` varchar(255) NOT NULL DEFAULT '',
  `sub_total` varchar(255) NOT NULL,
  `vat` varchar(255) NOT NULL,
  `total_amount` varchar(255) NOT NULL,
  `discount` varchar(255) NOT NULL,
  `late_fee` DECIMAL(12,2) NOT NULL DEFAULT '0.00',
  `grand_total` varchar(255) NOT NULL,
  `paid` varchar(255) NOT NULL,
  `due` varchar(255) NOT NULL,
  `payment_type` int(11) NOT NULL,
  `payment_reference` VARCHAR(100) NOT NULL DEFAULT '',
  `payment_status` int(11) NOT NULL,
  `payment_place` int(11) NOT NULL COMMENT 'Branch: 1 = Ruiru, 2 = Kikuyu',
  `gstn` varchar(255) NOT NULL DEFAULT '' COMMENT 'Client KRA PIN (optional)',
  `order_status` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL,
  `last_late_fee_calc` DATETIME NULL DEFAULT NULL,
  `reminder_sent` TINYINT(1) NOT NULL DEFAULT '0',
  `last_reminder_date` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  INDEX `idx_expect_return_date` (`expect_return_date`),
  INDEX `idx_returned_date` (`returned_date`),
  INDEX `idx_order_status` (`order_status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE IF NOT EXISTS `order_item` (
  `order_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `quantity` varchar(255) NOT NULL,
  `rental_days` INT(11) NOT NULL DEFAULT '1',
  `rate` varchar(255) NOT NULL,
  `total` varchar(255) NOT NULL,
  `order_item_status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`order_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Payment history (one row per payment received)
--

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

--
-- SMS log
--

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

-- --------------------------------------------------------

--
-- Table structure for table `product`
-- Updated with daily_rate for rental pricing
--

CREATE TABLE IF NOT EXISTS `product` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(255) NOT NULL,
  `product_image` text NOT NULL,
  `brand_id` int(11) NOT NULL,
  `categories_id` int(11) NOT NULL,
  `quantity` varchar(255) NOT NULL,
  `rate` varchar(255) NOT NULL,
  `daily_rate` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `active` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=11;

--
-- Dumping data for table `users`
-- Default admin user: username='admin', password='admin' (MD5 hash, upgraded to bcrypt on first login).
-- CHANGE THIS PASSWORD IMMEDIATELY after installing (My Account → Password).
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', '')
ON DUPLICATE KEY UPDATE username=username;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
