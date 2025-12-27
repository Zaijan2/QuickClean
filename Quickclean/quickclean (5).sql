-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 27, 2025 at 02:55 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quickclean`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `date` date NOT NULL,
  `time` varchar(50) DEFAULT NULL,
  `extras` text DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','accepted','declined') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `service_id`, `service_name`, `price`, `date`, `time`, `extras`, `name`, `phone`, `email`, `address`, `notes`, `status`, `created_at`) VALUES
(1, 0, 3, 'Move-in/Move-out Cleaning', NULL, '2025-10-13', '17:05', NULL, 'Otelo Nobleza', '09123456789', 'otelo@gmail.com', 'Sugcad, Polangui, Albay', 'matthew', '', '2025-10-06 04:05:48'),
(3, 0, 4, 'Upholstery Cleaning', NULL, '2025-10-20', '13:00', NULL, 'Otelo Nobleza', '09123456789', 'otelo@gmail.com', 'Sugcad, Polangui, Albay', 'k', '', '2025-10-06 04:13:34'),
(4, 0, 0, '', NULL, '2025-10-09', '16:23', NULL, 'neknek', '09123456789', 'neknek@gmail.com', 'Sugcad, Polangui, Albay', 'hehehe', '', '2025-10-06 04:24:17'),
(5, 0, 0, '', NULL, '2025-10-09', '16:23', NULL, 'neknek', '09123456789', 'neknek@gmail.com', 'Sugcad, Polangui, Albay', 'hehehe', '', '2025-10-06 04:25:29'),
(6, 0, 1, 'Post-Construction Cleaning', NULL, '2025-10-09', '10:10', NULL, 'neknek', '09123456789', 'neknek@gmail.com', 'Sugcad, Polangui, Albay', '', '', '2025-10-06 04:26:22'),
(7, 0, 1, 'Post-Construction Cleaning', NULL, '2025-10-09', '10:19', NULL, 'neknek', '09123456789', 'neknek@gmail.com', 'Sugcad, Polangui, Albay', '', 'accepted', '2025-10-06 04:31:42'),
(8, 0, 2, 'Regular Home Cleaning', 4500.00, '2025-10-09', '15:14', NULL, 'neknek', '09123456788', 'neknek@gmail.com', 'Sugcad, Polangui, Albay', 'k', '', '2025-10-06 05:14:50'),
(9, 0, 2, 'Regular Home Cleaning', 4500.00, '2025-10-07', '16:31', NULL, 'Jaiden Fermante', '09123456789', 'jaiden@gmail.com', 'Sugcad, Polangui, Albay', '', '', '2025-10-06 06:31:22'),
(10, 0, 2, 'Regular Home Cleaning', 4500.00, '2025-10-18', '06:31', NULL, 'Otelo Nobleza', '09123456789', 'otelo@gmail.com', 'Sugcad, Polangui, Albay', '', '', '2025-10-17 10:28:12'),
(11, 0, 2, 'Regular Home Cleaning', 4500.00, '2025-10-18', '23:06', NULL, 'Otelo Nobleza', '09123456789', 'otelo@gmail.com', 'Pilar, Sorsogon', '', 'accepted', '2025-10-24 15:04:26'),
(12, 0, 2, 'Regular Home Cleaning', 4500.00, '2025-10-18', '23:06', NULL, 'Otelo Nobleza', '09123456789', 'otelo@gmail.com', 'Pilar, Sorsogon', '', '', '2025-10-24 15:05:43'),
(13, 0, 2, 'Regular Home Cleaning', 4500.00, '2025-10-18', '12:30', NULL, 'Jon Matthew Mella', '09123456789', 'Mella2@gmail.com', 'Sugcad, Polangui, Albay', '', 'declined', '2025-10-24 15:29:42'),
(14, 0, 2, 'Regular Home Cleaning', 4500.00, '2025-10-18', '01:05', NULL, 'Symon Cristoffer Cano', '09123456789', 'cano@gmail.com', 'Sugcad, Polangui, Albay', '', '', '2025-10-24 16:04:57'),
(15, 0, 2, 'Regular Home Cleaning', 4500.00, '2025-11-05', '17:34', NULL, 'nigga', '09755084276', 'nigga@gmail.com', 'Pilar, Sorsogon', '', '', '2025-11-22 06:35:07'),
(16, 0, 6, 'Deep Home Cleaning', 6000.00, '2025-11-17', '20:58', NULL, 'Jaiden Fermante', '09107132211', 'jaiden2@gmail.com', 'Legazpi City', 'puke', 'accepted', '2025-11-22 07:58:35'),
(17, 0, 1, 'Post-Construction Cleaning', 5000.00, '2025-11-25', '10:19', NULL, 'Andrei LLoyd', '09685286793', 'andreilloyd@gmail.com', 'Ligao', '', '', '2025-11-24 14:19:50'),
(18, 0, 4, 'Upholstery Cleaning', 3500.00, '2025-11-30', '01:13', NULL, 'Andrei LLoyd Sinfuego', '09685286793', 'andreilloyd@gmail.com', 'Ligao', '', 'accepted', '2025-11-27 16:13:16'),
(19, 0, 1, 'Post-Construction Cleaning', 5000.00, '2025-12-07', '13:20', NULL, 'Jaiden Fermante', '09107132211', 'jaiden2@gmail.com', 'Legazpi City', '', 'declined', '2025-12-07 15:13:11'),
(20, 0, 4, 'Upholstery Cleaning', 3500.00, '2025-12-09', '12:06', NULL, 'Jaiden Fermante', '09107132211', 'jaiden2@gmail.com', 'Legazpi City', '', 'accepted', '2025-12-07 16:04:05'),
(21, 19, 2, 'Regular Home Cleaning', 4500.00, '2025-12-12', '12:00', NULL, 'xon', '09123456789', 'xon@gmail.com', 'polangui', '', 'accepted', '2025-12-10 15:52:06'),
(22, 19, 2, 'Regular Home Cleaning', 4500.00, '2025-12-12', '12:00', NULL, 'xon', '09123456789', 'xon@gmail.com', 'polangui', 'sige', 'accepted', '2025-12-10 15:55:27'),
(23, 24, 4, 'Upholstery Cleaning', 3500.00, '2025-12-11', '02:06', NULL, 'ivan', '09107132221', 'ivan@gmail.com', 'Legazpi City', '', 'accepted', '2025-12-10 18:04:47'),
(24, 24, 1, 'Post-Construction Cleaning', 5000.00, '2025-12-11', '12:23', NULL, 'ivan', '09107132221', 'ivan@gmail.com', 'Legazpi City', '', 'accepted', '2025-12-10 18:17:26'),
(25, 16, 4, 'Upholstery Cleaning', 3500.00, '2025-12-11', '11:10', NULL, 'Jaiden Fermante', '09107132211', 'jaiden2@gmail.com', 'Legazpi City', '', 'accepted', '2025-12-11 03:05:45'),
(26, 16, 3, 'Move-in/Move-out Cleaning', 3500.00, '2025-12-11', '23:00', NULL, 'Jaiden Fermante', '09107132211', 'jaiden2@gmail.com', 'Legazpi City (Landmark: likod kmart)', 'wala', 'pending', '2025-12-11 13:16:32'),
(27, 16, 4, 'Upholstery Cleaning', 3500.00, '2025-12-11', '23:11', NULL, 'Jaiden Fermante', '09107132211', 'jaiden2@gmail.com', 'Legazpi City (Landmark: likod kmart)', '', 'accepted', '2025-12-11 14:19:38'),
(28, 16, 1, 'Post-Construction Cleaning', 5000.00, '2025-12-11', '23:31', NULL, 'Jaiden Fermante', '09107132211', 'jaiden2@gmail.com', 'Legazpi City (Landmark: kmart)', '', 'declined', '2025-12-11 14:32:07'),
(29, 25, 6, 'Deep Home Cleaning', 6000.00, '2025-12-12', '01:02', NULL, 'Lloyd Diaz', '09123456789', 'lloyddiaz1@gmail.com', 'polangui (Landmark: likod kmart)', '', 'pending', '2025-12-11 16:00:33'),
(30, 25, 3, 'Move-in/Move-out Cleaning', 3500.00, '2025-12-11', '12:12', NULL, 'Lloyd Diaz', '09123456789', 'lloyddiaz1@gmail.com', 'polangui (Landmark: likod kmart)', '', 'pending', '2025-12-11 16:09:52'),
(31, 16, 7, 'Aircon Cleaning', 5000.00, '2025-12-12', '00:50', NULL, 'Jaiden Fermante', '09107132211', 'jaiden2@gmail.com', 'Legazpi City (Landmark: likod kmart)', '', 'pending', '2025-12-11 16:41:36'),
(32, 16, 6, 'Deep Home Cleaning', 6000.00, '2025-12-12', '11:07', NULL, 'Jaiden Fermante', '09107132211', 'jaiden2@gmail.com', 'Legazpi City (Landmark: likod kmart)', '', 'pending', '2025-12-12 03:06:33'),
(33, 16, 6, 'Deep Home Cleaning', 6000.00, '2025-12-19', '08:00', NULL, 'Jaiden Fermante', '09107132211', 'jaiden2@gmail.com', 'Legazpi City (Landmark: likod kmart)', '', 'pending', '2025-12-12 04:50:55'),
(34, 16, 6, 'Deep Home Cleaning', 6000.00, '2025-12-12', '17:30', NULL, 'Jaiden Fermante', '09107132211', 'jaiden2@gmail.com', 'Legazpi City (Landmark: likod kmart)', '', 'pending', '2025-12-12 08:32:47'),
(35, 26, 7, 'Aircon Cleaning', 5000.00, '2025-12-12', '16:45', NULL, 'Symon', '0969696969', 'scbc@gmail.com', 'tabaco (Landmark: butc)', 'ghjfhd', 'accepted', '2025-12-12 08:37:59'),
(36, 28, 7, 'Aircon Cleaning', 5000.00, '2025-12-13', '08:00', NULL, 'Kyla Marie', '09123456798', 'kyla4@gmail.com', 'Guinobatan (Landmark: likod kmart)', 'jjk', 'accepted', '2025-12-12 09:52:56'),
(37, 28, 6, 'Deep Home Cleaning', 6000.00, '2025-12-13', '08:00', NULL, 'Kyla Marie', '09123456798', 'kyla4@gmail.com', 'Guinobatan (Landmark: likod kmart)', 'jkk', 'accepted', '2025-12-12 09:54:20');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`) VALUES
(1, 19, 22, 'hiii', '2025-12-09 17:40:07'),
(2, 22, 19, 'grabe ka', '2025-12-09 17:57:25'),
(3, 22, 19, 'bat nawawala', '2025-12-09 18:00:24'),
(4, 19, 22, 'pangit mo', '2025-12-09 18:02:22'),
(5, 16, 22, 'hhHhH', '2025-12-11 04:53:12'),
(6, 22, 16, 'ngi', '2025-12-11 07:34:05'),
(7, 22, 16, 'ok', '2025-12-12 04:01:00'),
(8, 22, 16, 'hi', '2025-12-12 04:58:56'),
(9, 22, 16, 'kkk', '2025-12-12 07:33:54'),
(10, 16, 22, 'ok', '2025-12-12 07:37:33'),
(11, 16, 23, 'hello', '2025-12-12 08:01:43'),
(12, 16, 23, 'k', '2025-12-12 08:06:58'),
(13, 22, 16, 'ha?', '2025-12-12 08:19:43'),
(14, 16, 22, 'break na us', '2025-12-12 08:20:22'),
(15, 26, 22, 'hi po, linis time', '2025-12-12 08:38:40'),
(16, 28, 22, 'where are you now?', '2025-12-12 10:00:21'),
(17, 22, 28, 'on the way bossing.', '2025-12-12 10:02:51');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('qrcode') DEFAULT 'qrcode',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `booking_id`, `user_id`, `amount`, `payment_method`, `payment_status`, `transaction_id`, `payment_date`) VALUES
(1, 1, 1, 0.00, 'qrcode', 'paid', 'TXN1763787995', '2025-11-21 21:06:35'),
(2, 15, 1, 4500.00, 'qrcode', 'paid', 'TXN1763793309', '2025-11-21 22:35:09'),
(3, 16, 1, 6000.00, 'qrcode', 'paid', 'TXN1763798319', '2025-11-21 23:58:39'),
(4, 17, 1, 5000.00, 'qrcode', 'paid', 'TXN1763993998', '2025-11-24 06:19:58'),
(5, 18, 1, 3500.00, 'qrcode', 'paid', 'TXN1764260008', '2025-11-27 08:13:28'),
(6, 0, 1, 5000.00, 'qrcode', 'paid', 'TXN1765120392', '2025-12-07 15:13:12'),
(7, 22, 19, 2250.00, 'qrcode', 'paid', 'TXN1765382150', '2025-12-10 15:55:50'),
(8, 23, 24, 3500.00, 'qrcode', 'paid', 'TXN1765389893', '2025-12-10 18:04:53'),
(9, 24, 24, 5000.00, 'qrcode', 'paid', 'TXN1765390650', '2025-12-10 18:17:30'),
(10, 25, 16, 3500.00, 'qrcode', 'paid', 'TXN1765422350', '2025-12-11 03:05:50'),
(11, 27, 16, 3500.00, 'qrcode', 'paid', 'TXN1765462782', '2025-12-11 14:19:42'),
(12, 28, 16, 2500.00, 'qrcode', 'paid', 'TXN1765463542', '2025-12-11 14:32:22'),
(13, 29, 25, 6000.00, 'qrcode', 'paid', 'TXN1765468851', '2025-12-11 16:00:51'),
(14, 30, 25, 3500.00, 'qrcode', 'paid', 'TXN1765469396', '2025-12-11 16:09:56'),
(15, 31, 16, 2500.00, 'qrcode', 'paid', 'TXN1765471317', '2025-12-11 16:41:57'),
(16, 32, 16, 6000.00, 'qrcode', 'paid', 'TXN1765508812', '2025-12-12 03:06:52'),
(17, 34, 16, 3000.00, 'qrcode', 'paid', 'TXN1765528463', '2025-12-12 08:34:23'),
(18, 35, 26, 5000.00, 'qrcode', 'paid', 'TXN1765528690', '2025-12-12 08:38:10'),
(19, 36, 28, 5000.00, 'qrcode', 'paid', 'TXN1765533211', '2025-12-12 09:53:31'),
(20, 37, 28, 3000.00, 'qrcode', 'paid', 'TXN1765533288', '2025-12-12 09:54:48');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `date_created` timestamp NULL DEFAULT current_timestamp(),
  `last_updated` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `service_name`, `image`, `description`, `price`, `duration`, `category`, `status`, `date_created`, `last_updated`) VALUES
(1, 'Post-Construction Cleaning', 'post-construction.webp', 'Dust & debris removal after renovations for a spotless finish.', 5000.00, NULL, NULL, 'active', '2025-10-04 23:41:05', '2025-10-04 23:41:05'),
(2, 'Regular Home Cleaning', 'regular-cleaning.webp', 'Decluttering, Dry Vacuuming, Dry Wiping & Mopping, Moderate Scrubbing, Assessment, Aromatizing', 4500.00, NULL, NULL, 'active', '2025-10-05 03:16:01', '2025-10-05 03:16:01'),
(3, 'Move-in/Move-out Cleaning', 'moveinout.webp', 'Decluttering, Pre-Treatment, Dry Vacuuming, Wet Mopping, Intensive Scrubbing, Dry Mopping, Assessment, Organizing, Aromatizing', 3500.00, NULL, NULL, 'active', '2025-10-05 03:18:23', '2025-10-05 03:18:23'),
(4, 'Upholstery Cleaning', 'upholestry.webp', 'Decluttering, Pre-Treatment, Dry Vacuuming, Wet Mopping, Intensive Scrubbing, Dry Mopping, Assessment, Organizing, Aromatizing', 3500.00, NULL, NULL, 'active', '2025-10-05 03:19:58', '2025-10-05 03:19:58'),
(6, 'Deep Home Cleaning', 'deep-home-cleaning.webp', 'Decluttering, Pre-Treatment, Dry Vacuuming, Wet Mopping, Intensive Scrubbing, Dry Mopping, Assessment, Organizing, Aromatizing', 6000.00, NULL, NULL, 'active', '2025-10-05 03:24:25', '2025-10-05 03:24:25'),
(7, 'Aircon Cleaning', 'aircon-cleaning.jpg', 'Inspection, Unit Disassembly, Filter Washing, Coil Brushing, Deep Chemical Cleaning, Drain Line Flushing, Blower Cleaning, Casing Wipe-down, Drying, Reassembly, Final Testing, Aromatizing', 5000.00, NULL, NULL, 'active', '2025-12-11 16:13:03', '2025-12-11 16:13:03');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `time` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` enum('on the way','completed') DEFAULT 'on the way',
  `action_date` timestamp NULL DEFAULT current_timestamp(),
  `proof_image` varchar(255) DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `booking_id`, `customer_name`, `service_name`, `date`, `time`, `address`, `phone`, `email`, `status`, `action_date`, `proof_image`, `completed_at`) VALUES
(2, 3, 'Otelo Nobleza', 'Upholstery Cleaning', '2025-10-20', '13:00', 'Sugcad, Polangui, Albay', '09123456789', 'otelo@gmail.com', 'completed', '2025-10-17 02:19:31', NULL, NULL),
(3, 4, 'neknek', '', '2025-10-09', '16:23', 'Sugcad, Polangui, Albay', '09123456789', 'neknek@gmail.com', 'completed', '2025-10-17 02:26:07', NULL, NULL),
(4, 10, 'Otelo Nobleza', 'Regular Home Cleaning', '2025-10-18', '06:31', 'Sugcad, Polangui, Albay', '09123456789', 'otelo@gmail.com', 'completed', '2025-10-17 02:29:11', NULL, NULL),
(5, 14, 'Symon Cristoffer Cano', 'Regular Home Cleaning', '2025-10-18', '01:05', 'Sugcad, Polangui, Albay', '09123456789', 'cano@gmail.com', 'completed', '2025-10-24 08:14:27', NULL, NULL),
(6, 12, 'Otelo Nobleza', 'Regular Home Cleaning', '2025-10-18', '23:06', 'Pilar, Sorsogon', '09123456789', 'otelo@gmail.com', 'completed', '2025-10-24 08:20:56', NULL, NULL),
(7, 11, 'Otelo Nobleza', 'Regular Home Cleaning', '2025-10-18', '23:06', 'Pilar, Sorsogon', '09123456789', 'otelo@gmail.com', 'completed', '2025-10-24 08:22:05', 'uploads/completion_proofs/completion_11_1765419499.png', '2025-12-11 02:18:19'),
(8, 15, 'nigga', 'Regular Home Cleaning', '2025-11-05', '17:34', 'Pilar, Sorsogon', '09755084276', 'nigga@gmail.com', 'completed', '2025-11-21 22:35:59', NULL, NULL),
(9, 17, 'Andrei LLoyd', 'Post-Construction Cleaning', '2025-11-25', '10:19', 'Ligao', '09685286793', 'andreilloyd@gmail.com', 'completed', '2025-11-24 06:51:50', NULL, NULL),
(10, 16, 'Jaiden Fermante', 'Deep Home Cleaning', '2025-11-17', '20:58', 'Legazpi City', '09107132211', 'jaiden2@gmail.com', 'completed', '2025-11-24 06:56:21', 'uploads/completion_proofs/completion_16_1765419161.png', '2025-12-11 02:12:41'),
(12, 0, '', '', '0000-00-00', NULL, NULL, NULL, NULL, 'completed', '2025-12-07 16:58:28', 'uploads/completion_proofs/completion_0_1765126745.png', '2025-12-07 16:59:05'),
(13, 22, '', '', '0000-00-00', NULL, NULL, NULL, NULL, 'completed', '2025-12-10 18:00:58', 'uploads/completion_proofs/completion_22_1765389676.png', '2025-12-10 18:01:16'),
(14, 23, '', '', '0000-00-00', NULL, NULL, NULL, NULL, 'completed', '2025-12-10 18:06:25', 'uploads/completion_proofs/completion_23_1765389998.png', '2025-12-10 18:06:38'),
(15, 24, '', '', '0000-00-00', NULL, NULL, NULL, NULL, 'completed', '2025-12-10 18:18:45', 'uploads/completion_proofs/completion_24_1765390740.png', '2025-12-10 18:19:00'),
(16, 7, '', '', '0000-00-00', NULL, NULL, NULL, NULL, 'completed', '2025-12-11 02:23:59', 'uploads/completion_proofs/completion_7_1765419848.png', '2025-12-11 02:24:08'),
(17, 18, '', '', '0000-00-00', NULL, NULL, NULL, NULL, 'completed', '2025-12-11 02:32:17', 'uploads/completion_proofs/completion_18_1765420919.png', '2025-12-11 02:41:59'),
(18, 25, 'Jaiden Fermante', 'Upholstery Cleaning', '2025-12-11', '11:10', 'Legazpi City', '', 'jaiden2@gmail.com', 'completed', '2025-12-11 03:06:39', 'uploads/completion_proofs/completion_25_1765422410.png', '2025-12-11 03:06:50'),
(19, 21, 'xon', 'Regular Home Cleaning', '2025-12-12', '12:00', 'polangui', '', 'xon@gmail.com', 'completed', '2025-12-11 03:40:22', 'uploads/completion_proofs/completion_21_1765424974.png', '2025-12-11 03:49:34'),
(20, 20, 'Jaiden Fermante', 'Upholstery Cleaning', '2025-12-09', '12:06', 'Legazpi City', '09107132211', 'jaiden2@gmail.com', 'completed', '2025-12-11 03:54:44', 'uploads/completion_proofs/completion_20_1765425293.png', '2025-12-11 03:54:53'),
(21, 27, 'Jaiden Fermante', 'Upholstery Cleaning', '2025-12-11', '23:11', 'Legazpi City (Landmark: likod kmart)', '09107132211', 'jaiden2@gmail.com', 'completed', '2025-12-11 14:20:22', 'uploads/completion_proofs/completion_27_1765462834.png', '2025-12-11 14:20:34'),
(22, 35, 'Symon', 'Aircon Cleaning', '2025-12-12', '16:45', 'tabaco (Landmark: butc)', '0969696969', 'scbc@gmail.com', 'completed', '2025-12-12 08:39:46', 'uploads/completion_proofs/completion_35_1765528939.png', '2025-12-12 08:42:19'),
(23, 36, 'Kyla Marie', 'Aircon Cleaning', '2025-12-13', '08:00', 'Guinobatan (Landmark: likod kmart)', '09123456798', 'kyla4@gmail.com', 'on the way', '2025-12-12 09:57:42', NULL, NULL),
(24, 37, 'Kyla Marie', 'Deep Home Cleaning', '2025-12-13', '08:00', 'Guinobatan (Landmark: likod kmart)', '09123456798', 'kyla4@gmail.com', 'completed', '2025-12-12 09:58:08', 'uploads/completion_proofs/completion_37_1765533831.png', '2025-12-12 10:03:51');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_pic` varchar(255) NOT NULL,
  `address` varchar(100) DEFAULT NULL,
  `contact_num` varchar(50) DEFAULT NULL,
  `role` enum('admin','customer','cleaner','') NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `name`, `email`, `password`, `profile_pic`, `address`, `contact_num`, `role`, `date_created`, `last_updated`) VALUES
(1, 'otelo', 'otelo@gmail.com', '$2y$10$AvPI4xIX0eXa7i9pM/h0zOFq8JD2XgJPcu28MkEW/kv', '', 'Sugcad, Polangui, Albay', '09123456789', 'customer', '2025-10-04 19:09:14', '2025-10-04 19:09:14'),
(2, 'otelo1', 'otelo1@gmail.com', '$2y$10$wmvnpX9QGpUzQ40uIElzje6URYfpFoC12bHInNlDQ8e', '', 'Sugcad, Polangui, Albay', '09123456789', 'customer', '2025-10-04 23:30:40', '2025-10-04 23:30:40'),
(3, 'neknek', 'neknek@gmail.com', '$2y$10$ZioQhcG7SYZR91nuDOx.tu1dXJD2nJrHmgqxDJHD43P', '', 'Sugcad, Polangui, Albay', '09123456789', 'customer', '2025-10-05 04:06:40', '2025-10-05 04:06:40'),
(4, 'Jaiden Fermante', 'jaiden@gmail.com', '$2y$10$BwFjr3kEO.pfi/05zmXXlOCtUEQgzoJA1YbNFNPfDrC', '', 'Sugcad, Polangui, Albay', '09123456789', 'customer', '2025-10-05 22:29:07', '2025-10-05 22:29:07'),
(5, 'jon matthew mella', 'Mella2@gmail.com', '$2y$10$JVGvRFFpcbiotIUUMTRH/OAZsOnedE4jjMUi8JDuuxl', '', 'Sugcad, Polangui, Albay', '09123456789', 'customer', '2025-10-24 07:26:30', '2025-10-24 07:26:30'),
(6, 'Symon Cristoffer Cano', 'cano@gmail.com', '$2y$10$7zraWRxY2ztaFVwBG3G15eff.PhncYFp4G2A1JI1UWz', '', 'Sugcad, Polangui, Albay', '09123456789', 'customer', '2025-10-24 08:02:57', '2025-10-24 08:02:57'),
(7, 'nigga', 'nigga@gmail.com', '$2y$10$tv6SY4AuVuKYepghv7EcOuGC7qTg6kdv2r3SxMIPpJi', '', 'Pilar, Sorsogon', '09755084276', 'customer', '2025-11-21 21:25:47', '2025-11-21 21:25:47'),
(8, 'Khritine Botin', 'Botin@gmail.com', '$2y$10$1T.tmiFEy4U0i7qZJbbYxO7UneZWUukCDlUsoJYV7xk', '', 'polangui', '09123456789', 'customer', '2025-11-21 22:52:51', '2025-11-21 22:52:51'),
(9, 'Pula', 'pula@gmail.com', '$2y$10$0O/cei.QobwLLNGS3o1UBuAzXWZyKv56dzq1pdfQZ46', '', 'polangui', '09123456789', 'customer', '2025-11-21 23:03:41', '2025-11-21 23:03:41'),
(10, 'Bo10', 'Bo10@gmail.com', '$2y$10$SZFhqJ3hix60UXDZqqV9zeP3gHwCxZGgZAwyD92luRN', '', 'polangui', '09123456789', 'customer', '2025-11-21 23:09:41', '2025-11-21 23:09:41'),
(11, 'plats', 'plat@gmail.com', '$2y$10$ew.FtQY27pEJDI6lLeAqUODOtiuIowMOeiA6gxbsmbX', '', 'polangui', '09123456789', 'customer', '2025-11-21 23:15:13', '2025-11-21 23:15:13'),
(12, 'kyla', 'kyla@gmail.com', '$2y$10$llbwYhewOaMMeGfixl.bDuoEU8yWrpfi/pI30JhKIE2', '', 'polangui', '09123456789', 'customer', '2025-11-21 23:19:17', '2025-11-21 23:19:17'),
(13, 'kyla1', 'kyla1@gmail.com', '$2y$10$WQh4O.JHVf4mb8ODK87.seVKPcQWieK733xYviD/7ZE', '', 'polangui', '09123456789', 'customer', '2025-11-21 23:30:39', '2025-11-21 23:30:39'),
(14, 'Zai', 'zai@gmail.com', '$2y$10$05PCm3yTAGU2EtGlev6lQuwPBkxn/NZGvhyLyeiKlOR', '', 'polangui', '09755084276', 'customer', '2025-11-21 23:37:30', '2025-11-21 23:37:30'),
(15, '123', '123@gmail.com', '$2y$10$yNo5xwDa6LS7o33turyaVu9purS9AjXot55LaQ1bs06', '', 'polangui', '09755084276', 'customer', '2025-11-21 23:40:25', '2025-11-21 23:40:25'),
(16, 'Jaiden Fermante', 'jaiden2@gmail.com', '$2y$10$M96FUsKL.OCCdKT14Yeos.FKvQF2IkyuEAstusUBAZLx4BS0taIDG', 'profile_16_1765119811.png', 'Legazpi City', '09107132211', 'customer', '2025-11-21 23:49:48', '2025-12-07 15:03:31'),
(17, 'Andrei LLoyd Sinfuego', 'andreilloyd@gmail.com', '$2y$10$Nt/SUlJjGIHCH1PGPIRjtOPpmAYihbIvRpV7S1v5qPzW7oRaSTkkm', '', 'Ligao', '09685286793', 'customer', '2025-11-24 06:18:21', '2025-11-24 06:40:32'),
(18, 'Lloyd Diaz', 'lloyddiaz@gmail.com', '$2y$10$Bexjaig/EckTFqINniioAOzgtqJYF4Z8.BZaySKeXQcJ9rlytm9Xy', '', 'polangui', '09123456789', 'customer', '2025-11-29 14:48:33', '2025-11-29 14:48:33'),
(19, 'xon', 'xon@gmail.com', '$2y$10$PqPVeJj5ROTDwGyfOu7lc.HX048hyWw.RVBuRY3KM5snTnI3feygS', '', 'polangui', '09123456789', 'customer', '2025-12-07 16:47:18', '2025-12-07 16:47:18'),
(20, 'Rich', 'rich@gmail.com', '$2y$10$Q4MwZ9MNm3M.H4ubgRS2G.ErZe7E9yYyo10..pSESCNq5P9qisrpK', '', 'Legazpi City', '09107132221', 'customer', '2025-12-08 16:10:55', '2025-12-08 16:10:55'),
(21, 'Admin', 'admin@quickclean.com', '$2y$10$xb/BcrJcNKR/FjAhWP4oSe686J1lVkVkOlQQfcVLX0.1S4/BcQVQO', '', NULL, NULL, 'admin', '2025-12-09 17:22:02', '2025-12-09 17:22:02'),
(22, 'Otelo P. Nobleza', 'cleaner@gmail.com', '$2y$10$iKgF73Nf0Fg2LmR95Z.qI.LJQ70RzLucAo.FzLwTB3s0r85K5feA2', 'uploads/1765379905_Screenshot 2025-10-14 140633.png', 'polangui', '09755084276', 'cleaner', '2025-12-09 17:32:43', '2025-12-12 08:22:32'),
(24, 'ivan', 'ivan@gmail.com', '$2y$10$K8zcrtKuG2vMiTRvgITycuh07H0C.PFPtJ5cWTqcNJU.LcM0ECLsq', '', 'Legazpi City', '09107132221', 'customer', '2025-12-10 18:04:02', '2025-12-10 18:04:02'),
(25, 'Lloyd Diaz', 'lloyddiaz1@gmail.com', '$2y$10$BqsRMz1bFk.UP99WrVolH.6adpltURwJDBncb2EyPPWYJjF6eanPu', 'profile_25_1765468881.png', 'polangui', '09123456789', 'customer', '2025-12-11 15:57:15', '2025-12-11 16:01:21'),
(26, 'Symon', 'scbc@gmail.com', '$2y$10$3U6jC/8EjhTBGyB8Ac9hbOHqrmBksjrd479c9XoLc6kTPgzy.F1Ce', '', 'tabaco', '0969696969', 'customer', '2025-12-12 08:35:52', '2025-12-12 08:35:52'),
(27, 'redz', 'redz@gmail.com', '$2y$10$aECBCCoLRQj9X1JCpkx5b.FroSW/fHvXOF7.bTQX3oBafc9FVM6yW', '', 'polangui', '09123456789', 'customer', '2025-12-12 08:45:10', '2025-12-12 08:45:10'),
(28, 'Kyla Marie', 'kyla4@gmail.com', '$2y$10$HtFEfX/Bl7ckjl4H3BvjQecTrCXl3b.NxXbvHfbHK7U6/SyfesKq6', 'profile_28_1765533349.webp', 'Guinobatan', '09123456798', 'customer', '2025-12-12 09:50:35', '2025-12-12 09:55:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
