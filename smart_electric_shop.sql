-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 04, 2026 at 06:14 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smart_electric_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `Admin`
--

CREATE TABLE `Admin` (
  `admin_id` int(11) NOT NULL,
  `main_id` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Admin`
--

INSERT INTO `Admin` (`admin_id`, `main_id`, `name`, `email`, `password`, `phone_number`) VALUES
(1, 1, 'Nazmul', 'admin@smartelectric.com', 'admin123', '1234567890');

-- --------------------------------------------------------

--
-- Table structure for table `BulkPricing`
--

CREATE TABLE `BulkPricing` (
  `product_no` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `min_quantity` int(11) DEFAULT NULL,
  `discount_percentage` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `BulkPricing`
--

INSERT INTO `BulkPricing` (`product_no`, `product_id`, `min_quantity`, `discount_percentage`) VALUES
(1, 1, 5, 7.00),
(2, 1, 15, 10.00),
(3, 6, 5, 7.00),
(4, 6, 10, 11.00),
(5, 4, 5, 7.00),
(6, 4, 10, 11.00),
(7, 2, 5, 8.00),
(8, 2, 10, 15.00),
(9, 3, 5, 8.00),
(10, 3, 10, 12.00),
(11, 5, 5, 6.00),
(12, 5, 10, 10.00);

-- --------------------------------------------------------

--
-- Table structure for table `CanCheckOrder`
--

CREATE TABLE `CanCheckOrder` (
  `order_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `CanCheckOrder`
--

INSERT INTO `CanCheckOrder` (`order_id`, `admin_id`) VALUES
(1, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1);

-- --------------------------------------------------------

--
-- Table structure for table `ContactMessages`
--

CREATE TABLE `ContactMessages` (
  `message_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(30) DEFAULT 'Open',
  `response_text` text DEFAULT NULL,
  `responded_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ContactMessages`
--

INSERT INTO `ContactMessages` (`message_id`, `user_id`, `name`, `email`, `subject`, `message`, `status`, `response_text`, `responded_by`, `created_at`) VALUES
(1, 3, 'Rabbani', 'rabbani@gmail.com', 'hello', 'how are you?', 'Open', NULL, NULL, '2026-01-02 20:38:50');

-- --------------------------------------------------------

--
-- Table structure for table `Main_Admin`
--

CREATE TABLE `Main_Admin` (
  `main_id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Main_Admin`
--

INSERT INTO `Main_Admin` (`main_id`, `name`) VALUES
(1, 'System Administrator');

-- --------------------------------------------------------

--
-- Table structure for table `Order`
--

CREATE TABLE `Order` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT NULL,
  `payment_status` varchar(30) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Order`
--

INSERT INTO `Order` (`order_id`, `user_id`, `order_date`, `payment_status`, `total_amount`, `discount`) VALUES
(1, 3, '2026-01-01 15:30:17', 'Paid', 54490.00, 0.00),
(2, 3, '2026-01-01 15:35:55', 'Paid', 55490.00, 0.00),
(3, 3, '2026-01-01 15:36:27', 'Processing', 1089745.60, 54.40),
(4, 3, '2026-01-02 00:57:21', 'Cancelled', 54490.00, 0.00),
(5, 3, '2026-01-02 00:58:07', 'Paid', 54435.60, 54.40),
(6, 3, '2026-01-02 02:44:14', 'Pending', 53946.00, 544.00),
(7, 3, '2026-01-02 02:44:50', 'Paid', 53951.00, 539.00),
(8, 3, '2026-01-02 02:46:20', 'Processing', 108441.00, 539.00),
(9, 3, '2026-01-02 22:34:28', 'Paid', 54406.00, 1084.00),
(10, 3, '2026-01-02 22:35:29', 'Paid', 54946.00, 544.00),
(11, 3, '2026-01-03 23:41:06', 'Paid', 179900.00, 0.00),
(12, 3, '2026-01-04 22:19:47', 'cart', 1978900.00, 0.00),
(13, 3, '2026-01-04 22:20:30', 'Pending', 297700.00, 0.00),
(14, 3, '2026-01-04 22:20:55', 'Paid', 297700.00, 0.00),
(15, 3, '2026-01-04 22:49:56', 'Pending', 1134235.00, 0.00),
(16, 3, '2026-01-04 22:50:07', 'Pending', 1134235.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `OrderItem`
--

CREATE TABLE `OrderItem` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `OrderItem`
--

INSERT INTO `OrderItem` (`item_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 1, 1, 54490.00),
(3, 3, 1, 20, 54490.00),
(6, 4, 1, 1, 54490.00),
(7, 5, 1, 1, 54490.00),
(8, 6, 1, 1, 54490.00),
(9, 7, 1, 1, 54490.00),
(10, 8, 1, 2, 54490.00),
(11, 9, 2, 1, 55490.00),
(12, 2, 2, 1, 55490.00),
(13, 10, 2, 1, 55490.00),
(14, 11, 6, 1, 179900.00),
(16, 13, 4, 1, 297700.00),
(17, 14, 4, 1, 297700.00),
(18, 12, 6, 11, 179900.00),
(19, 15, 4, 1, 297700.00),
(20, 15, 6, 5, 167307.00),
(21, 16, 4, 1, 297700.00),
(22, 16, 6, 5, 167307.00);

-- --------------------------------------------------------

--
-- Table structure for table `Product`
--

CREATE TABLE `Product` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `warranty_duration` int(11) DEFAULT NULL,
  `available_quantity` int(11) DEFAULT NULL,
  `reward_points` int(11) DEFAULT 0,
  `images` text DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `warranty_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Product`
--

INSERT INTO `Product` (`product_id`, `name`, `description`, `price`, `warranty_duration`, `available_quantity`, `reward_points`, `images`, `admin_id`, `warranty_id`) VALUES
(2, 'Walton WFC-3F5-GDEL-XX (Inverter)', 'WFC-3F5-GDEL-XX (Inverter)\r\n   - Type: Direct Cool\r\n   - Door: Glass door\r\n   - Gross Volume: 380 Ltr\r\n   - Net Volume: 365 Ltr\r\n   - Refrigerant: R600a\r\n   - Wide Voltage design (75V - 270V)\r\n   - Using Latest Intelligent INVERTER technology\r\n   - Don\'t use Voltage stabilizer, if use warranty will be voided.\r\n   - Rated for 300W', 54990.00, 144, 50, 500, '[\"images\\/products\\/product-2-1767373403-0.jpg\",\"images\\/products\\/product-2-1767373403-1.jpg\",\"images\\/products\\/product-2-1767373403-2.jpg\",\"images\\/products\\/product-2-1767373403-3.jpg\",\"images\\/products\\/product-2-1767373403-4.jpg\"]', 1, NULL),
(3, 'Walton WFK-3G0-GDEL-XX (Inverter)', 'WFK-3G0-GDEL-XX (Inverter)\r\n   - Type: Direct Cool\r\n   - Door: Glass Door\r\n   - Gross Volume: 370 Ltr\r\n   - Net Volume: 367 Ltr\r\n   - Special Technology: Nano Healthcare\r\n   - Refrigerant: R600a\r\n   - Rated for 300W', 55990.00, 144, 50, 500, '[\"images\\/products\\/product-3-1767373683-0.jpg\",\"images\\/products\\/product-3-1767373683-1.jpg\",\"images\\/products\\/product-3-1767373683-2.jpg\",\"images\\/products\\/product-3-1767373683-3.jpg\",\"images\\/products\\/product-3-1767373683-4.jpg\"]', 1, NULL),
(4, 'Hitachi Big French Refrigerator | R-W690P7PB (GBK) | 586 L', 'Model:\r\nR-W690P7PB-GBK\r\n - Inverter\r\n - R-600a refrigerant \r\n - Dual fan cooling\r\n - Nano titanium\r\n - Rated for 500W', 297700.00, 120, 26, 1500, '[\"images\\/products\\/product-4-1767373924-0.webp\"]', 1, NULL),
(5, 'Samsung Refrigerator RT42CG6442B1D2 | 415 LTR', 'SKU:\r\n911399\r\nModel:\r\nRT42 B1\r\n - Deodorizer Fresh Filter \r\n - Digital Inverter Compressor\r\n - All Around Cooling\r\n - Rated for 400W', 124900.00, 120, 30, 700, '[\"images\\/products\\/product-5-1767374463-0.webp\",\"images\\/products\\/product-5-1767374463-1.webp\",\"images\\/products\\/product-5-1767374463-2.webp\",\"images\\/products\\/product-5-1767374463-3.webp\",\"images\\/products\\/product-5-1767374463-4.webp\",\"images\\/products\\/product-5-1767374463-5.webp\"]', 1, NULL),
(6, 'Samsung Top Mount Refrigerator | RT65K7058BS/D2 | 670 L', 'SKU: 911246\r\nModel: RT65K7058BS/D2\r\n - Digital inverter\r\n - Twin cooling plus\r\n - Odor-free\r\n - Power cool\r\n - Rated for 400W', 179900.00, 120, 19, 800, '[\"images\\/products\\/product-6-1767374539-0.webp\"]', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `RewardPoints`
--

CREATE TABLE `RewardPoints` (
  `points_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `points` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `RewardPoints`
--

INSERT INTO `RewardPoints` (`points_id`, `user_id`, `points`) VALUES
(1, 3, 15349),
(2, 3, 15349),
(3, 3, 15349),
(4, 3, 15349);

-- --------------------------------------------------------

--
-- Table structure for table `ServiceRequest`
--

CREATE TABLE `ServiceRequest` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `warranty_id` int(11) DEFAULT NULL,
  `issue` text DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ServiceRequest`
--

INSERT INTO `ServiceRequest` (`request_id`, `user_id`, `warranty_id`, `issue`, `status`) VALUES
(1, 3, NULL, 'Contact: AC need to be cleaned - Please send someone to clean my AC', 'Resolved');

-- --------------------------------------------------------

--
-- Table structure for table `Staff`
--

CREATE TABLE `Staff` (
  `staff_id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Staff`
--

INSERT INTO `Staff` (`staff_id`, `name`, `email`, `password`, `phone_number`) VALUES
(1, 'Mahir', 'mahir@smartelectric.com', 'staff@0001', '01714256789'),
(2, 'Nawshin', 'nawshin@smartelectric.com', 'staff@0002', '01754726458'),
(3, 'Nazmul', 'nazmul@smartelectric.com', 'staff@0003', '01812403459'),
(4, 'Metul', 'metul@smartelectric.com', 'staff@0004', '01924578104');

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE `User` (
  `user_id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `warranty_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `User`
--

INSERT INTO `User` (`user_id`, `name`, `email`, `password`, `phone_number`, `order_id`, `warranty_id`, `created_at`, `updated_at`) VALUES
(1, 'Nazmul', 'nazmulhaque@gmail.com', '$2y$10$nC3IH2e27565efevxvleIeWQkH9rAUfpduB3GWz/aU8hgc.3GV1wu', '01940624884', NULL, NULL, '2026-01-01 20:11:14', NULL),
(2, 'Mahir', 'mahir@gmail.com', '$2y$10$BEXSswO9UzXRPT5D/tWKj.hRfrdVuv.Ejdv441Zjh61a6tQe31yl2', '123456789', NULL, NULL, '2026-01-01 20:11:14', NULL),
(3, 'Rabbani', 'rabbani@gmail.com', '$2y$10$j1cVMmYer1VuGgydBXiRFuyj8KWI0TRgLGgPeOYxnEYvujGQdi7p2', '01911111111', NULL, 16, '2026-01-01 20:11:14', '2026-01-04 16:50:07'),
(4, 'Mostak', 'mostak@gmail.com', '$2y$10$/q.I9u1nTvwN/TBSr0veou1R7.4bjSnI0W7lnvft1X3Zq3nL9rAoO', '123456789', NULL, NULL, '2026-01-01 20:11:14', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Warranty`
--

CREATE TABLE `Warranty` (
  `warranty_id` int(11) NOT NULL,
  `warranty_duration` int(11) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Warranty`
--

INSERT INTO `Warranty` (`warranty_id`, `warranty_duration`, `purchase_date`) VALUES
(4, 144, '2026-01-02'),
(5, 1, '2026-01-02'),
(6, 1, '2026-01-02'),
(8, 144, '2026-01-02'),
(9, 144, '2026-01-02'),
(10, 120, '2026-01-03'),
(11, 120, '2026-01-04'),
(12, 120, '2026-01-04'),
(13, 120, '2026-01-04'),
(14, 120, '2026-01-04'),
(15, 120, '2026-01-04'),
(16, 120, '2026-01-04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Admin`
--
ALTER TABLE `Admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `main_id` (`main_id`);

--
-- Indexes for table `BulkPricing`
--
ALTER TABLE `BulkPricing`
  ADD PRIMARY KEY (`product_no`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `CanCheckOrder`
--
ALTER TABLE `CanCheckOrder`
  ADD PRIMARY KEY (`order_id`,`admin_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `ContactMessages`
--
ALTER TABLE `ContactMessages`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `Main_Admin`
--
ALTER TABLE `Main_Admin`
  ADD PRIMARY KEY (`main_id`);

--
-- Indexes for table `Order`
--
ALTER TABLE `Order`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `OrderItem`
--
ALTER TABLE `OrderItem`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `Product`
--
ALTER TABLE `Product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `RewardPoints`
--
ALTER TABLE `RewardPoints`
  ADD PRIMARY KEY (`points_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ServiceRequest`
--
ALTER TABLE `ServiceRequest`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `warranty_id` (`warranty_id`);

--
-- Indexes for table `Staff`
--
ALTER TABLE `Staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `Warranty`
--
ALTER TABLE `Warranty`
  ADD PRIMARY KEY (`warranty_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Admin`
--
ALTER TABLE `Admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `BulkPricing`
--
ALTER TABLE `BulkPricing`
  MODIFY `product_no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `ContactMessages`
--
ALTER TABLE `ContactMessages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Main_Admin`
--
ALTER TABLE `Main_Admin`
  MODIFY `main_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Order`
--
ALTER TABLE `Order`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `OrderItem`
--
ALTER TABLE `OrderItem`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `Product`
--
ALTER TABLE `Product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `RewardPoints`
--
ALTER TABLE `RewardPoints`
  MODIFY `points_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ServiceRequest`
--
ALTER TABLE `ServiceRequest`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Staff`
--
ALTER TABLE `Staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `User`
--
ALTER TABLE `User`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Warranty`
--
ALTER TABLE `Warranty`
  MODIFY `warranty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Admin`
--
ALTER TABLE `Admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`main_id`) REFERENCES `Main_Admin` (`main_id`);

--
-- Constraints for table `CanCheckOrder`
--
ALTER TABLE `CanCheckOrder`
  ADD CONSTRAINT `cancheckorder_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `Order` (`order_id`),
  ADD CONSTRAINT `cancheckorder_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `Admin` (`admin_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
