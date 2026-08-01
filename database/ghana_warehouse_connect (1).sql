-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 19, 2026 at 01:49 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ghana_warehouse_connect`
--
CREATE DATABASE IF NOT EXISTS `ghana_warehouse_connect` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ghana_warehouse_connect`;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(200) NOT NULL,
  `entity` varchar(100) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncate table before insert `audit_logs`
--

TRUNCATE TABLE `audit_logs`;
-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE IF NOT EXISTS `cart` (
  `cart_id` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `item_type` enum('product','package') NOT NULL DEFAULT 'product',
  `product_id` int(11) DEFAULT NULL,
  `package_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`cart_id`),
  KEY `user_id` (`UserID`),
  KEY `product_id` (`product_id`),
  KEY `package_id` (`package_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncate table before insert `cart`
--

TRUNCATE TABLE `cart`;
--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `UserID`, `item_type`, `product_id`, `package_id`, `quantity`, `added_at`) VALUES
(1, 1, 'product', 2, 1, 1, '2026-07-06 20:24:33');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncate table before insert `categories`
--

TRUNCATE TABLE `categories`;
--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`, `status`, `created_at`) VALUES
(1, 'Food & Groceries', 'Basic food and grocery items', 'active', '2026-06-27 19:32:06'),
(2, 'Beverages', 'Drinks and beverages', 'active', '2026-06-27 19:32:06'),
(3, 'Personal Care', 'Hygiene and personal care products', 'active', '2026-06-27 19:32:06'),
(4, 'Household', 'Household essentials', 'active', '2026-06-27 19:32:06'),
(5, 'Electronics', 'Electronic accessories', 'active', '2026-06-27 19:32:06');

-- --------------------------------------------------------

--
-- Table structure for table `combo_packages`
--

DROP TABLE IF EXISTS `combo_packages`;
CREATE TABLE IF NOT EXISTS `combo_packages` (
  `package_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `target_group` enum('student','community','all') DEFAULT 'all',
  `price` decimal(10,2) NOT NULL,
  `stock_limit` int(11) DEFAULT NULL,
  `availability_start` date DEFAULT NULL,
  `availability_end` date DEFAULT NULL,
  `StockLimit` int(11) NOT NULL DEFAULT 10,
  `status` enum('draft','published','unpublished') DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`package_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncate table before insert `combo_packages`
--

TRUNCATE TABLE `combo_packages`;
--
-- Dumping data for table `combo_packages`
--

INSERT INTO `combo_packages` (`package_id`, `name`, `description`, `target_group`, `price`, `stock_limit`, `availability_start`, `availability_end`, `StockLimit`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'RICE group', '', 'student', 120.00, NULL, '2026-06-29', NULL, 1000, 'published', NULL, '2026-06-29 18:12:14', '2026-06-29 18:14:58'),
(2, 'System_admin', '', '', 0.00, NULL, '2026-07-07', NULL, 1000, 'published', NULL, '2026-07-07 20:20:24', '2026-07-07 20:58:39');

-- --------------------------------------------------------

--
-- Table structure for table `customer_profiles`
--

DROP TABLE IF EXISTS `customer_profiles`;
CREATE TABLE IF NOT EXISTS `customer_profiles` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `customer_type` enum('bundle','wholesale') NOT NULL,
  `address` text DEFAULT NULL,
  `business_name` varchar(200) DEFAULT NULL,
  `verification_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`customer_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncate table before insert `customer_profiles`
--

TRUNCATE TABLE `customer_profiles`;
-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

DROP TABLE IF EXISTS `deliveries`;
CREATE TABLE IF NOT EXISTS `deliveries` (
  `delivery_id` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `DeliveryAddress` text DEFAULT NULL,
  `CustomerContact` varchar(20) DEFAULT NULL,
  `City` varchar(255) DEFAULT NULL,
  `status` enum('assigned','in_transit','delivered','failed','returned') DEFAULT 'assigned',
  `receiver_name` varchar(150) DEFAULT NULL,
  `proof_notes` text DEFAULT NULL,
  `proof_photo` varchar(255) DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`delivery_id`),
  KEY `order_id` (`OrderID`),
  KEY `assigned_to` (`assigned_to`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncate table before insert `deliveries`
--

TRUNCATE TABLE `deliveries`;
--
-- Dumping data for table `deliveries`
--

INSERT INTO `deliveries` (`delivery_id`, `OrderID`, `assigned_to`, `DeliveryAddress`, `CustomerContact`, `City`, `status`, `receiver_name`, `proof_notes`, `proof_photo`, `delivered_at`, `created_at`, `updated_at`) VALUES
(1, 4450, NULL, 'dfhdjgdhfu', '0549149391', 'Accra', 'assigned', NULL, NULL, NULL, NULL, '2026-07-02 13:49:41', '2026-07-02 13:49:41'),
(2, 4451, NULL, 'dfhdjgdhfu', '0549149391', 'Accra', 'assigned', NULL, NULL, NULL, NULL, '2026-07-02 14:02:08', '2026-07-02 14:02:08'),
(3, 4452, NULL, 'dfhdjgdhfu', NULL, 'Accra', 'assigned', NULL, NULL, NULL, NULL, '2026-07-09 20:51:12', '2026-07-09 20:51:12'),
(4, 4453, NULL, 'dfhdjgdhfu', NULL, 'Accra', 'assigned', NULL, NULL, NULL, NULL, '2026-07-09 20:52:00', '2026-07-09 20:52:00'),
(5, 4462, NULL, 'dfhdjgdhfu', NULL, 'Accra', 'assigned', NULL, NULL, NULL, NULL, '2026-07-12 21:40:08', '2026-07-12 21:40:08'),
(6, 4463, NULL, 'dfhdjgdhfu', NULL, 'Accra', 'assigned', NULL, NULL, NULL, NULL, '2026-07-12 21:41:06', '2026-07-12 21:41:06'),
(7, 4470, NULL, 'dfhdjgdhfu', '0549149391', 'Accra', 'assigned', NULL, NULL, NULL, NULL, '2026-07-12 21:59:46', '2026-07-12 21:59:46'),
(8, 4471, NULL, 'dfhdjgdhfu', '0549149391', 'Accra', 'assigned', NULL, NULL, NULL, NULL, '2026-07-12 22:00:26', '2026-07-12 22:00:26'),
(9, 4472, NULL, 'dfhdjgdhfu', '0549149391', 'Accra', 'assigned', NULL, NULL, NULL, NULL, '2026-07-12 22:00:44', '2026-07-12 22:00:44'),
(10, 4473, NULL, 'dfhdjgdhfu', NULL, 'Accra', 'assigned', NULL, NULL, NULL, NULL, '2026-07-17 16:01:49', '2026-07-17 16:01:49');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

DROP TABLE IF EXISTS `inventory`;
CREATE TABLE IF NOT EXISTS `inventory` (
  `inventory_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `reorder_level` int(11) DEFAULT 10,
  `reserved_quantity` int(11) DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ReorderLevel` int(11) NOT NULL DEFAULT 10,
  PRIMARY KEY (`inventory_id`),
  UNIQUE KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncate table before insert `inventory`
--

TRUNCATE TABLE `inventory`;
--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `product_id`, `stock_quantity`, `reorder_level`, `reserved_quantity`, `last_updated`, `ReorderLevel`) VALUES
(1, 2, 100, 10, 0, '2026-07-07 20:53:29', 10);

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
CREATE TABLE IF NOT EXISTS `invoices` (
  `invoice_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `invoice_number` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `issue_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('issued','paid','cancelled') DEFAULT 'issued',
  PRIMARY KEY (`invoice_id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncate table before insert `invoices`
--

TRUNCATE TABLE `invoices`;
-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`notification_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncate table before insert `notifications`
--

TRUNCATE TABLE `notifications`;
--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `event_type`, `message`, `status`, `created_at`) VALUES
(1, 4, 'hmm', 'hello', '', '0000-00-00 00:00:00'),
(2, 4, 'hmm', 'hello', '', '0000-00-00 00:00:00'),
(3, 4, 'hmm', 'hello', '', '0000-00-00 00:00:00'),
(4, 4, 'hmm', 'hello', '', '0000-00-00 00:00:00'),
(5, 4, 'hmm', 'hello', '', '0000-00-00 00:00:00'),
(6, 4, 'hmm', 'hello', '', '0000-00-00 00:00:00'),
(7, 4, 'hmm', 'hello', '', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `OrderID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `OrderType` enum('bundle','wholesale') NOT NULL,
  `status` enum('pending','confirmed','packed','dispatched','delivered','cancelled','failed') DEFAULT 'pending',
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `tax` decimal(10,2) DEFAULT 0.00,
  `delivery_fee` decimal(10,2) DEFAULT 0.00,
  `TotalAmount` decimal(10,2) NOT NULL,
  `PaymentStatus` enum('unpaid','pending','paid','failed','refunded') DEFAULT 'unpaid',
  `DeliveryAddress` text DEFAULT NULL,
  `DeliveryCity` varchar(225) DEFAULT NULL,
  `CustomerPhone` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`OrderID`),
  KEY `user_id` (`UserID`)
) ENGINE=InnoDB AUTO_INCREMENT=4474 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncate table before insert `orders`
--

TRUNCATE TABLE `orders`;
--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`OrderID`, `UserID`, `OrderType`, `status`, `subtotal`, `tax`, `delivery_fee`, `TotalAmount`, `PaymentStatus`, `DeliveryAddress`, `DeliveryCity`, `CustomerPhone`, `notes`, `created_at`, `updated_at`) VALUES
(4446, 4, 'bundle', 'pending', 0.00, 0.00, 0.00, 363.00, 'unpaid', 'dfhdjgdhfu', 'Accra', '0549149391', NULL, '2026-07-02 13:35:38', '2026-07-02 13:35:38'),
(4447, 4, 'bundle', 'pending', 0.00, 0.00, 0.00, 363.00, 'unpaid', 'dfhdjgdhfu', 'Accra', '0549149391', NULL, '2026-07-02 13:38:27', '2026-07-02 13:38:27'),
(4448, 4, 'bundle', 'pending', 0.00, 0.00, 0.00, 363.00, 'unpaid', 'dfhdjgdhfu', 'Accra', '0549149391', NULL, '2026-07-02 13:39:17', '2026-07-02 13:39:17'),
(4449, 4, 'bundle', 'cancelled', 0.00, 0.00, 0.00, 363.00, 'unpaid', 'dfhdjgdhfu', 'Accra', '0549149391', NULL, '2026-07-02 13:41:14', '2026-07-13 12:31:46'),
(4450, 4, 'bundle', 'pending', 0.00, 0.00, 0.00, 363.00, 'unpaid', 'dfhdjgdhfu', 'Accra', '0549149391', NULL, '2026-07-02 13:49:41', '2026-07-02 13:49:41'),
(4451, 4, 'bundle', 'pending', 0.00, 0.00, 0.00, 363.00, 'unpaid', 'dfhdjgdhfu', 'Accra', '0549149391', NULL, '2026-07-02 14:02:07', '2026-07-02 14:02:07'),
(4452, 4, '', 'pending', 0.00, 0.00, 0.00, 250.00, 'unpaid', 'dfhdjgdhfu', 'Accra', NULL, NULL, '2026-07-09 20:51:12', '2026-07-09 20:51:12'),
(4453, 4, '', 'pending', 0.00, 0.00, 0.00, 250.00, 'unpaid', 'dfhdjgdhfu', 'Accra', NULL, NULL, '2026-07-09 20:52:00', '2026-07-09 20:52:00'),
(4462, 4, '', 'pending', 0.00, 0.00, 0.00, 250.00, 'unpaid', 'dfhdjgdhfu', 'Accra', NULL, NULL, '2026-07-12 21:40:08', '2026-07-12 21:40:08'),
(4463, 4, '', 'pending', 0.00, 0.00, 0.00, 250.00, 'unpaid', 'dfhdjgdhfu', 'Accra', NULL, NULL, '2026-07-12 21:41:06', '2026-07-12 21:41:06'),
(4467, 1, '', 'pending', 0.00, 0.00, 0.00, 262.50, 'unpaid', 'dfhdjgdhfu', 'Accra', '0549149391', NULL, '2026-07-12 21:50:16', '2026-07-12 21:50:16'),
(4468, 1, '', 'pending', 0.00, 0.00, 0.00, 262.50, 'unpaid', 'dfhdjgdhfu', 'Accra', '0549149391', NULL, '2026-07-12 21:54:17', '2026-07-12 21:54:17'),
(4469, 1, '', 'pending', 0.00, 0.00, 0.00, 262.50, 'unpaid', 'dfhdjgdhfu', 'Accra', '0549149391', NULL, '2026-07-12 21:55:24', '2026-07-12 21:55:24'),
(4470, 1, '', 'pending', 0.00, 0.00, 0.00, 262.50, 'unpaid', 'dfhdjgdhfu', 'Accra', '0549149391', NULL, '2026-07-12 21:59:46', '2026-07-12 21:59:46'),
(4471, 1, '', 'pending', 0.00, 0.00, 0.00, 262.50, 'unpaid', 'dfhdjgdhfu', 'Accra', '0549149391', NULL, '2026-07-12 22:00:26', '2026-07-12 22:00:26'),
(4472, 1, '', 'pending', 0.00, 0.00, 0.00, 262.50, 'unpaid', 'dfhdjgdhfu', 'Accra', '0549149391', NULL, '2026-07-12 22:00:44', '2026-07-12 22:00:44'),
(4473, 4, '', 'pending', 0.00, 0.00, 0.00, 250.00, 'unpaid', 'dfhdjgdhfu', 'Accra', NULL, NULL, '2026-07-17 16:01:49', '2026-07-17 16:01:49');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `order_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `OrderID` int(11) NOT NULL,
  `item_type` enum('product','package') NOT NULL,
  `ProductID` int(11) DEFAULT NULL,
  `package_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `UnitPrice` decimal(10,2) NOT NULL,
  `TotalPrice` decimal(10,2) NOT NULL,
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`OrderID`),
  KEY `product_id` (`ProductID`),
  KEY `package_id` (`package_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncate table before insert `order_items`
--

TRUNCATE TABLE `order_items`;
--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `OrderID`, `item_type`, `ProductID`, `package_id`, `quantity`, `UnitPrice`, `TotalPrice`) VALUES
(1, 4448, 'product', 1, NULL, 5, 59.00, 295.00),
(2, 4448, 'product', 2, NULL, 2, 34.00, 68.00),
(3, 4449, 'product', 1, NULL, 5, 59.00, 295.00),
(4, 4449, 'product', 2, NULL, 2, 34.00, 68.00),
(5, 4450, 'product', 1, NULL, 5, 59.00, 295.00),
(6, 4450, 'product', 2, NULL, 2, 34.00, 68.00),
(7, 4451, 'product', 1, NULL, 5, 59.00, 295.00),
(8, 4451, 'product', 2, NULL, 2, 34.00, 68.00),
(9, 4470, 'product', 1, NULL, 5, 50.00, 250.00),
(10, 4471, 'product', 1, NULL, 5, 50.00, 250.00),
(11, 4472, 'product', 1, NULL, 5, 50.00, 250.00);

-- --------------------------------------------------------

--
-- Table structure for table `package_items`
--

DROP TABLE IF EXISTS `package_items`;
CREATE TABLE IF NOT EXISTS `package_items` (
  `package_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `package_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`package_item_id`),
  KEY `package_id` (`package_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncate table before insert `package_items`
--

TRUNCATE TABLE `package_items`;
--
-- Dumping data for table `package_items`
--

INSERT INTO `package_items` (`package_item_id`, `package_id`, `product_id`, `quantity`) VALUES
(1, 1, 1, 5),
(2, 2, 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `provider` varchar(100) DEFAULT NULL,
  `reference` varchar(200) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('initiated','pending','verified','failed','refunded') DEFAULT 'initiated',
  `payment_method` enum('mobile_money','card','bank_transfer','cash') DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`payment_id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `order_id` (`order_id`),
  KEY `verified_by` (`verified_by`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncate table before insert `payments`
--

TRUNCATE TABLE `payments`;
--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `provider`, `reference`, `amount`, `status`, `payment_method`, `verified_by`, `verified_at`, `created_at`) VALUES
(1, 4446, 'emefa', 'dzah', 0.00, 'initiated', NULL, NULL, NULL, '2026-07-09 16:19:17'),
(8, 4449, 'emefa', 'TXN-20260712221447-8856', 50.00, 'initiated', '', NULL, NULL, '2026-07-12 20:14:47'),
(16, 4449, 'emefa', 'TXN-20260712223307-9172', 50.00, '', '', NULL, '2026-07-12 22:07:09', '2026-07-12 20:33:07'),
(17, 4449, 'emefa', 'TXN-20260713001240-8625', 50.00, 'initiated', '', NULL, NULL, '2026-07-12 22:12:40'),
(18, 4449, 'emefa', 'TXN-20260713001245-9737', 50.00, 'initiated', '', NULL, NULL, '2026-07-12 22:12:45');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `sku` varchar(100) NOT NULL,
  `name` varchar(200) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `wholesale_price` decimal(10,2) NOT NULL,
  `min_wholesale_qty` int(11) DEFAULT 10,
  `unit` varchar(50) DEFAULT 'piece',
  `status` enum('active','archived') DEFAULT 'active',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `sku` (`sku`),
  KEY `category_id` (`category_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncate table before insert `products`
--

TRUNCATE TABLE `products`;
--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `sku`, `name`, `category_id`, `description`, `unit_price`, `wholesale_price`, `min_wholesale_qty`, `unit`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'fdsfdsfdsfdsfdsfdfld', '', 1, NULL, 0.00, 0.00, 10, 'piece', NULL, 4, '2026-06-29 17:54:35', '2026-07-17 15:45:37'),
(2, 'difndufnliudfwd', 'Sugar', 2, NULL, 12.00, 9.00, 5, 'piece', 'active', 1, '2026-06-29 17:54:35', '2026-07-16 20:23:14'),
(3, 'thtfddxcfdrx', 'emefa', 1, 'hmm', 0.00, 0.00, 1, 'pieces', 'active', NULL, '2026-07-16 19:39:01', '2026-07-16 19:39:01'),
(6, 'thtfddxcfdnn', 'emefaa', 1, 'hmm', 0.00, 0.00, 1, 'pieces', 'active', NULL, '2026-07-17 15:45:58', '2026-07-17 15:45:58');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('bundle_customer','wholesale_customer','warehouse_admin','delivery_personnel','system_admin') NOT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncate table before insert `users`
--

TRUNCATE TABLE `users`;
--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `phone`, `password_hash`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'System Administrator', 'admin@gwc.com', '0200000000', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'system_admin', 'active', '2026-06-27 19:31:25', '2026-06-27 19:31:25'),
(2, 'Emefa', 'emefa@gmail.com', '0547335055', '$2y$10$qveLR14jkpckeOAoqYPcOOyCPSStd4dnZr.3xUIj/Aim5CUeoc.AO', '', 'active', '2026-06-28 22:00:37', '2026-06-28 22:00:37'),
(3, 'John Smith', 'john@gmail.com', '0547295055', '$2y$10$GH9h8AgFfE3z0QpZNiz3yOqsgNCrPo01dkxc.EfznL8fJUYkjJ7bG', 'bundle_customer', 'active', '2026-06-28 22:02:57', '2026-06-28 22:02:57'),
(4, 'dzah', 'emefa1@gmail.com', '0549149392', '$2y$10$jW4qWCaZtjfG6yDpcOHd7esd33RPrNia50jnQ/QTBCpKDcxbFpI7u', 'warehouse_admin', 'active', '2026-06-29 17:01:35', '2026-07-07 19:30:26'),
(5, 'jennifer', 'jennifer55@gmal.com', '0240556721', '$2y$10$Kimr4.mmHBHpQoKkiybpm.jj4VcIzyQpQFpxVw4FfnkCu4N3Csary', '', 'active', '2026-07-07 18:11:16', '2026-07-07 18:11:16');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `cart_ibfk_3` FOREIGN KEY (`package_id`) REFERENCES `combo_packages` (`package_id`);

--
-- Constraints for table `combo_packages`
--
ALTER TABLE `combo_packages`
  ADD CONSTRAINT `combo_packages_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `customer_profiles`
--
ALTER TABLE `customer_profiles`
  ADD CONSTRAINT `customer_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`),
  ADD CONSTRAINT `deliveries_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`OrderID`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`ProductID`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`package_id`) REFERENCES `combo_packages` (`package_id`);

--
-- Constraints for table `package_items`
--
ALTER TABLE `package_items`
  ADD CONSTRAINT `package_items_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `combo_packages` (`package_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `package_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`OrderID`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`);
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
