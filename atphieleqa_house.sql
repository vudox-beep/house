-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 24, 2026 at 10:45 AM
-- Server version: 11.4.10-MariaDB-cll-lve
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `atphieleqa_house`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_role` varchar(50) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `user_role`, `action`, `description`, `ip_address`, `created_at`) VALUES
(1, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.225', '2026-03-01 14:07:52'),
(2, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.225', '2026-03-01 14:08:00'),
(3, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.225', '2026-03-01 14:08:24'),
(4, NULL, 'dealer', 'register', 'New user registered: chisalaluckykk5@gmail.com (dealer)', '165.56.186.225', '2026-03-01 14:09:51'),
(5, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.47', '2026-03-01 14:12:19'),
(6, 2, 'admin', 'delete_user', 'Deleted user ID: 46', '165.56.186.47', '2026-03-01 14:12:33'),
(7, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.30', '2026-03-01 14:18:57'),
(8, 4, 'dealer', 'login', 'User logged in successfully', '45.215.224.192', '2026-03-01 14:56:08'),
(9, 2, 'admin', 'login', 'User logged in successfully', '45.215.224.192', '2026-03-01 14:57:19'),
(10, 2, 'admin', 'login', 'User logged in successfully', '45.215.224.192', '2026-03-01 15:21:46'),
(11, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.42', '2026-03-01 17:56:33'),
(12, 2, 'admin', 'login', 'User logged in successfully', '45.215.255.229', '2026-03-01 18:13:12'),
(13, 2, 'admin', 'login', 'User logged in successfully', '45.215.255.229', '2026-03-01 18:28:26'),
(14, 2, 'admin', 'login', 'User logged in successfully', '45.215.255.229', '2026-03-01 19:07:37'),
(15, 2, 'admin', 'login', 'User logged in successfully', '45.215.255.229', '2026-03-01 20:05:13'),
(16, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.101', '2026-03-01 20:15:25'),
(17, 2, 'admin', 'login', 'User logged in successfully', '45.215.255.71', '2026-03-02 04:56:00'),
(18, 2, 'admin', 'login', 'User logged in successfully', '45.215.249.19', '2026-03-02 06:42:19'),
(19, 2, 'admin', 'login', 'User logged in successfully', '165.56.183.227', '2026-03-02 07:15:42'),
(20, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.78', '2026-03-02 08:36:07'),
(21, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.232', '2026-03-02 10:31:41'),
(22, 47, 'user', 'register', 'New user registered: frank.t.r.b59@gmail.com (user)', '51.77.74.105', '2026-03-02 11:46:37'),
(23, 47, 'user', 'login', 'User logged in successfully', '51.77.74.105', '2026-03-02 11:47:35'),
(24, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.97', '2026-03-02 12:18:24'),
(25, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.243', '2026-03-02 14:42:10'),
(26, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.192', '2026-03-02 15:12:17'),
(27, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.192', '2026-03-02 15:41:09'),
(28, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.192', '2026-03-02 15:42:01'),
(29, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.192', '2026-03-02 16:24:58'),
(30, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.192', '2026-03-02 16:51:11'),
(31, 2, 'admin', 'login', 'User logged in successfully', '165.56.183.236', '2026-03-02 20:21:56'),
(32, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.144', '2026-03-02 20:58:38'),
(33, 2, 'admin', 'login', 'User logged in successfully', '165.56.183.233', '2026-03-02 21:06:16'),
(34, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.59', '2026-03-02 22:11:48'),
(35, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.79', '2026-03-02 22:48:22'),
(36, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.227', '2026-03-02 23:02:33'),
(37, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.227', '2026-03-02 23:05:38'),
(38, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.23', '2026-03-02 23:26:27'),
(39, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.23', '2026-03-02 23:44:38'),
(40, 49, 'dealer', 'register', 'New user registered: deleyi5268@ostahie.com (dealer)', '104.28.46.100', '2026-03-03 04:23:23'),
(41, 49, 'dealer', 'login', 'User logged in successfully', '104.28.46.100', '2026-03-03 04:24:13'),
(42, 2, 'admin', 'login', 'User logged in successfully', '165.56.183.146', '2026-03-03 06:05:59'),
(43, 2, 'admin', 'login', 'User logged in successfully', '165.56.183.146', '2026-03-03 06:07:16'),
(44, 4, 'dealer', 'login', 'User logged in successfully', '165.56.183.146', '2026-03-03 06:13:17'),
(45, 2, 'admin', 'login', 'User logged in successfully', '165.56.183.146', '2026-03-03 06:39:47'),
(46, 4, 'dealer', 'login', 'User logged in successfully', '165.56.183.146', '2026-03-03 06:41:51'),
(47, 2, 'admin', 'login', 'User logged in successfully', '165.56.183.146', '2026-03-03 06:46:56'),
(48, 2, 'admin', 'login', 'User logged in successfully', '165.56.183.146', '2026-03-03 06:54:27'),
(49, 2, 'admin', 'ban_user', 'Changed ban status for user ID: 49 to 1', '165.56.183.146', '2026-03-03 06:55:45'),
(50, 2, 'admin', 'unban_user', 'Changed ban status for user ID: 49 to 0', '165.56.183.146', '2026-03-03 06:56:06'),
(51, 2, 'admin', 'login', 'User logged in successfully', '165.56.183.79', '2026-03-03 08:50:22'),
(52, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.141', '2026-03-03 14:48:28'),
(53, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.112', '2026-03-03 17:40:36'),
(54, 2, 'admin', 'login', 'User logged in successfully', '165.56.183.51', '2026-03-03 18:46:00'),
(55, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.181', '2026-03-03 20:21:48'),
(56, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.125', '2026-03-04 04:37:08'),
(57, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.191', '2026-03-04 06:44:38'),
(58, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.191', '2026-03-04 06:47:17'),
(59, 2, 'admin', 'login', 'User logged in successfully', '165.56.183.45', '2026-03-04 10:31:31'),
(60, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-04 15:19:29'),
(61, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-04 19:27:22'),
(62, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.9', '2026-03-04 20:40:54'),
(63, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.9', '2026-03-04 20:42:37'),
(64, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.186', '2026-03-04 21:09:11'),
(65, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.109', '2026-03-04 21:47:49'),
(66, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.109', '2026-03-04 22:12:41'),
(67, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.109', '2026-03-04 22:15:24'),
(68, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.109', '2026-03-04 22:36:40'),
(69, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.109', '2026-03-04 23:01:58'),
(70, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.109', '2026-03-04 23:06:49'),
(71, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.109', '2026-03-04 23:10:20'),
(72, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.109', '2026-03-04 23:11:48'),
(73, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.109', '2026-03-04 23:20:43'),
(74, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.109', '2026-03-04 23:23:36'),
(75, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.148', '2026-03-05 05:12:00'),
(76, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-05 12:04:26'),
(77, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.53', '2026-03-05 15:30:09'),
(78, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.53', '2026-03-05 16:17:46'),
(79, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.53', '2026-03-05 16:27:40'),
(80, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.53', '2026-03-05 16:29:36'),
(81, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.53', '2026-03-05 16:38:13'),
(82, 50, 'dealer', 'register', 'New user registered: dannynkhata6@gmail.com (dealer)', '45.215.237.154', '2026-03-05 16:41:52'),
(83, 50, 'dealer', 'login', 'User logged in successfully', '45.215.237.154', '2026-03-05 16:42:58'),
(84, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.53', '2026-03-05 16:52:52'),
(85, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.53', '2026-03-05 16:58:48'),
(86, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.53', '2026-03-05 17:03:28'),
(87, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.53', '2026-03-05 17:15:30'),
(88, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.53', '2026-03-05 17:20:12'),
(89, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.53', '2026-03-05 17:38:14'),
(90, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.53', '2026-03-05 17:57:11'),
(91, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.53', '2026-03-05 18:12:10'),
(92, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.53', '2026-03-05 18:15:41'),
(93, 2, 'admin', 'login', 'User logged in successfully', '45.215.255.122', '2026-03-05 20:17:57'),
(94, 2, 'admin', 'login', 'User logged in successfully', '45.215.255.122', '2026-03-05 20:31:15'),
(95, NULL, 'user', 'register', 'New user registered: chisalavudo@gmail.com (user)', '165.57.81.152', '2026-03-05 20:37:32'),
(96, NULL, 'user', 'login', 'User logged in successfully', '165.57.81.152', '2026-03-05 20:38:27'),
(97, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.152', '2026-03-05 20:38:44'),
(98, 2, 'admin', 'delete_user', 'Deleted user ID: 55', '165.57.81.152', '2026-03-05 20:39:08'),
(99, NULL, 'user', 'register', 'New user registered: chisalavudo@gmail.com (user)', '165.57.81.152', '2026-03-05 20:40:18'),
(100, NULL, 'user', 'login', 'User logged in successfully', '165.57.81.152', '2026-03-05 20:40:56'),
(101, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.152', '2026-03-05 20:41:10'),
(102, 2, 'admin', 'delete_user', 'Deleted user ID: 56', '165.57.81.152', '2026-03-05 20:41:27'),
(103, NULL, 'dealer', 'register', 'New user registered: chisalavudo@gmail.com (dealer)', '165.57.81.152', '2026-03-05 20:42:01'),
(104, NULL, 'dealer', 'login', 'User logged in successfully', '165.57.81.152', '2026-03-05 20:42:21'),
(105, 2, 'admin', 'login', 'User logged in successfully', '45.215.255.122', '2026-03-05 21:07:01'),
(106, 2, 'admin', 'delete_user', 'Deleted user ID: 58', '165.57.81.29', '2026-03-05 21:25:49'),
(107, NULL, 'dealer', 'login', 'User logged in successfully', '165.57.81.29', '2026-03-05 21:27:22'),
(108, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.29', '2026-03-05 21:28:21'),
(109, 4, 'dealer', 'login', 'User logged in successfully', '165.57.81.29', '2026-03-05 21:30:36'),
(110, 2, 'admin', 'login', 'User logged in successfully', '45.215.249.43', '2026-03-06 07:31:51'),
(111, 4, 'dealer', 'login', 'User logged in successfully', '45.215.249.43', '2026-03-06 07:46:04'),
(112, NULL, 'dealer', 'login', 'User logged in successfully', '45.215.249.43', '2026-03-06 07:48:43'),
(113, 4, 'dealer', 'login', 'User logged in successfully', '45.215.236.73', '2026-03-06 09:31:45'),
(114, 4, 'dealer', 'login', 'User logged in successfully', '45.215.236.73', '2026-03-06 09:49:08'),
(115, NULL, 'dealer', 'register', 'New user registered: gggeraldshamulanga@gmail.com (dealer)', '102.147.237.46', '2026-03-06 10:11:54'),
(116, NULL, 'dealer', 'login', 'User logged in successfully', '102.147.237.46', '2026-03-06 10:12:33'),
(117, NULL, 'dealer', 'login', 'User logged in successfully', '102.147.237.46', '2026-03-06 10:15:02'),
(118, NULL, 'dealer', 'login', 'User logged in successfully', '41.216.95.230', '2026-03-06 10:16:05'),
(119, NULL, 'dealer', 'login', 'User logged in successfully', '41.216.95.230', '2026-03-06 10:17:38'),
(120, NULL, 'dealer', 'login', 'User logged in successfully', '41.216.95.230', '2026-03-06 10:17:38'),
(121, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.73', '2026-03-06 10:21:08'),
(122, NULL, 'dealer', 'login', 'User logged in successfully', '41.216.95.230', '2026-03-06 10:33:02'),
(123, NULL, 'dealer', 'register', 'New user registered: chisalavudo@gmail.com (dealer)', '45.215.236.73', '2026-03-06 10:33:04'),
(124, NULL, 'dealer', 'login', 'User logged in successfully', '45.215.236.73', '2026-03-06 10:33:22'),
(125, NULL, 'dealer', 'login', 'User logged in successfully', '45.215.236.73', '2026-03-06 10:39:35'),
(126, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.73', '2026-03-06 10:45:31'),
(127, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.73', '2026-03-06 12:03:35'),
(128, 2, 'admin', 'delete_user', 'Deleted user ID: 64', '45.215.236.73', '2026-03-06 12:03:59'),
(129, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.73', '2026-03-06 13:09:51'),
(130, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.237', '2026-03-06 13:38:24'),
(131, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.237', '2026-03-06 13:41:56'),
(132, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.237', '2026-03-06 13:42:18'),
(133, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.237', '2026-03-06 13:43:20'),
(134, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.237', '2026-03-06 14:26:44'),
(135, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.237', '2026-03-06 15:05:27'),
(136, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.237', '2026-03-06 15:13:30'),
(137, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.237', '2026-03-06 16:26:47'),
(138, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.27', '2026-03-06 17:10:25'),
(139, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-07 07:40:36'),
(140, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-07 08:14:33'),
(141, 2, 'admin', 'update_subscription', 'Updated subscription for user ID: 63. Status: inactive, Expiry: 2026-04-05', '102.208.220.203', '2026-03-07 08:15:37'),
(142, 4, 'dealer', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-07 08:26:15'),
(143, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-07 12:00:15'),
(144, 2, 'admin', 'send_email', 'Sent email to user ID: 50. Subject: subscription', '102.208.220.203', '2026-03-07 12:24:03'),
(145, 2, 'admin', 'send_email', 'Sent email to user ID: 66. Subject: verification', '102.208.220.203', '2026-03-07 12:25:16'),
(146, 2, 'admin', 'send_email', 'Sent email to user ID: 37. Subject: verification', '102.208.220.203', '2026-03-07 12:25:36'),
(147, 2, 'admin', 'send_email', 'Sent email to user ID: 63. Subject: Happy customer', '102.208.220.203', '2026-03-07 12:30:11'),
(148, 2, 'admin', 'send_email', 'Sent email to user ID: 4. Subject: verification', '102.208.220.203', '2026-03-07 12:40:15'),
(149, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.206', '2026-03-07 13:52:33'),
(150, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-07 14:13:42'),
(151, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-07 14:48:15'),
(152, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.202', '2026-03-07 15:47:26'),
(153, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.202', '2026-03-07 18:08:41'),
(154, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-07 18:58:57'),
(155, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-08 06:08:48'),
(156, 4, 'dealer', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-08 08:40:33'),
(157, 4, 'dealer', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-08 08:48:03'),
(158, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.200', '2026-03-08 10:42:21'),
(159, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-08 13:16:08'),
(160, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.206', '2026-03-08 14:15:45'),
(161, 4, 'dealer', 'login', 'User logged in successfully', '102.208.220.206', '2026-03-08 14:16:08'),
(162, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.202', '2026-03-08 16:25:24'),
(163, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-08 17:17:27'),
(164, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-08 19:12:33'),
(165, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-08 19:12:37'),
(166, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-09 06:47:10'),
(167, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-09 06:47:32'),
(168, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-09 10:46:18'),
(169, NULL, 'user', 'register', 'New user registered: josphatmp.86@gmail.com (user)', '41.60.174.186', '2026-03-09 12:19:04'),
(170, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-09 14:16:12'),
(171, 2, 'admin', 'send_email', 'Sent email to user ID: 67. Subject: Welcome', '102.208.220.203', '2026-03-09 14:17:43'),
(172, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.103', '2026-03-09 19:51:31'),
(173, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.85', '2026-03-09 21:28:06'),
(174, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.85', '2026-03-09 21:28:22'),
(175, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.85', '2026-03-09 21:34:39'),
(176, 2, 'admin', 'delete_user', 'Deleted user ID: 65', '165.58.129.85', '2026-03-09 21:35:55'),
(177, NULL, 'user', 'register', 'New user registered: chisalavudo@gmail.com (user)', '165.58.129.85', '2026-03-09 21:36:35'),
(178, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.85', '2026-03-09 22:34:10'),
(179, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.85', '2026-03-09 22:44:58'),
(180, 2, 'admin', 'delete_user', 'Deleted user ID: 37', '165.58.129.85', '2026-03-09 22:46:03'),
(181, NULL, 'user', 'register', 'New user registered: chisalaluckson27@gmail.com (user)', '165.58.129.85', '2026-03-09 22:47:33'),
(182, 2, 'admin', 'delete_user', 'Deleted user ID: 37', '165.58.129.85', '2026-03-09 22:47:41'),
(183, 2, 'admin', 'delete_user', 'Deleted user ID: 37', '165.58.129.85', '2026-03-09 22:48:05'),
(184, 2, 'admin', 'delete_user', 'Deleted user ID: 68', '165.58.129.85', '2026-03-09 22:48:25'),
(185, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.87', '2026-03-10 04:39:33'),
(186, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.166', '2026-03-10 06:16:15'),
(187, NULL, 'dealer', 'register', 'New user registered: kambwanga2@gmail.com (dealer)', '165.58.129.161', '2026-03-10 07:44:26'),
(188, NULL, 'dealer', 'login', 'User logged in successfully', '165.58.129.161', '2026-03-10 07:46:45'),
(189, NULL, 'dealer', 'login', 'User logged in successfully', '165.58.129.161', '2026-03-10 07:49:28'),
(190, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.84', '2026-03-10 11:03:52'),
(191, 2, 'admin', 'send_email', 'Sent email to user ID: 70. Subject: Verification ', '165.58.129.84', '2026-03-10 11:06:31'),
(192, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.84', '2026-03-10 12:05:03'),
(193, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.84', '2026-03-10 12:08:19'),
(194, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.201', '2026-03-10 13:37:08'),
(195, 2, 'admin', 'ban_user', 'Changed ban status for user ID: 70 to 1', '165.56.186.201', '2026-03-10 13:39:07'),
(196, 2, 'admin', 'delete_user', 'Deleted user ID: 69', '165.56.186.201', '2026-03-10 13:40:44'),
(197, NULL, 'dealer', 'register', 'New user registered: chisalaluckson27@gmail.com (dealer)', '165.56.186.201', '2026-03-10 13:42:01'),
(198, 2, 'admin', 'delete_user', 'Deleted user ID: 69', '165.56.186.201', '2026-03-10 13:42:11'),
(199, NULL, 'dealer', 'login', 'User logged in successfully', '165.56.186.201', '2026-03-10 13:42:35'),
(200, 2, 'admin', 'delete_user', 'Deleted user ID: 69', '165.56.186.201', '2026-03-10 13:43:06'),
(201, 2, 'admin', 'delete_user', 'Deleted user ID: 71', '165.56.186.201', '2026-03-10 14:04:28'),
(202, NULL, 'dealer', 'register', 'New user registered: chisalaluckson27@gmail.com (dealer)', '165.56.186.201', '2026-03-10 14:07:11'),
(203, NULL, 'dealer', 'login', 'User logged in successfully', '165.56.186.201', '2026-03-10 14:07:45'),
(204, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.201', '2026-03-10 14:24:25'),
(205, NULL, 'dealer', 'login', 'User logged in successfully', '165.56.186.201', '2026-03-10 14:26:36'),
(206, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.201', '2026-03-10 14:27:40'),
(207, 2, 'admin', 'delete_user', 'Deleted user ID: 72', '165.56.186.201', '2026-03-10 14:27:54'),
(208, 2, 'admin', 'unban_user', 'Changed ban status for user ID: 70 to 0', '165.56.186.201', '2026-03-10 14:28:03'),
(209, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.201', '2026-03-10 14:29:28'),
(210, 2, 'admin', 'verify_user', 'Updated verification for user ID: 70 to 1', '165.56.186.201', '2026-03-10 14:31:04'),
(211, 2, 'admin', 'verify_user', 'Updated verification for user ID: 70 to 0', '165.56.186.201', '2026-03-10 14:31:08'),
(212, 2, 'admin', 'ban_user', 'Changed ban status for user ID: 70 to 1', '165.56.186.201', '2026-03-10 14:32:19'),
(213, 2, 'admin', 'delete_user', 'Deleted user ID: 70', '165.56.186.201', '2026-03-10 14:32:26'),
(214, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.201', '2026-03-10 15:30:01'),
(215, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.233', '2026-03-10 20:06:27'),
(216, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.106', '2026-03-11 04:16:11'),
(217, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.244', '2026-03-11 07:52:58'),
(218, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.14', '2026-03-11 12:34:04'),
(219, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.97', '2026-03-11 14:04:51'),
(220, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.98', '2026-03-11 15:24:02'),
(221, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.98', '2026-03-11 15:27:02'),
(222, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.98', '2026-03-11 15:33:04'),
(223, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.98', '2026-03-11 16:25:48'),
(224, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.98', '2026-03-11 16:30:45'),
(225, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.98', '2026-03-11 16:46:34'),
(226, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.98', '2026-03-11 17:12:13'),
(227, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.98', '2026-03-11 17:14:17'),
(228, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.98', '2026-03-11 17:20:14'),
(229, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.98', '2026-03-11 17:24:58'),
(230, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.98', '2026-03-11 17:27:20'),
(231, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.98', '2026-03-11 18:12:21'),
(232, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.98', '2026-03-11 18:27:26'),
(233, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.98', '2026-03-11 18:27:54'),
(234, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.145', '2026-03-11 23:38:19'),
(235, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.145', '2026-03-12 04:55:25'),
(236, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.254', '2026-03-12 07:59:15'),
(237, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.70', '2026-03-12 09:09:33'),
(238, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.54', '2026-03-12 15:40:24'),
(239, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.98', '2026-03-12 17:59:23'),
(240, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.47', '2026-03-12 19:40:43'),
(241, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.62', '2026-03-13 10:05:37'),
(242, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.63', '2026-03-13 12:39:41'),
(243, 4, 'dealer', 'login', 'User logged in successfully', '165.57.81.206', '2026-03-13 14:04:57'),
(244, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.206', '2026-03-13 14:32:23'),
(245, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.18', '2026-03-13 15:52:11'),
(246, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-13 20:06:02'),
(247, NULL, 'user', 'register', 'New user registered: aja.l.i.va.gosi5.36@gmail.com (user)', '185.129.62.62', '2026-03-14 02:44:31'),
(248, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.85', '2026-03-14 05:04:45'),
(249, 2, 'admin', 'ban_user', 'Changed ban status for user ID: 73 to 1', '165.56.186.85', '2026-03-14 05:05:23'),
(250, 2, 'admin', 'delete_user', 'Deleted user ID: 73', '165.56.186.85', '2026-03-14 05:05:28'),
(251, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.9', '2026-03-14 05:38:27'),
(252, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.9', '2026-03-14 05:38:42'),
(253, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.206', '2026-03-14 07:57:35'),
(254, 2, 'admin', 'send_email', 'Sent email to user ID: 67. Subject: Verification ', '102.208.220.200', '2026-03-14 07:59:33'),
(255, 2, 'admin', 'send_email', 'Sent email to user ID: 49. Subject: Property upload ', '102.208.220.203', '2026-03-14 08:02:13'),
(256, 2, 'admin', 'send_email', 'Sent email to user ID: 50. Subject: Property ', '102.208.220.202', '2026-03-14 08:03:54'),
(257, 2, 'admin', 'send_email', 'Sent email to user ID: 66. Subject: Verification ', '102.208.220.203', '2026-03-14 08:05:17'),
(258, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-14 08:10:59'),
(259, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-14 08:34:18'),
(260, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.235', '2026-03-14 09:38:36'),
(261, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.109', '2026-03-14 10:49:47'),
(262, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.48', '2026-03-14 11:05:42'),
(263, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.48', '2026-03-14 11:22:50'),
(264, 2, 'admin', 'send_email', 'Sent email to user ID: 50. Subject: Verification ', '165.56.186.48', '2026-03-14 11:32:17'),
(265, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.48', '2026-03-14 11:33:39'),
(266, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.31', '2026-03-14 13:04:10'),
(267, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.58', '2026-03-14 13:52:51'),
(268, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.216', '2026-03-14 14:50:01'),
(269, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.220', '2026-03-14 16:35:29'),
(270, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.220', '2026-03-14 17:01:39'),
(271, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.220', '2026-03-14 17:04:55'),
(272, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.220', '2026-03-14 17:07:56'),
(273, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.220', '2026-03-14 17:27:54'),
(274, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.220', '2026-03-14 17:42:21'),
(275, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.220', '2026-03-14 17:45:24'),
(276, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.220', '2026-03-14 17:48:02'),
(277, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.220', '2026-03-14 18:01:35'),
(278, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.220', '2026-03-14 18:02:37'),
(279, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.220', '2026-03-14 18:38:35'),
(280, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-14 20:24:02'),
(281, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-15 05:44:21'),
(282, 4, 'dealer', 'login', 'User logged in successfully', '102.208.220.203', '2026-03-15 05:44:48'),
(283, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.206', '2026-03-15 08:42:01'),
(284, 4, 'dealer', 'login', 'User logged in successfully', '102.208.220.206', '2026-03-15 08:42:54'),
(285, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.206', '2026-03-15 11:12:29'),
(286, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.206', '2026-03-15 12:17:34'),
(287, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.207', '2026-03-15 12:24:39'),
(288, 2, 'admin', 'login', 'User logged in successfully', '102.208.220.206', '2026-03-15 12:38:54'),
(289, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.242', '2026-03-15 15:16:10'),
(290, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.242', '2026-03-15 16:35:55'),
(291, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.191', '2026-03-15 18:07:27'),
(292, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.40', '2026-03-16 08:47:51'),
(293, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.185', '2026-03-16 12:03:15'),
(294, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.185', '2026-03-16 12:03:37'),
(295, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.185', '2026-03-16 12:06:38'),
(296, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.31', '2026-03-16 12:12:35'),
(297, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.126', '2026-03-16 17:18:46'),
(298, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.34', '2026-03-16 19:57:06'),
(299, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.132', '2026-03-16 20:50:20'),
(300, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.177', '2026-03-16 21:03:02'),
(301, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.177', '2026-03-16 21:03:24'),
(302, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.177', '2026-03-16 21:26:49'),
(303, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.201', '2026-03-16 21:45:43'),
(304, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.200', '2026-03-16 22:02:55'),
(305, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.182', '2026-03-17 05:05:58'),
(306, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.175', '2026-03-17 09:23:05'),
(307, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.110', '2026-03-17 11:12:42'),
(308, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.54', '2026-03-17 13:03:05'),
(309, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.213', '2026-03-17 13:22:04'),
(310, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.113', '2026-03-17 15:26:04'),
(311, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.113', '2026-03-17 15:26:21'),
(312, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.202', '2026-03-17 17:12:48'),
(313, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.57', '2026-03-17 20:19:10'),
(314, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.128', '2026-03-18 09:53:57'),
(315, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.65', '2026-03-18 13:14:04'),
(316, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.217', '2026-03-18 14:47:51'),
(317, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.217', '2026-03-18 15:33:16'),
(318, 2, 'admin', 'login', 'User logged in successfully', '45.215.249.159', '2026-03-18 20:17:24'),
(319, 4, 'dealer', 'login', 'User logged in successfully', '45.215.224.233', '2026-03-18 20:34:25'),
(320, 2, 'admin', 'login', 'User logged in successfully', '45.215.224.233', '2026-03-18 21:13:07'),
(321, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.124', '2026-03-19 05:42:03'),
(322, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.130', '2026-03-19 10:21:55'),
(323, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.224', '2026-03-19 15:12:36'),
(324, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.101', '2026-03-19 19:24:31'),
(325, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.41', '2026-03-20 05:45:02'),
(326, NULL, 'user', 'register', 'New user registered: pfnfjtvv@immenseignite.info (user)', '212.192.28.198', '2026-03-20 08:03:12'),
(327, NULL, 'user', 'register', 'New user registered: olxfymfl@immenseignite.info (user)', '45.43.163.244', '2026-03-20 08:11:53'),
(328, NULL, 'user', 'register', 'New user registered: fuzzyball3188@msn.com (user)', '194.116.236.215', '2026-03-20 08:12:46'),
(329, NULL, 'user', 'register', 'New user registered: mnelson55@fordham.edu (user)', '194.116.236.215', '2026-03-20 08:12:47'),
(330, NULL, 'user', 'register', 'New user registered: daria.schevchuk@gmail.com (user)', '45.141.148.148', '2026-03-20 08:26:32'),
(331, NULL, 'user', 'register', 'New user registered: ctewes@comcast.net (user)', '45.141.148.148', '2026-03-20 08:26:33'),
(332, NULL, 'user', 'register', 'New user registered: megan.hulme@live.co.uk (user)', '216.9.225.89', '2026-03-20 09:06:36'),
(333, NULL, 'user', 'register', 'New user registered: shaiyue@hotmail.com (user)', '31.40.204.182', '2026-03-20 09:24:53'),
(334, NULL, 'user', 'register', 'New user registered: katelynore@gmail.com (user)', '31.40.204.182', '2026-03-20 09:25:12'),
(335, NULL, 'user', 'register', 'New user registered: caline.is@gmail.com (user)', '78.108.218.142', '2026-03-20 09:40:48'),
(336, NULL, 'user', 'register', 'New user registered: alicewiswell@gmail.com (user)', '78.108.218.142', '2026-03-20 09:40:48'),
(337, NULL, 'user', 'register', 'New user registered: rickjmcmichael@gmail.com (user)', '45.43.163.244', '2026-03-20 09:57:13'),
(338, NULL, 'user', 'register', 'New user registered: cbsweet@gmail.com (user)', '45.43.163.244', '2026-03-20 09:57:40'),
(339, NULL, 'user', 'register', 'New user registered: levi.brower@outlook.com (user)', '216.9.225.163', '2026-03-20 10:13:31'),
(340, NULL, 'user', 'register', 'New user registered: locklearjm@aol.com (user)', '216.9.225.163', '2026-03-20 10:13:39'),
(341, NULL, 'user', 'register', 'New user registered: mollyruben@gmail.com (user)', '198.46.154.21', '2026-03-20 10:31:28'),
(342, NULL, 'user', 'register', 'New user registered: hmbsam@gmail.com (user)', '212.192.28.198', '2026-03-20 10:46:56'),
(343, NULL, 'user', 'register', 'New user registered: bfrink77@yahoo.com (user)', '192.210.198.197', '2026-03-20 11:02:18'),
(344, NULL, 'user', 'register', 'New user registered: awheat@polsinelli.com (user)', '192.210.198.197', '2026-03-20 11:02:22'),
(345, NULL, 'user', 'register', 'New user registered: falcon97@aol.com (user)', '107.173.160.167', '2026-03-20 11:29:18'),
(346, NULL, 'user', 'register', 'New user registered: crawkin@gmail.com (user)', '107.173.160.167', '2026-03-20 11:30:39'),
(347, NULL, 'user', 'register', 'New user registered: anetarons3653@outlook.com (user)', '198.12.69.93', '2026-03-20 12:19:54'),
(348, NULL, 'user', 'register', 'New user registered: michelleheck@hotmail.com (user)', '198.12.69.93', '2026-03-20 12:20:49'),
(349, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.25', '2026-03-20 12:20:58'),
(350, 2, 'admin', 'delete_user', 'Deleted user ID: 74', '165.58.129.25', '2026-03-20 12:21:41'),
(351, 2, 'admin', 'delete_user', 'Deleted user ID: 96', '165.58.129.25', '2026-03-20 12:21:47'),
(352, 2, 'admin', 'delete_user', 'Deleted user ID: 95', '165.58.129.25', '2026-03-20 12:21:51'),
(353, 2, 'admin', 'delete_user', 'Deleted user ID: 93', '165.58.129.25', '2026-03-20 12:22:06'),
(354, 2, 'admin', 'delete_user', 'Deleted user ID: 75', '165.58.129.25', '2026-03-20 12:22:31'),
(355, 2, 'admin', 'ban_user', 'Changed ban status for user ID: 82 to 1', '165.58.129.25', '2026-03-20 12:22:46'),
(356, 2, 'admin', 'ban_user', 'Changed ban status for user ID: 84 to 1', '165.58.129.25', '2026-03-20 12:22:47'),
(357, 2, 'admin', 'ban_user', 'Changed ban status for user ID: 76 to 1', '165.58.129.25', '2026-03-20 12:22:57'),
(358, 2, 'admin', 'ban_user', 'Changed ban status for user ID: 77 to 1', '165.58.129.25', '2026-03-20 12:23:22'),
(359, 2, 'admin', 'delete_user', 'Deleted user ID: 94', '165.58.129.25', '2026-03-20 12:23:28'),
(360, 2, 'admin', 'delete_user', 'Deleted user ID: 67', '165.58.129.25', '2026-03-20 12:23:40'),
(361, 2, 'admin', 'ban_user', 'Changed ban status for user ID: 78 to 1', '165.58.129.25', '2026-03-20 12:23:56'),
(362, 2, 'admin', 'delete_user', 'Deleted user ID: 92', '165.58.129.25', '2026-03-20 12:24:01'),
(363, 2, 'admin', 'delete_user', 'Deleted user ID: 91', '165.58.129.25', '2026-03-20 12:24:04'),
(364, 2, 'admin', 'delete_user', 'Deleted user ID: 90', '165.58.129.25', '2026-03-20 12:24:08'),
(365, 2, 'admin', 'delete_user', 'Deleted user ID: 89', '165.58.129.25', '2026-03-20 12:24:11'),
(366, 2, 'admin', 'delete_user', 'Deleted user ID: 88', '165.58.129.25', '2026-03-20 12:24:15'),
(367, 2, 'admin', 'delete_user', 'Deleted user ID: 87', '165.58.129.25', '2026-03-20 12:24:18'),
(368, 2, 'admin', 'delete_user', 'Deleted user ID: 76', '165.58.129.25', '2026-03-20 12:24:26'),
(369, 2, 'admin', 'delete_user', 'Deleted user ID: 78', '165.58.129.25', '2026-03-20 12:24:37'),
(370, 2, 'admin', 'delete_user', 'Deleted user ID: 86', '165.58.129.25', '2026-03-20 12:24:46'),
(371, 2, 'admin', 'delete_user', 'Deleted user ID: 85', '165.58.129.25', '2026-03-20 12:24:58'),
(372, 2, 'admin', 'delete_user', 'Deleted user ID: 79', '165.58.129.25', '2026-03-20 12:25:06'),
(373, 2, 'admin', 'ban_user', 'Changed ban status for user ID: 80 to 1', '165.58.129.25', '2026-03-20 12:25:30'),
(374, 2, 'admin', 'ban_user', 'Changed ban status for user ID: 83 to 1', '165.58.129.25', '2026-03-20 12:25:37'),
(375, 2, 'admin', 'ban_user', 'Changed ban status for user ID: 81 to 1', '165.58.129.25', '2026-03-20 12:25:53'),
(376, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.25', '2026-03-20 12:26:36'),
(377, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.25', '2026-03-20 12:26:58'),
(378, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.25', '2026-03-20 12:47:40'),
(379, NULL, 'user', 'register', 'New user registered: jdavis@atedsn.com (user)', '216.9.224.107', '2026-03-20 13:50:12'),
(380, NULL, 'user', 'register', 'New user registered: jmjfam@gmail.com (user)', '216.9.224.107', '2026-03-20 13:50:29'),
(381, NULL, 'user', 'register', 'New user registered: dcrider0776@msn.com (user)', '192.210.150.198', '2026-03-20 14:27:21'),
(382, NULL, 'user', 'register', 'New user registered: angehof@gmail.com (user)', '192.210.150.198', '2026-03-20 14:27:22'),
(383, NULL, 'user', 'register', 'New user registered: sjbalks@gmail.com (user)', '192.210.198.197', '2026-03-20 14:54:30'),
(384, NULL, 'user', 'register', 'New user registered: sales@audioinnovators.com (user)', '192.210.198.197', '2026-03-20 14:54:31'),
(385, NULL, 'user', 'register', 'New user registered: fcadelagarza@gmail.com (user)', '216.9.225.89', '2026-03-20 15:22:24'),
(386, NULL, 'user', 'register', 'New user registered: messywigs@rogers.com (user)', '216.9.225.89', '2026-03-20 15:22:25'),
(387, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.128', '2026-03-20 16:04:30'),
(388, NULL, 'user', 'register', 'New user registered: srottman@comcast.net (user)', '31.40.204.182', '2026-03-20 16:06:30'),
(389, NULL, 'user', 'register', 'New user registered: misscherrikitty@yahoo.com (user)', '31.40.204.182', '2026-03-20 16:06:36'),
(390, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.128', '2026-03-20 17:29:02'),
(391, 2, 'admin', 'delete_user', 'Deleted user ID: 97', '165.56.186.128', '2026-03-20 17:29:40'),
(392, 2, 'admin', 'delete_user', 'Deleted user ID: 106', '165.56.186.128', '2026-03-20 17:29:46'),
(393, 2, 'admin', 'delete_user', 'Deleted user ID: 105', '165.56.186.128', '2026-03-20 17:29:52'),
(394, 2, 'admin', 'delete_user', 'Deleted user ID: 104', '165.56.186.128', '2026-03-20 17:29:57'),
(395, 2, 'admin', 'delete_user', 'Deleted user ID: 103', '165.56.186.128', '2026-03-20 17:30:03'),
(396, 2, 'admin', 'delete_user', 'Deleted user ID: 102', '165.56.186.128', '2026-03-20 17:30:12'),
(397, 2, 'admin', 'delete_user', 'Deleted user ID: 98', '165.56.186.128', '2026-03-20 17:30:21'),
(398, 2, 'admin', 'delete_user', 'Deleted user ID: 101', '165.56.186.128', '2026-03-20 17:30:25'),
(399, 2, 'admin', 'delete_user', 'Deleted user ID: 100', '165.56.186.128', '2026-03-20 17:30:30'),
(400, 2, 'admin', 'delete_user', 'Deleted user ID: 99', '165.56.186.128', '2026-03-20 17:30:36'),
(401, NULL, 'user', 'register', 'New user registered: mfm@extreme-endeavors.com (user)', '31.40.204.182', '2026-03-20 17:39:40'),
(402, NULL, 'user', 'register', 'New user registered: hdpapa57@yahoo.com (user)', '31.40.204.182', '2026-03-20 17:39:46'),
(403, NULL, 'user', 'register', 'New user registered: skdk911@comcast.net (user)', '216.9.225.89', '2026-03-20 18:29:13'),
(404, NULL, 'user', 'register', 'New user registered: karenmacd@telus.net (user)', '216.9.225.89', '2026-03-20 18:29:13'),
(405, NULL, 'user', 'register', 'New user registered: christian.hang@gmail.com (user)', '198.46.154.22', '2026-03-20 19:17:11'),
(406, NULL, 'user', 'register', 'New user registered: collegeangela@gmail.com (user)', '198.46.154.22', '2026-03-20 19:17:41'),
(407, NULL, 'user', 'register', 'New user registered: nhatfield2302@gmail.com (user)', '216.9.225.89', '2026-03-20 20:20:34'),
(408, NULL, 'user', 'register', 'New user registered: arturo.manzanares8@yahoo.com (user)', '216.9.225.89', '2026-03-20 20:20:34'),
(409, NULL, 'user', 'register', 'New user registered: langworthy.john@gmail.com (user)', '78.108.218.142', '2026-03-20 21:16:17'),
(410, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.56', '2026-03-20 21:33:39'),
(411, 2, 'admin', 'delete_user', 'Deleted user ID: 108', '165.56.186.56', '2026-03-20 21:33:59'),
(412, 2, 'admin', 'delete_user', 'Deleted user ID: 83', '165.56.186.56', '2026-03-20 21:34:08'),
(413, 2, 'admin', 'delete_user', 'Deleted user ID: 107', '165.56.186.56', '2026-03-20 21:34:16'),
(414, 2, 'admin', 'delete_user', 'Deleted user ID: 115', '165.56.186.56', '2026-03-20 21:34:31'),
(415, 2, 'admin', 'delete_user', 'Deleted user ID: 113', '165.56.186.56', '2026-03-20 21:34:34'),
(416, 2, 'admin', 'delete_user', 'Deleted user ID: 114', '165.56.186.56', '2026-03-20 21:34:37'),
(417, 2, 'admin', 'delete_user', 'Deleted user ID: 112', '165.56.186.56', '2026-03-20 21:34:39'),
(418, NULL, 'user', 'register', 'New user registered: langworthy.john@gmail.com (user)', '216.9.225.163', '2026-03-20 21:51:25'),
(419, NULL, 'user', 'register', 'New user registered: nhatfield2302@gmail.com (user)', '216.9.225.163', '2026-03-20 21:51:34'),
(420, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.56', '2026-03-20 21:55:22'),
(421, 2, 'admin', 'delete_user', 'Deleted user ID: 117', '165.56.186.56', '2026-03-20 21:55:37'),
(422, 2, 'admin', 'delete_user', 'Deleted user ID: 116', '165.56.186.56', '2026-03-20 21:55:41'),
(423, 2, 'admin', 'delete_user', 'Deleted user ID: 111', '165.56.186.56', '2026-03-20 21:55:45'),
(424, 2, 'admin', 'delete_user', 'Deleted user ID: 109', '165.56.186.56', '2026-03-20 21:55:52'),
(425, 2, 'admin', 'delete_user', 'Deleted user ID: 110', '165.56.186.56', '2026-03-20 21:55:55'),
(426, 2, 'admin', 'delete_user', 'Deleted user ID: 111', '165.56.186.56', '2026-03-20 22:03:03'),
(427, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.56', '2026-03-20 22:03:42'),
(428, NULL, 'user', 'register', 'New user registered: chisalavudo@gmail.com (user)', '165.56.186.56', '2026-03-20 22:06:49'),
(429, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.56', '2026-03-20 22:10:58'),
(430, 2, 'admin', 'delete_user', 'Deleted user ID: 84', '165.56.186.56', '2026-03-20 22:11:10'),
(431, 2, 'admin', 'delete_user', 'Deleted user ID: 82', '165.56.186.56', '2026-03-20 22:11:14'),
(432, 2, 'admin', 'delete_user', 'Deleted user ID: 81', '165.56.186.56', '2026-03-20 22:11:19'),
(433, 2, 'admin', 'delete_user', 'Deleted user ID: 118', '165.56.186.56', '2026-03-20 22:11:24'),
(434, 2, 'admin', 'delete_user', 'Deleted user ID: 77', '165.56.186.56', '2026-03-20 22:11:28'),
(435, 2, 'admin', 'delete_user', 'Deleted user ID: 80', '165.56.186.56', '2026-03-20 22:11:32'),
(436, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.56', '2026-03-20 22:14:26'),
(437, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.56', '2026-03-20 22:28:08'),
(438, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.56', '2026-03-21 05:38:08'),
(439, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.56', '2026-03-21 06:04:39'),
(440, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.56', '2026-03-21 06:18:32'),
(441, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.56', '2026-03-21 07:24:48'),
(442, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.145', '2026-03-21 08:25:57'),
(443, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.131', '2026-03-21 11:06:38'),
(444, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.171', '2026-03-21 12:01:42'),
(445, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.247', '2026-03-21 14:35:17'),
(446, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.144', '2026-03-21 17:42:59'),
(447, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.144', '2026-03-21 17:51:07'),
(448, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.144', '2026-03-21 17:51:31'),
(449, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.132', '2026-03-21 20:19:54'),
(450, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.245', '2026-03-22 06:42:02'),
(451, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.216', '2026-03-22 14:24:26'),
(452, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.138', '2026-03-22 15:47:04'),
(453, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.33', '2026-03-22 16:50:05'),
(454, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.218', '2026-03-22 22:09:38'),
(455, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.190', '2026-03-23 07:49:46'),
(456, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.202', '2026-03-23 08:48:02'),
(457, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.30', '2026-03-23 09:57:51'),
(458, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.111', '2026-03-23 16:45:08'),
(459, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.111', '2026-03-23 16:51:56'),
(460, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.111', '2026-03-23 16:54:11'),
(461, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.111', '2026-03-23 19:03:24'),
(462, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.111', '2026-03-23 20:29:12'),
(463, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.111', '2026-03-23 21:04:16'),
(464, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.111', '2026-03-23 21:15:44'),
(465, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.203', '2026-03-24 08:14:32'),
(466, 2, 'admin', 'update_subscription', 'Updated subscription for user ID: 4. Status: inactive, Expiry: 2026-04-05', '165.57.81.203', '2026-03-24 08:14:54'),
(467, 2, 'admin', 'update_subscription', 'Updated subscription for user ID: 4. Status: active, Expiry: 2026-04-05', '165.57.81.203', '2026-03-24 08:31:14'),
(468, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.203', '2026-03-24 09:11:02'),
(469, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.203', '2026-03-24 10:20:41'),
(470, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.226', '2026-03-24 11:07:55'),
(471, 4, 'dealer', 'login', 'User logged in successfully', '165.56.66.226', '2026-03-24 11:08:32'),
(472, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.16', '2026-03-24 12:13:27'),
(473, 2, 'admin', 'send_email', 'Sent email to user ID: 121. Subject: Bsnana', '165.57.81.16', '2026-03-24 12:14:29'),
(474, 2, 'admin', 'delete_user', 'Deleted user ID: 121', '165.57.81.16', '2026-03-24 12:15:03'),
(475, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.16', '2026-03-24 12:15:40'),
(476, 2, 'admin', 'delete_user', 'Deleted user ID: 120', '165.57.81.16', '2026-03-24 12:15:50'),
(477, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.154', '2026-03-24 14:04:47'),
(478, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.243', '2026-03-24 15:20:24'),
(479, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.79', '2026-03-24 18:06:15'),
(480, 2, 'admin', 'delete_user', 'Deleted user ID: 122', '165.58.129.79', '2026-03-24 18:06:29'),
(481, NULL, 'user', 'register', 'New user registered: chisalaluckyk5@gmail.com (user)', '165.58.129.79', '2026-03-24 18:09:08'),
(482, NULL, 'user', 'login', 'User logged in successfully', '165.58.129.165', '2026-03-24 18:57:04'),
(483, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.53', '2026-03-25 14:46:21'),
(484, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.129', '2026-03-25 16:34:38'),
(485, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.16', '2026-03-25 16:50:04'),
(486, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.16', '2026-03-25 19:49:27');
INSERT INTO `activity_logs` (`id`, `user_id`, `user_role`, `action`, `description`, `ip_address`, `created_at`) VALUES
(487, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.133', '2026-03-26 05:56:15'),
(488, 2, 'admin', 'delete_user', 'Deleted user ID: 124', '165.56.186.133', '2026-03-26 05:56:40'),
(489, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.136', '2026-03-26 06:23:27'),
(490, NULL, 'dealer', 'register', 'New user registered: chisalaluckson27@gmail.com (dealer)', '165.57.81.107', '2026-03-26 06:30:40'),
(491, NULL, 'dealer', 'login', 'User logged in successfully', '165.57.81.107', '2026-03-26 06:32:28'),
(492, NULL, 'dealer', 'login', 'User logged in successfully', '165.58.129.242', '2026-03-26 07:40:50'),
(493, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.91', '2026-03-26 08:20:28'),
(494, 2, 'admin', 'verify_user', 'Updated verification for user ID: 125 to 1', '165.58.129.91', '2026-03-26 08:37:28'),
(495, NULL, 'dealer', 'register', 'New user registered: chisalaluckson27@gmail.com (dealer)', '165.58.129.185', '2026-03-26 08:49:15'),
(496, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.185', '2026-03-26 08:50:26'),
(497, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.185', '2026-03-26 09:36:35'),
(498, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.14', '2026-03-26 10:46:03'),
(499, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.125', '2026-03-26 21:14:10'),
(500, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.125', '2026-03-26 21:44:36'),
(501, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.59', '2026-03-26 22:43:45'),
(502, NULL, 'dealer', 'login', 'User logged in successfully', '165.56.66.59', '2026-03-26 22:48:16'),
(503, 4, 'dealer', 'login', 'User logged in successfully', '165.56.66.59', '2026-03-26 22:49:07'),
(504, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.220', '2026-03-26 23:48:20'),
(505, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.167', '2026-03-27 06:21:05'),
(506, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.106', '2026-03-27 08:23:26'),
(507, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.212', '2026-03-27 10:04:36'),
(508, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.212', '2026-03-27 10:04:56'),
(509, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.124', '2026-03-27 10:19:55'),
(510, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.128', '2026-03-27 12:48:44'),
(511, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.128', '2026-03-27 14:15:34'),
(512, NULL, 'user', 'login', 'User logged in successfully', '165.57.81.206', '2026-03-27 19:30:01'),
(513, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.206', '2026-03-27 19:30:19'),
(514, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.122', '2026-03-28 02:50:43'),
(515, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.231', '2026-03-28 09:36:51'),
(516, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.85', '2026-03-28 15:07:19'),
(517, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.38', '2026-03-28 16:14:29'),
(518, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.38', '2026-03-28 16:14:53'),
(519, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.227', '2026-03-28 20:56:17'),
(520, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.195', '2026-03-28 21:33:43'),
(521, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.26', '2026-03-29 06:39:16'),
(522, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.26', '2026-03-29 09:02:54'),
(523, 4, 'dealer', 'login', 'User logged in successfully', '165.56.66.26', '2026-03-29 09:04:01'),
(524, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.26', '2026-03-29 09:53:09'),
(525, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.26', '2026-03-29 10:38:49'),
(526, 2, 'admin', 'update_subscription', 'Updated subscription for user ID: 4. Status: inactive, Expiry: 2026-04-05', '165.56.66.26', '2026-03-29 10:39:15'),
(527, 2, 'admin', 'update_subscription', 'Updated subscription for user ID: 4. Status: active, Expiry: 2026-04-05', '165.56.66.26', '2026-03-29 10:40:53'),
(528, 2, 'admin', 'update_subscription', 'Updated subscription for user ID: 4. Status: active, Expiry: 2026-04-05', '165.56.66.26', '2026-03-29 10:54:01'),
(529, 2, 'admin', 'update_subscription', 'Updated subscription for user ID: 4. Status: inactive, Expiry: 2026-04-05', '165.56.66.26', '2026-03-29 10:54:27'),
(530, 2, 'admin', 'update_subscription', 'Updated subscription for user ID: 4. Status: active, Expiry: 2026-04-05', '165.56.66.26', '2026-03-29 10:54:36'),
(531, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.26', '2026-03-29 11:38:02'),
(532, 2, 'admin', 'update_subscription', 'Updated subscription for user ID: 4. Status: inactive, Expiry: 2026-04-05', '165.56.66.26', '2026-03-29 11:59:32'),
(533, 2, 'admin', 'update_subscription', 'Updated subscription for user ID: 4. Status: active, Expiry: 2026-04-05', '165.56.66.26', '2026-03-29 12:00:10'),
(534, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.26', '2026-03-29 12:51:57'),
(535, 2, 'admin', 'delete_user', 'Deleted user ID: 127', '165.56.66.26', '2026-03-29 12:53:21'),
(536, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.215', '2026-03-29 13:30:52'),
(537, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.71', '2026-03-29 13:53:28'),
(538, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.204', '2026-03-29 14:27:28'),
(539, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.106', '2026-03-29 14:42:33'),
(540, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.185', '2026-03-29 15:58:47'),
(541, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.124', '2026-03-29 16:50:11'),
(542, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.198', '2026-03-29 18:29:17'),
(543, 2, 'admin', 'delete_user', 'Deleted user ID: 128', '165.57.81.198', '2026-03-29 18:29:43'),
(544, 2, 'admin', 'delete_user', 'Deleted user ID: 129', '165.57.81.198', '2026-03-29 18:29:46'),
(545, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.50', '2026-03-30 04:20:08'),
(546, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.123', '2026-03-30 09:34:29'),
(547, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.30', '2026-03-30 10:56:07'),
(548, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.29', '2026-03-30 13:26:09'),
(549, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.29', '2026-03-30 14:02:00'),
(550, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.42', '2026-03-30 14:24:50'),
(551, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.22', '2026-03-30 15:03:03'),
(552, NULL, 'user', 'login', 'User logged in successfully', '165.58.129.22', '2026-03-30 15:05:08'),
(553, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.72', '2026-03-30 15:13:15'),
(554, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.72', '2026-03-30 15:47:41'),
(555, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.72', '2026-03-30 18:01:16'),
(556, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.72', '2026-03-30 18:46:24'),
(557, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.72', '2026-03-30 18:54:08'),
(558, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.185', '2026-03-30 19:39:27'),
(559, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.17', '2026-03-30 20:25:41'),
(560, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.227', '2026-03-31 04:35:36'),
(561, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.108', '2026-03-31 05:41:11'),
(562, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.108', '2026-03-31 06:56:09'),
(563, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.127', '2026-03-31 10:47:59'),
(564, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.127', '2026-03-31 11:01:59'),
(565, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.166', '2026-03-31 11:29:15'),
(566, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.128', '2026-03-31 11:38:11'),
(567, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.231', '2026-03-31 12:48:49'),
(568, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.164', '2026-03-31 13:41:22'),
(569, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.105', '2026-03-31 14:29:09'),
(570, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.208', '2026-03-31 15:05:34'),
(571, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.212', '2026-03-31 15:16:07'),
(572, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.212', '2026-03-31 15:22:34'),
(573, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.48', '2026-03-31 16:42:18'),
(574, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.207', '2026-03-31 17:45:33'),
(575, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.207', '2026-03-31 18:11:18'),
(576, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.207', '2026-03-31 18:13:02'),
(577, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.207', '2026-03-31 18:15:54'),
(578, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.206', '2026-03-31 18:44:00'),
(579, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.103', '2026-03-31 19:54:21'),
(580, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.103', '2026-03-31 19:54:25'),
(581, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.85', '2026-04-01 04:40:37'),
(582, 130, 'dealer', 'login', 'User logged in successfully', '45.215.237.156', '2026-04-01 05:55:16'),
(583, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.190', '2026-04-01 06:03:49'),
(584, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.190', '2026-04-01 06:43:50'),
(585, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.190', '2026-04-01 07:06:05'),
(586, 131, 'user', 'register', 'New user registered: janeaaliyah84@gmail.com (user)', '41.223.117.78', '2026-04-01 07:08:21'),
(587, NULL, 'user', 'login', 'User logged in successfully', '165.58.129.190', '2026-04-01 07:32:15'),
(588, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.190', '2026-04-01 07:35:58'),
(589, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.190', '2026-04-01 07:49:47'),
(590, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.190', '2026-04-01 07:50:54'),
(591, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.190', '2026-04-01 07:56:05'),
(592, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.190', '2026-04-01 08:02:07'),
(593, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.190', '2026-04-01 08:20:57'),
(594, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.190', '2026-04-01 08:27:00'),
(595, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.190', '2026-04-01 08:28:06'),
(596, NULL, 'user', 'login', 'User logged in successfully', '165.58.129.190', '2026-04-01 08:29:39'),
(597, NULL, 'user', 'login', 'User logged in successfully', '165.58.129.190', '2026-04-01 08:42:31'),
(598, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.190', '2026-04-01 09:31:54'),
(599, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.190', '2026-04-01 09:54:53'),
(600, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.190', '2026-04-01 10:13:11'),
(601, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.190', '2026-04-01 10:13:33'),
(602, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.190', '2026-04-01 10:26:36'),
(603, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.190', '2026-04-01 10:42:12'),
(604, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.190', '2026-04-01 10:53:38'),
(605, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.190', '2026-04-01 12:51:20'),
(606, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.190', '2026-04-01 13:07:11'),
(607, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.155', '2026-04-01 13:55:19'),
(608, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.18', '2026-04-01 14:33:26'),
(609, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.210', '2026-04-01 15:42:08'),
(610, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.100', '2026-04-01 19:34:16'),
(611, 2, 'admin', 'send_email', 'Sent email to user ID: 132. Subject: Property upload ', '45.215.236.107', '2026-04-01 19:37:02'),
(612, 2, 'admin', 'send_email', 'Sent email to user ID: 131. Subject: Welcome', '45.215.236.99', '2026-04-01 19:37:57'),
(613, 2, 'admin', 'send_email', 'Sent email to user ID: 123. Subject: Welcome', '45.215.236.100', '2026-04-01 19:38:22'),
(614, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.99', '2026-04-01 19:52:48'),
(615, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.99', '2026-04-01 20:45:34'),
(616, 4, 'dealer', 'login', 'User logged in successfully', '45.215.236.99', '2026-04-01 20:57:54'),
(617, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.99', '2026-04-01 20:59:29'),
(618, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.99', '2026-04-01 22:04:19'),
(619, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.51', '2026-04-02 07:35:39'),
(620, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.51', '2026-04-02 07:51:14'),
(621, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.62', '2026-04-02 10:56:32'),
(622, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.62', '2026-04-02 11:00:42'),
(623, 2, 'admin', 'delete_user', 'Deleted user ID: 123', '165.57.81.62', '2026-04-02 11:01:05'),
(624, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.62', '2026-04-02 11:08:04'),
(625, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.62', '2026-04-02 11:26:06'),
(626, 2, 'admin', 'delete_user', 'Deleted user ID: 133', '165.57.81.62', '2026-04-02 11:26:24'),
(627, NULL, 'dealer', 'register', 'New user registered: chisalaluckyk5@gmail.com (dealer)', '165.57.81.62', '2026-04-02 11:27:45'),
(628, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.124', '2026-04-02 15:10:07'),
(629, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.155', '2026-04-02 18:07:53'),
(630, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.51', '2026-04-02 19:23:06'),
(631, 2, 'admin', 'delete_user', 'Deleted user ID: 134', '165.58.129.51', '2026-04-02 19:23:21'),
(632, 135, 'user', 'register', 'New user registered: chisalaluckyk5@gmail.com (user)', '165.58.129.51', '2026-04-02 19:25:31'),
(633, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.16', '2026-04-02 20:58:09'),
(634, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.142', '2026-04-03 06:58:29'),
(635, NULL, 'dealer', 'register', 'New user registered: chisalaluckson27@gmail.com (dealer)', '165.58.129.84', '2026-04-03 07:00:32'),
(636, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.84', '2026-04-03 07:02:34'),
(637, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.73', '2026-04-03 08:22:28'),
(638, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.232', '2026-04-03 10:12:24'),
(639, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.43', '2026-04-03 17:03:35'),
(640, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.240', '2026-04-04 07:17:33'),
(641, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.224', '2026-04-04 08:00:31'),
(642, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.98', '2026-04-04 11:01:32'),
(643, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.192', '2026-04-04 16:34:10'),
(644, 4, 'dealer', 'login', 'User logged in successfully', '165.56.186.192', '2026-04-04 16:38:03'),
(645, 135, 'user', 'login', 'User logged in successfully', '165.56.186.192', '2026-04-04 16:38:54'),
(646, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.147', '2026-04-05 06:34:32'),
(647, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.147', '2026-04-05 06:36:10'),
(648, 2, 'admin', 'send_email', 'Sent email to user ID: 50. Subject: Payment ', '165.58.129.147', '2026-04-05 06:37:23'),
(649, 2, 'admin', 'update_subscription', 'Updated subscription for user ID: 4. Status: , Expiry: 2026-04-06', '165.58.129.147', '2026-04-05 06:38:10'),
(650, 2, 'admin', 'update_subscription', 'Updated subscription for user ID: 4. Status: active, Expiry: 2026-04-06', '165.58.129.147', '2026-04-05 06:38:30'),
(651, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.147', '2026-04-05 08:05:11'),
(652, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.147', '2026-04-05 08:19:57'),
(653, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.147', '2026-04-05 08:20:36'),
(654, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.226', '2026-04-05 10:37:34'),
(655, 4, 'dealer', 'login', 'User logged in successfully', '165.57.81.207', '2026-04-05 14:14:47'),
(656, 135, 'user', 'login', 'User logged in successfully', '165.57.81.207', '2026-04-05 14:15:48'),
(657, 4, 'dealer', 'login', 'User logged in successfully', '165.57.81.207', '2026-04-05 14:18:30'),
(658, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.186', '2026-04-05 17:44:45'),
(659, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.52', '2026-04-06 05:22:47'),
(660, 2, 'admin', 'update_subscription', 'Updated subscription for user ID: 4. Status: active, Expiry: 2026-04-30', '165.56.186.52', '2026-04-06 05:23:32'),
(661, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.94', '2026-04-06 10:30:26'),
(662, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.84', '2026-04-06 11:01:44'),
(663, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.84', '2026-04-06 11:04:10'),
(664, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.175', '2026-04-06 11:50:31'),
(665, 4, 'dealer', 'login', 'User logged in successfully', '45.215.237.188', '2026-04-06 12:26:59'),
(666, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.15', '2026-04-06 15:56:26'),
(667, 2, 'admin', 'login', 'User logged in successfully', '45.215.224.20', '2026-04-06 16:30:20'),
(668, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.197', '2026-04-06 16:48:43'),
(669, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.197', '2026-04-06 16:58:19'),
(670, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.197', '2026-04-06 17:55:07'),
(671, 2, 'admin', 'login', 'User logged in successfully', '45.215.255.191', '2026-04-06 18:09:57'),
(672, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.228', '2026-04-06 20:01:44'),
(673, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.228', '2026-04-06 21:12:47'),
(674, 2, 'admin', 'login', 'User logged in successfully', '45.215.237.183', '2026-04-07 05:08:23'),
(675, 2, 'admin', 'login', 'User logged in successfully', '45.215.237.183', '2026-04-07 05:37:39'),
(676, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.111', '2026-04-07 07:37:16'),
(677, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.157', '2026-04-07 08:32:01'),
(678, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.66', '2026-04-07 09:54:27'),
(679, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.139', '2026-04-07 12:24:06'),
(680, 2, 'admin', 'login', 'User logged in successfully', '45.215.237.150', '2026-04-07 12:37:48'),
(681, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.32', '2026-04-07 14:13:56'),
(682, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.66', '2026-04-07 15:50:40'),
(683, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.72', '2026-04-07 19:16:47'),
(684, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.166', '2026-04-07 20:30:46'),
(685, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.167', '2026-04-07 20:49:33'),
(686, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.167', '2026-04-07 21:02:11'),
(687, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.82', '2026-04-07 21:09:15'),
(688, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.42', '2026-04-07 22:04:50'),
(689, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.150', '2026-04-08 05:14:14'),
(690, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.244', '2026-04-08 08:31:52'),
(691, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.158', '2026-04-08 09:46:24'),
(692, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.158', '2026-04-08 10:26:50'),
(693, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.222', '2026-04-08 11:06:11'),
(694, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.156', '2026-04-08 11:49:22'),
(695, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.31', '2026-04-08 13:05:46'),
(696, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.240', '2026-04-08 13:34:02'),
(697, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.24', '2026-04-08 14:50:08'),
(698, 2, 'admin', 'delete_user', 'Deleted user ID: 136', '165.58.129.24', '2026-04-08 14:50:34'),
(699, NULL, 'dealer', 'register', 'New user registered: chisalaluckson27@gmail.com (dealer)', '165.56.66.39', '2026-04-08 14:56:13'),
(700, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.128', '2026-04-08 15:56:40'),
(701, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.115', '2026-04-08 16:35:17'),
(702, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.115', '2026-04-08 16:51:57'),
(703, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.24', '2026-04-08 19:22:39'),
(704, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.135', '2026-04-09 06:20:30'),
(705, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.71', '2026-04-09 10:03:29'),
(706, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.101', '2026-04-09 10:38:02'),
(707, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.25', '2026-04-09 12:19:48'),
(708, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.30', '2026-04-09 14:04:17'),
(709, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.11', '2026-04-09 15:04:43'),
(710, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.173', '2026-04-09 17:13:17'),
(711, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.227', '2026-04-09 17:31:15'),
(712, NULL, 'user', 'register', 'New user registered: nkhataf59@gmail.com (user)', '102.130.127.135', '2026-04-09 17:43:23'),
(713, 47, 'user', 'login', 'User logged in successfully', '102.130.127.135', '2026-04-09 17:52:17'),
(714, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.178', '2026-04-09 18:00:35'),
(715, 139, 'user', 'register', 'New user registered: samanthakungwa@gmail.com (user)', '216.234.213.51', '2026-04-10 06:02:12'),
(716, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.49', '2026-04-10 06:03:49'),
(717, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.152', '2026-04-10 10:49:08'),
(718, 2, 'admin', 'delete_user', 'Deleted user ID: 138', '165.56.66.152', '2026-04-10 10:50:06'),
(719, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.170', '2026-04-10 13:53:54'),
(720, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.49', '2026-04-10 15:08:00'),
(721, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.249', '2026-04-10 20:19:15'),
(722, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.105', '2026-04-11 04:14:53'),
(723, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.13', '2026-04-11 10:01:33'),
(724, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.131', '2026-04-11 14:19:13'),
(725, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.100', '2026-04-11 20:25:51'),
(726, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.17', '2026-04-11 22:32:26'),
(727, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.71', '2026-04-12 05:11:55'),
(728, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.134', '2026-04-12 11:45:29'),
(729, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.10', '2026-04-12 13:52:59'),
(730, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.49', '2026-04-12 15:48:47'),
(731, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.168', '2026-04-13 05:27:19'),
(732, NULL, 'dealer', 'register', 'New user registered: michozulu18@gmail.com (dealer)', '41.223.117.45', '2026-04-13 10:14:40'),
(733, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.200', '2026-04-13 12:46:45'),
(734, 2, 'admin', 'send_email', 'Sent email to user ID: 140. Subject: Verification ', '165.56.186.200', '2026-04-13 12:48:02'),
(735, 142, 'user', 'register', 'New user registered: simbayaedgarbl@gmail.com (user)', '45.215.251.239', '2026-04-13 12:55:43'),
(736, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.200', '2026-04-13 12:56:11'),
(737, 2, 'admin', 'send_email', 'Sent email to user ID: 142. Subject: Verification ', '165.57.81.202', '2026-04-13 13:06:11'),
(738, 2, 'admin', 'send_email', 'Sent email to user ID: 135. Subject: Hh', '165.57.81.202', '2026-04-13 13:06:24'),
(739, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.180', '2026-04-13 13:11:53'),
(740, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.180', '2026-04-13 13:16:10'),
(741, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.180', '2026-04-13 13:27:10'),
(742, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.180', '2026-04-13 13:37:42'),
(743, 143, 'user', 'register', 'New user registered: mekelanie1@gmail.com (user)', '41.223.117.42', '2026-04-13 13:47:23'),
(744, 142, 'user', 'login', 'User logged in successfully', '45.215.251.20', '2026-04-13 13:52:35'),
(745, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.80', '2026-04-13 14:18:11'),
(746, 2, 'admin', 'send_email', 'Sent email to user ID: 143. Subject: Verification ', '165.56.66.80', '2026-04-13 14:19:02'),
(747, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.80', '2026-04-13 14:22:45'),
(748, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.80', '2026-04-13 14:27:52'),
(749, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.244', '2026-04-13 14:36:09'),
(750, 144, 'user', 'register', 'New user registered: obertyhillen@gmail.com (user)', '45.215.255.1', '2026-04-13 14:46:48'),
(751, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.114', '2026-04-13 14:57:49'),
(752, 2, 'admin', 'send_email', 'Sent email to user ID: 144. Subject: Verification ', '165.56.186.32', '2026-04-13 15:01:45'),
(753, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.32', '2026-04-13 15:23:10'),
(754, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.32', '2026-04-13 15:23:41'),
(755, 2, 'admin', 'delete_user', 'Deleted user ID: 66', '165.56.186.32', '2026-04-13 15:23:57'),
(756, 2, 'admin', 'delete_user', 'Deleted user ID: 63', '165.56.186.32', '2026-04-13 15:24:08'),
(757, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.32', '2026-04-13 15:41:07'),
(758, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.32', '2026-04-13 16:16:39'),
(759, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.32', '2026-04-13 16:20:26'),
(760, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.28', '2026-04-13 16:40:32'),
(761, 2, 'admin', 'delete_user', 'Deleted user ID: 137', '165.57.81.28', '2026-04-13 16:43:20'),
(762, 145, 'dealer', 'register', 'New user registered: chisalaluckson27@gmail.com (dealer)', '165.57.81.28', '2026-04-13 16:47:25'),
(763, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.28', '2026-04-13 16:48:56'),
(764, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.35', '2026-04-13 18:10:56'),
(765, 145, 'dealer', 'login', 'User logged in successfully', '165.58.129.252', '2026-04-13 18:52:10'),
(766, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.252', '2026-04-13 18:56:06'),
(767, 145, 'dealer', 'login', 'User logged in successfully', '165.58.129.252', '2026-04-13 18:58:18'),
(768, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.8', '2026-04-13 19:02:37'),
(769, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.80', '2026-04-13 20:57:33'),
(770, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.152', '2026-04-14 05:12:41'),
(771, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.226', '2026-04-14 06:11:51'),
(772, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.226', '2026-04-14 06:12:23'),
(773, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.62', '2026-04-14 07:41:53'),
(774, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.94', '2026-04-14 08:41:30'),
(775, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.218', '2026-04-14 09:00:49'),
(776, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.214', '2026-04-14 09:42:09'),
(777, 143, 'user', 'login', 'User logged in successfully', '41.223.118.43', '2026-04-14 10:10:20'),
(778, 146, 'user', 'register', 'New user registered: inongekashawa2019@gmail.com (user)', '74.244.129.162', '2026-04-14 10:18:18'),
(779, 147, 'user', 'register', 'New user registered: wilfredkhunga84@gmail.com (user)', '102.212.181.13', '2026-04-14 10:20:46'),
(780, 148, 'user', 'register', 'New user registered: phirialison15@gmail.com (user)', '45.215.251.3', '2026-04-14 10:22:59'),
(781, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.214', '2026-04-14 10:26:15'),
(782, 147, 'user', 'login', 'User logged in successfully', '102.212.181.13', '2026-04-14 10:26:20'),
(783, 149, 'dealer', 'register', 'New user registered: patriciankolem@gmail.com (dealer)', '45.215.237.215', '2026-04-14 10:29:33'),
(784, 2, 'admin', 'send_email', 'Sent email to user ID: 149. Subject: Verification ', '165.58.129.214', '2026-04-14 10:33:54'),
(785, 150, 'user', 'register', 'New user registered: yumbesimwinga@gmail.com (user)', '45.215.237.29', '2026-04-14 10:35:15'),
(786, 151, 'user', 'register', 'New user registered: eniakapepa1724@gmail.com (user)', '41.216.86.33', '2026-04-14 10:35:36'),
(787, 152, 'user', 'register', 'New user registered: gkalonje09@gmail.com (user)', '45.215.236.74', '2026-04-14 10:36:25'),
(788, 153, 'user', 'register', 'New user registered: kennymunene220@gmail.com (user)', '41.223.116.246', '2026-04-14 10:38:54'),
(789, 154, 'user', 'register', 'New user registered: mulamfupauline@gmail.com (user)', '102.208.221.209', '2026-04-14 10:51:33'),
(790, 155, 'user', 'register', 'New user registered: salundalukas@gmail.com (user)', '102.212.183.90', '2026-04-14 11:12:55'),
(791, 155, 'user', 'login', 'User logged in successfully', '102.212.183.90', '2026-04-14 11:14:41'),
(792, 156, 'user', 'register', 'New user registered: dorisjdala20@gmail.com (user)', '41.223.119.35', '2026-04-14 11:17:51'),
(793, 157, 'user', 'register', 'New user registered: isaacblackson95@gmail.com (user)', '45.215.237.139', '2026-04-14 11:23:54'),
(794, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.239', '2026-04-14 11:27:25'),
(795, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.97', '2026-04-14 11:54:37'),
(796, 158, 'user', 'register', 'New user registered: christopherkalale6@gmail.com (user)', '45.215.224.120', '2026-04-14 11:58:15'),
(797, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 12:01:03'),
(798, 2, 'admin', 'send_email', 'Sent email to user ID: 156. Subject: verification', '165.58.129.196', '2026-04-14 12:03:34'),
(799, 159, 'user', 'register', 'New user registered: mcmambo09@gmail.com (user)', '45.215.236.218', '2026-04-14 12:06:15'),
(800, 160, 'user', 'register', 'New user registered: Choolwe619@gmail.com (user)', '165.56.12.194', '2026-04-14 12:10:19'),
(801, 161, 'user', 'register', 'New user registered: chipakopamofwe@gmail.com (user)', '45.215.224.60', '2026-04-14 12:22:22'),
(802, 162, 'user', 'register', 'New user registered: susanlungu181@gmail.com (user)', '45.215.236.249', '2026-04-14 12:26:32'),
(803, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 12:27:54'),
(804, 163, 'user', 'register', 'New user registered: mwanzawanzi@gmail.com (user)', '45.214.226.129', '2026-04-14 12:36:50'),
(805, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 12:40:18'),
(806, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 12:44:04'),
(807, 164, 'user', 'register', 'New user registered: musondamwila8@outlook.com (user)', '41.175.172.144', '2026-04-14 12:47:34'),
(808, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 12:55:32'),
(809, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 13:00:35'),
(810, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 13:05:56'),
(811, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 13:12:41'),
(812, 165, 'user', 'register', 'New user registered: emmanuellupyana@gmail.com (user)', '45.215.251.47', '2026-04-14 13:26:45'),
(813, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 13:29:15'),
(814, 166, 'user', 'register', 'New user registered: derrick.kangwa@gmail.com (user)', '45.215.254.200', '2026-04-14 13:45:14'),
(815, 167, 'user', 'register', 'New user registered: chabalamumba@gmail.com (user)', '216.234.213.250', '2026-04-14 13:45:40'),
(816, 168, 'user', 'register', 'New user registered: tembobertha16@gmail.com (user)', '45.215.255.215', '2026-04-14 13:45:48'),
(817, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 13:46:35'),
(818, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 13:48:54'),
(819, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 13:51:19'),
(820, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 13:55:08'),
(821, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 14:00:35'),
(822, 169, 'user', 'register', 'New user registered: terrencemwale141@gmail.com (user)', '45.215.251.1', '2026-04-14 14:04:30'),
(823, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 14:10:10'),
(824, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 14:11:00'),
(825, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 14:17:37'),
(826, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 14:24:01'),
(827, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 14:29:04'),
(828, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 14:31:23'),
(829, 170, 'user', 'register', 'New user registered: www.kaizerisaacphirijr@gmail.com (user)', '102.220.158.252', '2026-04-14 14:34:39'),
(830, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 14:42:19'),
(831, 171, 'user', 'register', 'New user registered: marysilishebo04@gmail.com (user)', '45.212.243.249', '2026-04-14 14:42:46'),
(832, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 14:43:21'),
(833, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 14:47:49'),
(834, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 14:51:25'),
(835, 2, 'admin', 'send_email', 'Sent email to user ID: 171. Subject: Verification ', '165.58.129.196', '2026-04-14 14:51:53'),
(836, 172, 'user', 'register', 'New user registered: harrynyirongo7@gmail.com (user)', '216.234.213.121', '2026-04-14 15:00:53'),
(837, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.196', '2026-04-14 15:01:09'),
(838, 173, 'user', 'register', 'New user registered: nalwambaalice1@gmail.com (user)', '41.223.116.244', '2026-04-14 15:26:08'),
(839, 174, 'user', 'register', 'New user registered: elizabethmunthali5@gmail.com (user)', '45.213.248.138', '2026-04-14 15:49:08'),
(840, 174, 'user', 'login', 'User logged in successfully', '45.213.248.138', '2026-04-14 15:49:59'),
(841, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.43', '2026-04-14 15:57:11'),
(842, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.43', '2026-04-14 16:04:16'),
(843, 175, 'user', 'register', 'New user registered: wendymutayomba3@gmail.com (user)', '102.208.221.209', '2026-04-14 16:11:44'),
(844, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.195', '2026-04-14 16:25:34'),
(845, 176, 'user', 'register', 'New user registered: stanmakondo56@gmail.com (user)', '45.215.236.41', '2026-04-14 16:41:49'),
(846, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.13', '2026-04-14 17:00:16'),
(847, 177, 'user', 'register', 'New user registered: dakar7190@gmail.com (user)', '45.215.236.82', '2026-04-14 17:16:16'),
(848, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.2', '2026-04-14 17:26:31'),
(849, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.1', '2026-04-14 17:58:28'),
(850, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.217', '2026-04-14 18:09:52'),
(851, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.217', '2026-04-14 18:19:57'),
(852, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.191', '2026-04-14 18:43:48'),
(853, 2, 'admin', 'delete_user', 'Deleted user ID: 119', '165.58.129.144', '2026-04-14 19:16:44'),
(854, NULL, 'user', 'register', 'New user registered: chisalavudo@gmail.com (user)', '165.58.129.144', '2026-04-14 19:18:32'),
(855, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.133', '2026-04-14 19:42:50'),
(856, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.133', '2026-04-14 19:43:09'),
(857, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.14', '2026-04-14 20:28:28'),
(858, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.6', '2026-04-14 21:36:24'),
(859, 180, 'user', 'register', 'New user registered: mooyerlumamba@gmail.com (user)', '41.223.118.41', '2026-04-15 04:04:09'),
(860, 180, 'user', 'login', 'User logged in successfully', '41.223.117.39', '2026-04-15 04:06:24'),
(861, 181, 'user', 'register', 'New user registered: samuelhamafuwa@gmail.com (user)', '41.216.87.2', '2026-04-15 05:50:21'),
(862, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.135', '2026-04-15 05:55:08'),
(863, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.135', '2026-04-15 06:36:27'),
(864, 182, 'user', 'register', 'New user registered: oscarjaila44@gmail.com (user)', '45.215.224.6', '2026-04-15 06:50:38'),
(865, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.135', '2026-04-15 06:52:42'),
(866, 182, 'user', 'login', 'User logged in successfully', '45.215.224.6', '2026-04-15 06:57:32'),
(867, 2, 'admin', 'login', 'User logged in successfully', '45.215.237.108', '2026-04-15 07:06:59'),
(868, 183, 'user', 'register', 'New user registered: nmunganguta@gmail.com (user)', '45.215.236.124', '2026-04-15 07:13:21'),
(869, 183, 'user', 'login', 'User logged in successfully', '45.215.236.124', '2026-04-15 07:15:27'),
(870, 2, 'admin', 'login', 'User logged in successfully', '197.213.70.111', '2026-04-15 07:34:12'),
(871, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.5', '2026-04-15 08:03:28'),
(872, 184, 'user', 'register', 'New user registered: chalimbanakomana@gmail.com (user)', '45.215.251.6', '2026-04-15 08:08:33'),
(873, 185, 'user', 'register', 'New user registered: Edsonchilala7@gmail.com (user)', '102.146.39.143', '2026-04-15 08:21:31'),
(874, 2, 'admin', 'login', 'User logged in successfully', '102.151.81.75', '2026-04-15 08:21:49'),
(875, 185, 'user', 'login', 'User logged in successfully', '102.146.39.143', '2026-04-15 08:23:56'),
(876, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.87', '2026-04-15 08:33:40'),
(877, 2, 'admin', 'login', 'User logged in successfully', '102.151.81.75', '2026-04-15 08:50:20'),
(878, 2, 'admin', 'login', 'User logged in successfully', '102.151.81.75', '2026-04-15 09:09:47'),
(879, 186, 'user', 'register', 'New user registered: mw11ngamul@gmail.com (user)', '102.68.138.183', '2026-04-15 09:38:36'),
(880, 187, 'user', 'register', 'New user registered: alicekw04@gmail.com (user)', '45.215.236.167', '2026-04-15 09:40:07'),
(881, 187, 'user', 'login', 'User logged in successfully', '45.215.236.167', '2026-04-15 09:42:16'),
(882, 2, 'admin', 'login', 'User logged in successfully', '45.215.237.144', '2026-04-15 09:53:52'),
(883, 188, 'user', 'register', 'New user registered: preciousmubanga997@gmail.com (user)', '45.215.237.198', '2026-04-15 10:05:32'),
(884, 2, 'admin', 'login', 'User logged in successfully', '45.215.237.144', '2026-04-15 10:06:09'),
(885, 2, 'admin', 'login', 'User logged in successfully', '45.215.237.144', '2026-04-15 10:09:28'),
(886, 189, 'dealer', 'register', 'New user registered: kmalichi@yahoo.com (dealer)', '165.56.64.34', '2026-04-15 10:19:37'),
(887, 2, 'admin', 'send_email', 'Sent email to user ID: 189. Subject: Verification ', '45.215.237.0', '2026-04-15 10:26:15'),
(888, 2, 'admin', 'send_email', 'Sent email to user ID: 189. Subject: Verification ', '45.215.237.0', '2026-04-15 10:26:18'),
(889, 190, 'dealer', 'register', 'New user registered: nyakunzutanaka@gmail.com (dealer)', '45.215.237.154', '2026-04-15 10:44:23'),
(890, 2, 'admin', 'login', 'User logged in successfully', '45.215.237.0', '2026-04-15 10:45:37'),
(891, 191, 'dealer', 'register', 'New user registered: davidchibwela@gmail.com (dealer)', '102.208.221.209', '2026-04-15 10:47:23'),
(892, 143, 'user', 'login', 'User logged in successfully', '41.216.86.35', '2026-04-15 11:01:07'),
(893, 192, 'user', 'register', 'New user registered: thumbikozoba21@gmail.com (user)', '165.56.66.9', '2026-04-15 11:11:41'),
(894, 193, 'user', 'register', 'New user registered: justnmums@gmail.com (user)', '45.215.237.240', '2026-04-15 11:13:39'),
(895, 194, 'user', 'register', 'New user registered: glenmoremulenga740@gmail.com (user)', '45.213.21.233', '2026-04-15 11:22:03'),
(896, 2, 'admin', 'send_email', 'Sent email to user ID: 189. Subject: Verification ', '45.215.237.0', '2026-04-15 11:26:11'),
(897, 2, 'admin', 'send_email', 'Sent email to user ID: 193. Subject: Verification ', '45.215.237.0', '2026-04-15 11:29:00'),
(898, 2, 'admin', 'send_email', 'Sent email to user ID: 191. Subject: Verification ', '45.215.237.0', '2026-04-15 11:29:34'),
(899, 2, 'admin', 'login', 'User logged in successfully', '45.215.237.0', '2026-04-15 11:31:23'),
(900, 195, 'user', 'register', 'New user registered: jkasambala37@gmail.com (user)', '45.215.237.167', '2026-04-15 11:34:26'),
(901, 2, 'admin', 'login', 'User logged in successfully', '45.215.237.0', '2026-04-15 11:34:46'),
(902, 196, 'dealer', 'register', 'New user registered: msimwemba@gmail.com (dealer)', '45.215.236.237', '2026-04-15 11:41:42'),
(903, 196, 'dealer', 'login', 'User logged in successfully', '45.215.236.237', '2026-04-15 11:42:49'),
(904, 197, 'user', 'register', 'New user registered: givatonemumba1994@gmail.com (user)', '45.215.236.47', '2026-04-15 11:50:31'),
(905, 198, 'dealer', 'register', 'New user registered: linzychandanora@gmail.com (dealer)', '102.212.183.4', '2026-04-15 11:53:26'),
(906, 2, 'admin', 'login', 'User logged in successfully', '45.215.237.0', '2026-04-15 12:01:19'),
(907, 2, 'admin', 'send_email', 'Sent email to user ID: 198. Subject: verification', '45.215.237.0', '2026-04-15 12:03:56'),
(908, 2, 'admin', 'delete_user', 'Deleted user ID: 140', '45.215.237.0', '2026-04-15 12:07:24'),
(909, 2, 'admin', 'delete_user', 'Deleted user ID: 140', '45.215.237.0', '2026-04-15 12:07:45'),
(910, 199, 'user', 'register', 'New user registered: mathewschibuye94@gmail.com (user)', '102.212.181.80', '2026-04-15 12:25:16'),
(911, 200, 'user', 'register', 'New user registered: edinasfa@gmail.com (user)', '45.215.251.51', '2026-04-15 12:59:49'),
(912, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.239', '2026-04-15 13:18:33'),
(913, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.239', '2026-04-15 13:19:13'),
(914, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.239', '2026-04-15 13:23:56'),
(915, 2, 'admin', 'login', 'User logged in successfully', '45.215.237.0', '2026-04-15 13:52:41'),
(916, 2, 'admin', 'login', 'User logged in successfully', '45.215.237.0', '2026-04-15 14:07:11'),
(917, 201, 'user', 'register', 'New user registered: moses01ngulube4@gmail.com (user)', '45.215.236.53', '2026-04-15 14:49:25'),
(918, 201, 'user', 'login', 'User logged in successfully', '45.215.236.53', '2026-04-15 14:50:57'),
(919, 2, 'admin', 'login', 'User logged in successfully', '197.213.126.3', '2026-04-15 15:09:17'),
(920, 189, 'dealer', 'login', 'User logged in successfully', '165.56.64.34', '2026-04-15 15:15:51'),
(921, 2, 'admin', 'login', 'User logged in successfully', '197.213.126.3', '2026-04-15 15:19:03'),
(922, 2, 'admin', 'login', 'User logged in successfully', '197.213.126.3', '2026-04-15 15:26:40'),
(923, 2, 'admin', 'login', 'User logged in successfully', '197.213.126.3', '2026-04-15 15:31:25'),
(924, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.175', '2026-04-15 15:59:45'),
(925, 202, 'user', 'register', 'New user registered: vusmuzimbewe@gmail.com (user)', '45.215.236.191', '2026-04-15 15:59:46'),
(926, 202, 'user', 'login', 'User logged in successfully', '45.215.236.191', '2026-04-15 16:01:40'),
(927, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.175', '2026-04-15 16:31:50'),
(928, 203, 'user', 'register', 'New user registered: www.josephmbale19@gmail.com (user)', '102.151.231.213', '2026-04-15 16:33:00'),
(929, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.125', '2026-04-15 17:10:54'),
(930, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.125', '2026-04-15 17:17:21'),
(931, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.126', '2026-04-15 17:35:00'),
(932, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.116', '2026-04-15 17:37:56'),
(933, 204, 'dealer', 'register', 'New user registered: lameck.kasanga@gmail.com (dealer)', '45.215.236.163', '2026-04-15 17:52:09'),
(934, 2, 'admin', 'send_email', 'Sent email to user ID: 196. Subject: verification', '45.215.236.112', '2026-04-15 18:01:22'),
(935, 2, 'admin', 'send_email', 'Sent email to user ID: 204. Subject: verification', '45.215.236.96', '2026-04-15 18:01:56'),
(936, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.197', '2026-04-15 19:00:42'),
(937, 205, 'user', 'register', 'New user registered: whyclefmwape1@gmail.com (user)', '45.215.237.208', '2026-04-15 19:56:22'),
(938, 206, 'user', 'register', 'New user registered: wyclefmwape@gmail.com (user)', '45.215.237.203', '2026-04-15 20:17:28'),
(939, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.122', '2026-04-15 20:52:36'),
(940, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.122', '2026-04-15 21:06:25'),
(941, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.239', '2026-04-15 21:27:07'),
(942, 207, 'user', 'register', 'New user registered: Samanthakabungo3@gmail.com (user)', '45.215.254.145', '2026-04-16 00:25:17'),
(943, 207, 'user', 'login', 'User logged in successfully', '45.215.254.145', '2026-04-16 00:27:06'),
(944, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.43', '2026-04-16 03:44:33'),
(945, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.43', '2026-04-16 05:12:51'),
(946, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.75', '2026-04-16 06:30:37'),
(947, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.243', '2026-04-16 07:43:40'),
(948, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.243', '2026-04-16 08:45:17'),
(949, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.157', '2026-04-16 09:25:38'),
(950, 208, 'user', 'register', 'New user registered: sampalaluwisa@gmail.com (user)', '45.215.224.240', '2026-04-16 09:59:34'),
(951, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.220', '2026-04-16 10:47:32'),
(952, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.172', '2026-04-16 10:58:44'),
(953, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.172', '2026-04-16 11:13:52'),
(954, 2, 'admin', 'send_email', 'Sent email to user ID: 196. Subject: Verification ', '165.58.129.172', '2026-04-16 11:17:46'),
(955, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.172', '2026-04-16 11:27:45'),
(956, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.172', '2026-04-16 11:34:59'),
(957, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.172', '2026-04-16 11:41:16'),
(958, 209, 'user', 'register', 'New user registered: chibwemichael102@gmail.com (user)', '102.146.255.31', '2026-04-16 11:42:02'),
(959, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.172', '2026-04-16 12:31:59'),
(960, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.92', '2026-04-16 12:49:15'),
(961, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.179', '2026-04-16 13:04:17'),
(962, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.210', '2026-04-16 13:18:56');
INSERT INTO `activity_logs` (`id`, `user_id`, `user_role`, `action`, `description`, `ip_address`, `created_at`) VALUES
(963, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.210', '2026-04-16 13:25:17'),
(964, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.210', '2026-04-16 13:36:23'),
(965, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.210', '2026-04-16 13:44:41'),
(966, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.210', '2026-04-16 13:55:02'),
(967, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.210', '2026-04-16 14:23:01'),
(968, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.210', '2026-04-16 14:48:15'),
(969, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.210', '2026-04-16 15:01:28'),
(970, 210, 'user', 'register', 'New user registered: kalengalilian689@gmail.com (user)', '216.234.213.57', '2026-04-16 15:26:48'),
(971, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.54', '2026-04-16 17:32:55'),
(972, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.219', '2026-04-16 20:36:16'),
(973, 211, 'user', 'register', 'New user registered: christophermphiri52@gmail.com (user)', '45.215.237.139', '2026-04-16 21:24:51'),
(974, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.160', '2026-04-16 21:36:07'),
(975, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.125', '2026-04-16 21:48:10'),
(976, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.226', '2026-04-16 22:38:12'),
(977, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.220', '2026-04-17 06:00:21'),
(978, 212, 'user', 'register', 'New user registered: Chimwenyejudith@gmail.com (user)', '102.212.183.180', '2026-04-17 07:06:54'),
(979, 212, 'user', 'login', 'User logged in successfully', '102.212.183.180', '2026-04-17 07:09:28'),
(980, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.193', '2026-04-17 07:14:58'),
(981, 2, 'admin', 'send_email', 'Sent email to user ID: 196. Subject: Uploading ', '165.58.129.193', '2026-04-17 07:16:34'),
(982, 2, 'admin', 'send_email', 'Sent email to user ID: 4. Subject: Uploading ', '165.58.129.193', '2026-04-17 07:16:54'),
(983, 2, 'admin', 'send_email', 'Sent email to user ID: 4. Subject: Uploading ', '165.58.129.193', '2026-04-17 07:22:01'),
(984, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.193', '2026-04-17 07:30:24'),
(985, 213, 'user', 'register', 'New user registered: jonathanbanda39@gmail.com (user)', '41.173.42.2', '2026-04-17 07:38:11'),
(986, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.106', '2026-04-17 07:42:37'),
(987, 2, 'admin', 'resend_verification_email', 'Resent verification email to dealer ID: 198', '165.58.129.106', '2026-04-17 07:45:46'),
(988, 214, 'user', 'register', 'New user registered: mwilakalenga77@gmail.com (user)', '216.234.213.57', '2026-04-17 07:49:42'),
(989, 215, 'user', 'register', 'New user registered: chilondewise23@gmail.com (user)', '45.215.251.167', '2026-04-17 08:00:27'),
(990, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.142', '2026-04-17 08:04:33'),
(991, 216, 'user', 'register', 'New user registered: chizasimwinga@gmail.com (user)', '45.215.207.224', '2026-04-17 08:06:26'),
(992, 2, 'admin', 'resend_verification_email', 'Resent verification email to user ID: 214', '165.57.81.187', '2026-04-17 08:12:15'),
(993, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.60', '2026-04-17 08:20:08'),
(994, 2, 'admin', 'resend_verification_email', 'Resent verification email to user ID: 215', '165.58.129.60', '2026-04-17 08:20:34'),
(995, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.105', '2026-04-17 09:48:01'),
(996, 217, 'user', 'register', 'New user registered: mwapeyumba60@gmail.com (user)', '45.215.236.150', '2026-04-17 09:54:20'),
(997, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.105', '2026-04-17 10:11:11'),
(998, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.149', '2026-04-17 10:31:23'),
(999, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.80', '2026-04-17 10:59:15'),
(1000, 219, 'user', 'register', 'New user registered: zuluamos14@gmail.com (user)', '102.151.226.54', '2026-04-17 11:48:00'),
(1001, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.68', '2026-04-17 12:23:37'),
(1002, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.68', '2026-04-17 12:33:29'),
(1003, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.68', '2026-04-17 12:38:21'),
(1004, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.68', '2026-04-17 12:57:24'),
(1005, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.68', '2026-04-17 13:42:21'),
(1006, 220, 'user', 'register', 'New user registered: fanwell69@gmail.com (user)', '209.145.54.84', '2026-04-17 13:46:33'),
(1007, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.68', '2026-04-17 14:02:35'),
(1008, 2, 'admin', 'resend_verification_email', 'Resent verification email to user ID: 215', '165.58.129.68', '2026-04-17 14:03:01'),
(1009, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.68', '2026-04-17 14:44:06'),
(1010, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.173', '2026-04-17 15:44:37'),
(1011, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.79', '2026-04-17 16:35:24'),
(1012, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.219', '2026-04-17 17:11:03'),
(1013, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.201', '2026-04-17 17:31:13'),
(1014, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.38', '2026-04-17 18:59:16'),
(1015, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.164', '2026-04-17 20:32:25'),
(1016, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.71', '2026-04-18 06:01:41'),
(1017, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.41', '2026-04-18 06:33:34'),
(1018, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.173', '2026-04-18 06:48:17'),
(1019, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.172', '2026-04-18 07:11:15'),
(1020, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.113', '2026-04-18 07:36:52'),
(1021, 2, 'admin', 'resend_verification_email', 'Resent verification email to dealer ID: 149', '165.56.66.113', '2026-04-18 07:37:22'),
(1022, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.113', '2026-04-18 07:52:19'),
(1023, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.113', '2026-04-18 08:34:59'),
(1024, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.205', '2026-04-18 09:39:59'),
(1025, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.205', '2026-04-18 10:34:37'),
(1026, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.205', '2026-04-18 10:49:51'),
(1027, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.205', '2026-04-18 11:15:53'),
(1028, 2, 'admin', 'resend_verification_email', 'Resent verification email to dealer ID: 149', '165.58.129.205', '2026-04-18 11:16:41'),
(1029, 2, 'admin', 'resend_verification_email', 'Resent verification email to dealer ID: 149', '165.58.129.205', '2026-04-18 11:19:42'),
(1030, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.205', '2026-04-18 11:43:34'),
(1031, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.205', '2026-04-18 11:58:58'),
(1032, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.205', '2026-04-18 12:06:29'),
(1033, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.205', '2026-04-18 13:56:39'),
(1034, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.205', '2026-04-18 14:16:09'),
(1035, 221, 'user', 'register', 'New user registered: hampongochonde@gmail.com (user)', '102.210.161.171', '2026-04-18 14:18:48'),
(1036, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.133', '2026-04-18 16:19:51'),
(1037, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.133', '2026-04-18 16:43:11'),
(1038, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.133', '2026-04-18 17:10:47'),
(1039, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.133', '2026-04-18 17:51:51'),
(1040, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.133', '2026-04-18 18:59:39'),
(1041, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.133', '2026-04-18 19:13:01'),
(1042, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.241', '2026-04-18 19:43:27'),
(1043, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.189', '2026-04-18 20:06:14'),
(1044, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.19', '2026-04-18 21:03:02'),
(1045, 130, 'dealer', 'login', 'User logged in successfully', '45.215.237.205', '2026-04-19 03:48:17'),
(1046, 130, 'dealer', 'login', 'User logged in successfully', '165.58.129.114', '2026-04-19 05:34:59'),
(1047, 130, 'dealer', 'login', 'User logged in successfully', '165.58.129.114', '2026-04-19 05:46:19'),
(1048, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.207', '2026-04-19 06:05:19'),
(1049, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.160', '2026-04-19 07:05:40'),
(1050, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.160', '2026-04-19 07:09:27'),
(1051, 2, 'admin', 'delete_user', 'Deleted user ID: 179', '165.58.129.160', '2026-04-19 07:10:01'),
(1052, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.160', '2026-04-19 07:11:36'),
(1053, NULL, 'dealer', 'register', 'New user registered: chisalavudo@gmail.com (dealer)', '165.58.129.160', '2026-04-19 07:13:58'),
(1054, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.160', '2026-04-19 07:15:15'),
(1055, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.160', '2026-04-19 07:22:33'),
(1056, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.16', '2026-04-19 08:50:26'),
(1057, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.16', '2026-04-19 08:51:02'),
(1058, 2, 'admin', 'delete_user', 'Deleted user ID: 222', '165.58.129.16', '2026-04-19 08:51:25'),
(1059, 223, 'dealer', 'register', 'New user registered: chisalavudo@gmail.com (dealer)', '165.58.129.16', '2026-04-19 08:53:16'),
(1060, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.16', '2026-04-19 09:45:45'),
(1061, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.16', '2026-04-19 10:30:22'),
(1062, 224, 'dealer', 'register', 'New user registered: bjambo42@gmail.com (dealer)', '41.173.23.70', '2026-04-19 10:52:14'),
(1063, 224, 'dealer', 'login', 'User logged in successfully', '41.173.23.70', '2026-04-19 10:57:36'),
(1064, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.16', '2026-04-19 12:20:50'),
(1065, 2, 'admin', 'send_email', 'Sent email to user ID: 198. Subject: Property upload ', '165.58.129.16', '2026-04-19 12:23:29'),
(1066, 2, 'admin', 'send_email', 'Sent email to user ID: 4. Subject: Welcome', '165.58.129.16', '2026-04-19 12:23:55'),
(1067, 2, 'admin', 'send_email', 'Sent email to user ID: 4. Subject: Welcome', '165.58.129.16', '2026-04-19 12:24:27'),
(1068, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.16', '2026-04-19 12:24:59'),
(1069, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.16', '2026-04-19 12:48:52'),
(1070, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.93', '2026-04-19 14:36:16'),
(1071, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.93', '2026-04-19 16:10:34'),
(1072, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.62', '2026-04-19 16:35:22'),
(1073, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.62', '2026-04-19 16:52:41'),
(1074, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.62', '2026-04-19 18:01:33'),
(1075, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.8', '2026-04-19 19:50:04'),
(1076, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.76', '2026-04-19 20:49:56'),
(1077, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.29', '2026-04-19 22:22:02'),
(1078, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.117', '2026-04-20 06:01:18'),
(1079, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.59', '2026-04-20 09:01:44'),
(1080, 225, 'dealer', 'register', 'New user registered: kelvinvoicemusic@gmail.com (dealer)', '165.57.81.100', '2026-04-20 10:42:47'),
(1081, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.59', '2026-04-20 11:33:00'),
(1082, 2, 'admin', 'resend_verification_email', 'Resent verification email to dealer ID: 225', '165.58.129.59', '2026-04-20 11:40:09'),
(1083, 2, 'admin', 'resend_verification_email', 'Resent verification email to dealer ID: 225', '165.58.129.59', '2026-04-20 11:41:56'),
(1084, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.59', '2026-04-20 11:51:32'),
(1085, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.59', '2026-04-20 12:29:09'),
(1086, 2, 'admin', 'send_email', 'Sent email to user ID: 223. Subject: Welcome', '165.58.129.59', '2026-04-20 12:30:23'),
(1087, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.134', '2026-04-20 13:32:08'),
(1088, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.203', '2026-04-20 14:02:33'),
(1089, 2, 'admin', 'send_email', 'Sent email to user ID: 225. Subject: verification', '165.57.81.235', '2026-04-20 14:26:07'),
(1090, 4, 'dealer', 'login', 'User logged in successfully', '165.57.81.235', '2026-04-20 14:58:32'),
(1091, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.43', '2026-04-20 15:06:54'),
(1092, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.74', '2026-04-20 15:56:39'),
(1093, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.74', '2026-04-20 17:16:06'),
(1094, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.68', '2026-04-20 17:55:14'),
(1095, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.68', '2026-04-20 18:15:09'),
(1096, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.68', '2026-04-20 18:22:21'),
(1097, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.68', '2026-04-20 18:54:08'),
(1098, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.171', '2026-04-20 19:11:49'),
(1099, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.86', '2026-04-20 19:27:12'),
(1100, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.8', '2026-04-20 19:56:55'),
(1101, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.41', '2026-04-20 20:20:15'),
(1102, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.90', '2026-04-20 20:39:09'),
(1103, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.154', '2026-04-20 21:05:31'),
(1104, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.200', '2026-04-21 04:25:06'),
(1105, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.120', '2026-04-21 05:43:46'),
(1106, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.246', '2026-04-21 06:19:02'),
(1107, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.218', '2026-04-21 06:34:45'),
(1108, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.29', '2026-04-21 07:19:24'),
(1109, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.58', '2026-04-21 08:08:12'),
(1110, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.136', '2026-04-21 10:49:43'),
(1111, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.136', '2026-04-21 11:14:22'),
(1112, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.59', '2026-04-21 13:56:56'),
(1113, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.59', '2026-04-21 14:22:32'),
(1114, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.59', '2026-04-21 14:35:52'),
(1115, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.59', '2026-04-21 15:16:23'),
(1116, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.59', '2026-04-21 15:24:57'),
(1117, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.50', '2026-04-21 17:41:58'),
(1118, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.50', '2026-04-21 17:51:12'),
(1119, 2, 'admin', 'send_email', 'Sent email to user ID: 223. Subject: Welcome', '165.56.186.50', '2026-04-21 17:52:01'),
(1120, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.50', '2026-04-21 19:00:52'),
(1121, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.0', '2026-04-21 20:07:30'),
(1122, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.39', '2026-04-21 20:28:31'),
(1123, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.104', '2026-04-21 20:44:17'),
(1124, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.38', '2026-04-22 05:40:11'),
(1125, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.178', '2026-04-22 05:50:16'),
(1126, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.241', '2026-04-22 06:00:16'),
(1127, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.70', '2026-04-22 06:50:30'),
(1128, 226, 'user', 'register', 'New user registered: jonathanmwale711@gmail.com (user)', '45.215.255.24', '2026-04-22 07:44:01'),
(1129, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.8', '2026-04-22 07:46:26'),
(1130, 227, 'user', 'register', 'New user registered: rhyanmalama0@gmail.com (user)', '45.215.251.80', '2026-04-22 08:17:54'),
(1131, 228, 'user', 'register', 'New user registered: nelsonlungu28@gmail.com (user)', '45.215.237.237', '2026-04-22 08:37:35'),
(1132, 229, 'user', 'register', 'New user registered: arnoldbandachabdollar@gmail.com (user)', '41.223.116.252', '2026-04-22 08:39:52'),
(1133, 229, 'user', 'login', 'User logged in successfully', '41.223.116.252', '2026-04-22 08:42:37'),
(1134, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.174', '2026-04-22 11:04:44'),
(1135, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.174', '2026-04-22 11:13:47'),
(1136, 230, 'user', 'register', 'New user registered: twambonakuweza4@gmail.com (user)', '45.215.251.24', '2026-04-22 11:29:54'),
(1137, 230, 'user', 'login', 'User logged in successfully', '45.215.251.24', '2026-04-22 11:32:03'),
(1138, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.5', '2026-04-22 11:56:52'),
(1139, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.249', '2026-04-22 13:20:16'),
(1140, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.86', '2026-04-22 13:47:59'),
(1141, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.86', '2026-04-22 14:02:29'),
(1142, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.86', '2026-04-22 14:12:44'),
(1143, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.86', '2026-04-22 14:19:15'),
(1144, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.86', '2026-04-22 14:19:46'),
(1145, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.86', '2026-04-22 14:20:02'),
(1146, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.86', '2026-04-22 14:23:00'),
(1147, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.179', '2026-04-22 14:49:30'),
(1148, 232, 'user', 'register', 'New user registered: julietchola414@gmail.com (user)', '45.215.251.18', '2026-04-22 15:01:38'),
(1149, 232, 'user', 'login', 'User logged in successfully', '45.215.251.18', '2026-04-22 15:09:48'),
(1150, 233, 'user', 'register', 'New user registered: lynessmwanza3@gmail.com (user)', '45.215.237.159', '2026-04-22 15:11:21'),
(1151, 233, 'user', 'login', 'User logged in successfully', '45.215.237.159', '2026-04-22 15:12:43'),
(1152, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.192', '2026-04-22 15:21:43'),
(1153, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.192', '2026-04-22 15:23:14'),
(1154, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.192', '2026-04-22 15:28:20'),
(1155, 234, 'user', 'register', 'New user registered: ngandweraymumba@gmail.com (user)', '41.216.73.21', '2026-04-22 15:36:01'),
(1156, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.192', '2026-04-22 15:46:06'),
(1157, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.192', '2026-04-22 16:14:54'),
(1158, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.38', '2026-04-22 17:19:40'),
(1159, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.38', '2026-04-22 17:32:35'),
(1160, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.38', '2026-04-22 17:46:52'),
(1161, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.59', '2026-04-22 18:43:03'),
(1162, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.59', '2026-04-22 18:49:21'),
(1163, 229, 'user', 'login', 'User logged in successfully', '41.60.179.142', '2026-04-22 18:55:33'),
(1164, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.43', '2026-04-22 19:52:00'),
(1165, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.43', '2026-04-22 19:54:14'),
(1166, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.43', '2026-04-22 20:05:15'),
(1167, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.43', '2026-04-22 20:19:22'),
(1168, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.43', '2026-04-22 20:24:07'),
(1169, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.79', '2026-04-22 21:10:45'),
(1170, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.102', '2026-04-22 22:34:19'),
(1171, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.70', '2026-04-22 22:54:10'),
(1172, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.109', '2026-04-23 04:58:36'),
(1173, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.182', '2026-04-23 06:43:02'),
(1174, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.165', '2026-04-23 08:20:00'),
(1175, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.233', '2026-04-23 10:59:01'),
(1176, 4, 'dealer', 'login', 'User logged in successfully', '165.58.129.233', '2026-04-23 11:00:10'),
(1177, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.80', '2026-04-23 11:02:34'),
(1178, 4, 'dealer', 'login', 'User logged in successfully', '165.56.66.3', '2026-04-23 11:15:54'),
(1179, 4, 'dealer', 'login', 'User logged in successfully', '165.56.66.3', '2026-04-23 11:17:38'),
(1180, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.3', '2026-04-23 11:20:15'),
(1181, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.192', '2026-04-23 11:32:06'),
(1182, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.192', '2026-04-23 11:56:24'),
(1183, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.151', '2026-04-23 12:08:35'),
(1184, NULL, 'dealer', 'register', 'New user registered: luckchisala@gmail.com (dealer)', '165.56.186.151', '2026-04-23 12:30:26'),
(1185, 236, 'user', 'register', 'New user registered: bandacecilia63@gmail.com (user)', '41.216.95.236', '2026-04-23 12:38:31'),
(1186, NULL, 'dealer', 'login', 'User logged in successfully', '165.58.129.222', '2026-04-23 12:42:59'),
(1187, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.222', '2026-04-23 12:46:12'),
(1188, 2, 'admin', 'delete_user', 'Deleted user ID: 235', '165.58.129.222', '2026-04-23 12:46:35'),
(1189, 237, 'user', 'register', 'New user registered: lukachongo2020@gmail.com (user)', '165.56.186.221', '2026-04-23 13:37:42'),
(1190, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.99', '2026-04-23 13:38:22'),
(1191, 237, 'user', 'login', 'User logged in successfully', '165.56.186.221', '2026-04-23 13:39:50'),
(1192, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.99', '2026-04-23 13:54:47'),
(1193, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.141', '2026-04-23 14:21:36'),
(1194, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.141', '2026-04-23 14:42:02'),
(1195, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.245', '2026-04-23 14:50:01'),
(1196, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.245', '2026-04-23 14:55:19'),
(1197, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.24', '2026-04-23 15:09:17'),
(1198, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.24', '2026-04-23 15:21:52'),
(1199, 2, 'admin', 'login', 'User logged in successfully', '165.56.66.199', '2026-04-23 15:58:58'),
(1200, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.131', '2026-04-23 16:10:14'),
(1201, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.131', '2026-04-23 16:52:25'),
(1202, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.24', '2026-04-23 17:23:33'),
(1203, 2, 'admin', 'login', 'User logged in successfully', '165.56.186.24', '2026-04-23 17:28:25'),
(1204, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.127', '2026-04-23 20:41:46'),
(1205, 2, 'admin', 'login', 'User logged in successfully', '45.215.236.178', '2026-04-24 05:35:07'),
(1206, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.37', '2026-04-24 06:11:25'),
(1207, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.78', '2026-04-24 07:52:08'),
(1208, 238, 'user', 'register', 'New user registered: katongoinnocent27@gmail.com (user)', '41.216.82.25', '2026-04-24 12:39:11'),
(1209, 238, 'user', 'login', 'User logged in successfully', '41.216.82.25', '2026-04-24 12:41:04'),
(1210, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.136', '2026-04-24 13:53:13'),
(1211, 2, 'admin', 'login', 'User logged in successfully', '165.57.81.136', '2026-04-24 14:05:37'),
(1212, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.211', '2026-04-24 14:12:49'),
(1213, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.151', '2026-04-24 14:35:33');

-- --------------------------------------------------------

--
-- Table structure for table `app_feedbacks_and_requests`
--

CREATE TABLE `app_feedbacks_and_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `request_type` enum('property_request','app_improvement') NOT NULL,
  `description` text NOT NULL,
  `preferred_location` varchar(255) DEFAULT NULL,
  `budget_range` varchar(100) DEFAULT NULL,
  `status` enum('pending','reviewed','actioned') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dealers`
--

CREATE TABLE `dealers` (
  `user_id` int(11) NOT NULL,
  `company_name` varchar(100) DEFAULT NULL,
  `office_address` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `subscription_status` enum('active','expired','none') DEFAULT 'none',
  `subscription_expiry` timestamp NULL DEFAULT NULL,
  `referral_earnings` decimal(10,2) NOT NULL DEFAULT 0.00,
  `referral_milestone_awarded` tinyint(1) NOT NULL DEFAULT 0,
  `referral_discount_used` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dealers`
--

INSERT INTO `dealers` (`user_id`, `company_name`, `office_address`, `bio`, `subscription_status`, `subscription_expiry`, `referral_earnings`, `referral_milestone_awarded`, `referral_discount_used`) VALUES
(4, NULL, NULL, NULL, 'active', '2026-05-19 18:39:21', 15.00, 0, 0),
(38, NULL, NULL, NULL, 'active', '2026-03-31 00:42:02', 0.00, 0, 0),
(49, NULL, NULL, NULL, 'active', '2026-04-02 08:23:23', 0.00, 0, 0),
(50, NULL, NULL, NULL, 'active', '2026-04-04 20:41:52', 0.00, 0, 0),
(130, NULL, NULL, NULL, 'active', '2026-04-30 08:54:45', 0.00, 0, 0),
(132, NULL, NULL, NULL, 'active', '2026-05-01 13:33:41', 0.00, 0, 0),
(145, NULL, NULL, NULL, 'active', '2026-05-18 18:17:33', 0.00, 0, 0),
(149, NULL, NULL, NULL, 'active', '2026-05-14 14:29:33', 0.00, 0, 0),
(189, NULL, NULL, NULL, 'active', '2026-05-15 14:19:36', 0.00, 0, 0),
(190, NULL, NULL, NULL, 'active', '2026-05-15 14:44:23', 0.00, 0, 0),
(191, NULL, NULL, NULL, 'active', '2026-05-15 14:47:22', 0.00, 0, 0),
(196, NULL, NULL, NULL, 'active', '2026-05-15 15:41:41', 0.00, 0, 0),
(198, NULL, NULL, NULL, 'active', '2026-05-15 15:53:26', 0.00, 0, 0),
(204, NULL, NULL, NULL, 'active', '2026-05-15 21:52:08', 0.00, 0, 0),
(223, NULL, NULL, NULL, 'active', '2026-05-19 12:53:15', 0.00, 0, 0),
(224, NULL, NULL, NULL, 'active', '2026-05-19 14:52:14', 0.00, 0, 0),
(225, NULL, NULL, NULL, 'active', '2026-05-20 14:42:47', 0.00, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `landlord_ratings`
--

CREATE TABLE `landlord_ratings` (
  `id` int(11) NOT NULL,
  `dealer_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `landlord_ratings`
--

INSERT INTO `landlord_ratings` (`id`, `dealer_id`, `property_id`, `user_id`, `rating`, `review`, `created_at`, `updated_at`) VALUES
(1, 191, 24, 4, 3, '', '2026-04-16 16:28:26', '2026-04-16 16:28:26'),
(2, 4, 11, 135, 3, '', '2026-04-16 16:43:29', '2026-04-16 16:43:29'),
(3, 191, 24, 135, 3, '', '2026-04-16 20:09:31', '2026-04-16 22:55:37');

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `dealer_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `property_id`, `dealer_id`, `name`, `email`, `phone`, `message`, `created_at`) VALUES
(1, 7, 4, 'Lackson Chisala', 'chisalaluckykb5@gmail.com', '0771355473', 'I&#039;m interested in this property.', '2026-02-25 15:19:55'),
(2, 7, 4, 'Lackson Chisala', 'chisalaluckyk5@gmail.com', '0771355473', 'I&#039;m interested in this property.', '2026-02-25 15:54:00'),
(3, 7, 4, 'ZAP', 'foo-bar@example.com', 'ZAP', 'I&#039;m interested in this property.', '2026-02-26 06:08:47'),
(4, 3, 5, 'ZAP', 'foo-bar@example.com', 'ZAP', 'I&#039;m interested in this property.', '2026-02-26 06:08:48'),
(5, 6, 4, 'ZAP', 'foo-bar@example.com', 'ZAP', 'I&#039;m interested in this property.', '2026-02-26 06:08:48'),
(6, 12, 50, 'We&#039;re are you located', 'mutale@gmail.com', '0771355475', 'I&#039;m interested in this property.', '2026-03-07 13:04:41'),
(7, 9, 4, 'Lackson Chisala', 'chisalaluckyk5@gmail.com', '0771355473', 'I&#039;m interested in this property.', '2026-03-07 13:05:13'),
(8, 9, 4, 'Mutinta Muyamwa', 'muyamwaprincess@gmail.com', '0972508280', 'I&#039;m interested in this property. Is it available?', '2026-03-08 04:35:09'),
(9, 11, 4, 'SxrphbWygFFalaHsLe', 'i.pine.qo.y.o.2.0@gmail.com', '7831123436', 'I&#039;m interested in this property.', '2026-03-11 11:59:13'),
(10, 11, 4, 'WJvuodshHxBwmteb', 'aja.l.i.va.gosi5.36@gmail.com', '5612923983', 'I&#039;m interested in this property.', '2026-03-13 22:45:11'),
(11, 9, 4, 'VxOzpxmVUcqycCotjF', 'o.qu.p.u.p.uh18@gmail.com', '6110355567', 'I&#039;m interested in this property.', '2026-03-25 23:29:19'),
(12, 7, 4, 'bLGMAkEqoQMVNGAP', 'x.uqe.saz.a.g.u.s.62.1@gmail.com', '5928825564', 'I&#039;m interested in this property.', '2026-03-27 21:32:24'),
(13, 9, 4, 'Aaliyah', 'janeaaliyah84@gmail.com', 'Phone : 076 684 3216 / WhatsApp : +263 778869669', 'I&#039;m interested in this property.', '2026-04-01 03:01:00'),
(14, 11, 4, 'chisala', 'chisalaluckyk5@gmail.com', '077012506', 'chisala', '2026-04-01 08:00:33'),
(15, 20, 130, 'vjsnsm', 'chisalaluckyk5@gmail.com', '077012506', 'bxnsj', '2026-04-01 16:31:25'),
(16, 11, 4, 'naomi', 'chisalaluckson27@gmail.com', '0000000', 'bnNz', '2026-04-03 12:19:58'),
(17, 20, 130, 'lebeWRTIrBUqTgcidwG', 'q.og.im.iyi.3.8@gmail.com', '3830037908', 'I&#039;m interested in this property.', '2026-04-05 02:37:49'),
(18, 9, 4, 'lOKXvszWfKSMkVKzx', 'in.ip.ew.ax.ato0.0.2@gmail.com', '6224492587', 'I&#039;m interested in this property.', '2026-04-08 12:18:59'),
(19, 10, 4, 'ZsYyBhlRfmectRqXCLX', 'q.ewuyiru.6.2.0@gmail.com', '4465229051', 'I&#039;m interested in this property.', '2026-04-19 08:14:40'),
(20, 34, 130, 'Innocent Katongo', 'katongoinnocent27@gmail.com', '0971446900', 'Is this house a stand alone or shared? I love it', '2026-04-24 08:33:59');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `property_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','warning','danger','success') DEFAULT 'info',
  `target_role` enum('all','dealer','user') DEFAULT 'all',
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `title`, `message`, `type`, `target_role`, `is_active`, `created_by`, `created_at`) VALUES
(4, 'info', '🚨 Exciting Update from HouseRent Africa! 🏡\r\n\r\nHello everyone!\r\n\r\nWe’re happy to have you as part of the HouseRent Africa community. Our goal is to make finding and listing homes easier, faster, and more reliable for everyone.\r\n\r\nWhether you’re looking for a place to rent or you’re a landlord with property to list, HouseRent Africa is here to connect you seamlessly.\r\n\r\n✨ What you can do on the app:\r\n\r\n* Find affordable and verified rental homes\r\n* List your property بسهولة and reach more tenants\r\n* Communicate directly and securely\r\n\r\nWe’re continuously improving the platform to give you the best experience. Your feedback means everything to us—feel free to share your thoughts!\r\n\r\nThank you for trusting HouseRent Africa 💙', 'info', 'all', 1, 2, '2026-04-13 19:48:37'),
(5, 'Landlords', 'lets upload and get tenants', 'info', 'all', 1, 2, '2026-04-16 15:15:04'),
(10, 'HouseRent Africa', 'We happy to announce of new features on the app update app to enjoy more features, we made sure the app is optimized', 'success', 'all', 1, 2, '2026-04-16 21:50:05'),
(11, 'New Update', '🚀 Big News!\r\n\r\nYou can now refer a friend to list their property on HouseRent and earn **30% commission** when they get started!\r\n\r\nDon’t miss out on this opportunity to earn while helping others find tenants faster.\r\n\r\n👉 Refer now and start earning!', 'info', 'all', 1, 2, '2026-04-20 13:33:47'),
(12, 'important info', '⚠️ Important Notice\r\n\r\nIf any landlord asks you to pay to view reports, please report them immediately. Viewing reports should not require any extra charges.\r\n\r\nAlso, **never share your login details with anyone**. For your safety, report anyone who asks for your account information.\r\n\r\n📞 The only official number we use is **0772125121**.\r\n\r\nStay safe and protect your account!', 'warning', 'all', 1, 2, '2026-04-20 14:03:00'),
(13, '😀😀', 'Dear customers HouseRent wishes you a happy evening', 'info', 'all', 1, 2, '2026-04-22 14:37:02'),
(14, '💯💯', 'New features download the new update', 'success', 'all', 1, 2, '2026-04-22 14:39:23'),
(15, 'Invite', 'Invite landlords and get 30% bonus cash', 'info', 'all', 1, 2, '2026-04-22 14:40:08'),
(16, '✅✅✅🫷', 'Say no to an verified agents', 'info', 'all', 1, 2, '2026-04-22 14:41:38'),
(18, 'update', 'dealers you can use the app to make payment only K20', 'info', 'all', 1, 2, '2026-04-23 12:11:42');

-- --------------------------------------------------------

--
-- Table structure for table `notification_reads`
--

CREATE TABLE `notification_reads` (
  `id` int(11) NOT NULL,
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `read_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_reads`
--

INSERT INTO `notification_reads` (`id`, `notification_id`, `user_id`, `read_at`) VALUES
(1, 1, 4, '2026-03-14 17:44:45'),
(2, 2, 4, '2026-03-15 05:44:58'),
(3, 1, 66, '2026-03-15 11:27:52'),
(4, 2, 66, '2026-03-15 11:28:03'),
(5, 2, 130, '2026-03-31 05:00:48'),
(8, 3, 4, '2026-04-05 14:08:55'),
(12, 3, 135, '2026-04-05 14:10:08'),
(22, 3, 47, '2026-04-09 17:57:34'),
(23, 4, 135, '2026-04-14 14:26:22'),
(26, 4, 4, '2026-04-14 16:58:44');

-- --------------------------------------------------------

--
-- Table structure for table `premium_contacts`
--

CREATE TABLE `premium_contacts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `transaction_reference` varchar(255) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 5.00,
  `lenco_reference` varchar(255) DEFAULT NULL,
  `payment_type` varchar(50) DEFAULT NULL,
  `operator` varchar(50) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `account_name` varchar(255) DEFAULT NULL,
  `operator_transaction_id` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `dealer_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'ZMW',
  `bedrooms` int(11) DEFAULT NULL,
  `bathrooms` int(11) DEFAULT NULL,
  `rooms` int(11) DEFAULT NULL,
  `size_sqm` decimal(10,2) DEFAULT NULL,
  `property_type` enum('house','apartment','flat','boarding_house','land','commercial','wedding_venue','restaurant','lodge','studio','cottage','manor') NOT NULL DEFAULT 'house',
  `listing_purpose` enum('rent','sale','booking','service') NOT NULL DEFAULT 'rent',
  `location` varchar(255) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `status` enum('available','rented') DEFAULT 'available',
  `verification_image` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `amenities` text DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_boosted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `capacity` int(11) DEFAULT NULL,
  `people_per_room` int(11) DEFAULT NULL,
  `event_type` varchar(255) DEFAULT NULL,
  `catering_available` tinyint(1) DEFAULT 0,
  `equipment_available` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `dealer_id`, `title`, `description`, `price`, `currency`, `bedrooms`, `bathrooms`, `rooms`, `size_sqm`, `property_type`, `listing_purpose`, `location`, `city`, `country`, `latitude`, `longitude`, `status`, `verification_image`, `is_verified`, `amenities`, `video_url`, `views`, `is_featured`, `is_boosted`, `created_at`, `capacity`, `people_per_room`, `event_type`, `catering_available`, `equipment_available`) VALUES
(6, 4, 'Chunga hill', 'Very neat ', 2500.00, 'ZMW', 3, 2, 6, 8.00, 'flat', 'rent', '257', 'Lusaka', 'Zambia', -15.37027407, 28.29425812, '', NULL, 1, '', '', 301, 1, 0, '2026-02-24 12:38:43', NULL, NULL, NULL, 0, 0),
(7, 4, 'Chalala ', 'Very neat', 5800.00, 'ZMW', 4, 3, 10, 80.00, 'house', 'rent', 'Chalala mall', 'Chipata', 'Zambia', -15.46459998, 28.34266663, 'rented', NULL, 1, 'Wifi, solar', '', 371, 1, 0, '2026-02-24 12:40:09', NULL, NULL, NULL, 0, 0),
(9, 4, 'Neat house ', 'This house it&amp;#039;s very neat.\r\nHas everything you need ', 8000.00, 'ZMW', 4, 2, 8, 100.00, 'house', 'rent', 'Chalala mall', 'Lusaka', 'Zambia', NULL, NULL, 'available', NULL, 1, '', '', 292, 1, 0, '2026-03-02 15:43:35', NULL, NULL, NULL, 0, 0),
(10, 4, 'Weddings', 'Very ckean', 4800.00, 'ZMW', NULL, NULL, NULL, 8000.00, '', 'service', 'Lusaka', 'Lusaka', 'Zambia', -15.37810204, 28.32848628, 'available', NULL, 1, '', '', 173, 1, 0, '2026-03-02 23:29:41', 6000, NULL, 'Weddings', 1, 1),
(11, 4, 'Salama', 'Bmnbvvccc', 3000.00, 'ZMW', NULL, NULL, NULL, 7.00, '', 'service', 'Matero', 'Lusaka', 'Zambia', -15.37738230, 28.26143320, 'available', NULL, 1, 'WiFi,Parking,Security,Air Conditioning,Swimming Pool', '', 191, 1, 0, '2026-03-02 23:32:59', 100, NULL, 'restaurant ', 1, 1),
(12, 50, 'Parkview Boarding house ', '', 850.00, 'ZMW', 2, 2, 3, 0.00, 'boarding_house', 'rent', 'Parkview boarding house ', 'Lusaka', 'Zambia', -15.41746306, 28.28246176, 'available', NULL, 1, '', '', 126, 0, 0, '2026-03-05 16:48:33', NULL, 6, NULL, 0, 0),
(13, 50, 'Parkview Boarding house ', '', 850.00, 'ZMW', 2, 2, 3, 0.00, 'boarding_house', 'rent', 'Parkview boarding house ', 'Lusaka', 'Zambia', -15.41746306, 28.28246176, 'available', NULL, 1, '', '', 109, 0, 0, '2026-03-05 16:48:33', NULL, 6, NULL, 0, 0),
(20, 130, 'Female Boarding houses', 'Mass media female boarding hpuse.\r\n15mins walk to Unilus Pioneer \r\n15mins walk to CUZ medical campus \r\n5mins walk to UNZA\r\n2mins walk to Chreso', 1500.00, 'ZMW', 6, 4, 14, 100.00, 'boarding_house', 'service', 'Alick Nkhata Road', 'Lusaka', 'Zambia', -15.40642960, 28.32837460, 'available', NULL, 1, '', 'https://vt.tiktok.com/ZSH2CRtNQ/', 107, 0, 0, '2026-04-01 06:09:47', NULL, 4, NULL, 0, 0),
(24, 191, '4 Bedroomed house for sale', 'HOUSE FOR SALE – EAST OF GARNETON\n✅ 4 Bedroomed House\n✅ Sitting on a 20 × 30 plot\n✅ Borehole available 💧\n📍 Located East of Garneton, Kitwe\n💰 Price: K560,000\ncall/app: 0963111800', 560000.00, 'ZMW', 4, 2, 0, 0.00, 'house', 'sale', 'East of garnaton', 'kitwe', 'Zambia', -15.38750000, 28.32280000, 'available', NULL, 1, '', '', 47, 0, 0, '2026-04-16 03:31:52', NULL, NULL, NULL, 0, 0),
(31, 130, 'Female bed sitter ', 'Kalundu\r\n4min walk est park ', 3000.00, 'ZMW', 4, 2, 2, 50.00, 'boarding_house', 'rent', 'Kalundu', 'Lusaka', 'Zambia', -15.37915590, 28.32573900, 'available', NULL, 0, 'Water 24/7', '', 37, 0, 0, '2026-04-19 05:38:08', NULL, 2, NULL, 0, 0),
(32, 130, 'Helen kaunda', 'K1000 to be be 4,\r\nK1200 self contained to be 4', 1000.00, 'ZMW', 8, 2, 8, 2.00, 'boarding_house', 'rent', 'Helen Kaunda', 'Lusaka', 'Zambia', -15.40441910, 28.34473380, 'available', NULL, 0, '', '', 30, 0, 0, '2026-04-19 05:52:09', NULL, 4, NULL, 0, 0),
(33, 130, 'Kalingalinga female bh', '9mins walk to unza and chreso,\r\n25mins walk walk to unlis pioneer and cuz', 1250.00, 'ZMW', 3, 2, 4, 20.00, 'boarding_house', 'rent', 'Kalingalinga', 'Lusaka', 'Zambia', -15.40378110, 28.33012260, 'available', NULL, 0, '', '', 31, 0, 0, '2026-04-19 05:56:03', NULL, 3, NULL, 0, 0),
(34, 130, 'Ridgeway Campus', '5mins walk to Ridgway\r\nTo be 2 lediea\r\n5mins walk to Ridgeway\r\n', 2000.00, 'ZMW', 4, 2, 4, 20.00, 'boarding_house', 'rent', 'Ridgeway', 'Lusaka', 'Zambia', -15.43308560, 28.31989400, 'available', NULL, 0, '', '', 37, 0, 0, '2026-04-19 06:03:17', NULL, 2, NULL, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `property_images`
--

CREATE TABLE `property_images` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_main` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` enum('image','video') DEFAULT 'image'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_images`
--

INSERT INTO `property_images` (`id`, `property_id`, `image_path`, `is_main`, `created_at`, `type`) VALUES
(21, 6, 'assets/images/properties/prop_6_699d9be777772.jpg', 0, '2026-02-24 12:39:03', 'image'),
(22, 6, 'assets/images/properties/prop_6_699d9be777c87.jpg', 0, '2026-02-24 12:39:03', 'image'),
(23, 7, 'assets/images/properties/prop_7_699d9c37a7d88.jpg', 0, '2026-02-24 12:40:23', 'image'),
(24, 7, 'assets/images/properties/prop_7_699d9c37a8196.jpg', 0, '2026-02-24 12:40:23', 'image'),
(25, 7, 'assets/images/properties/vid_7_699ed49301954.mp4', 0, '2026-02-25 10:53:07', 'video'),
(27, 9, 'assets/images/properties/prop_9_69a5b0362987b.jpg', 0, '2026-03-02 15:43:50', 'image'),
(28, 10, 'assets/images/properties/prop_10_69a61d7343b7c.jpg', 0, '2026-03-02 23:29:55', 'image'),
(29, 11, 'assets/images/properties/prop_11_69a61e382e9ae.jpg', 0, '2026-03-02 23:33:12', 'image'),
(36, 20, 'assets/images/properties/prop_20_69ccb71139130.jpg', 0, '2026-04-01 06:11:29', 'image'),
(37, 20, 'assets/images/properties/prop_20_69ccba40e6bc8.jpg', 0, '2026-04-01 06:25:04', 'image'),
(38, 20, 'assets/images/properties/prop_20_69ccbad519730.jpg', 0, '2026-04-01 06:27:33', 'image'),
(39, 20, 'assets/images/properties/prop_20_69ccbad51b17e.jpg', 0, '2026-04-01 06:27:33', 'image'),
(42, 24, 'assets/images/properties/prop_69e084d981b8b_1776321753.jpg', 1, '2026-04-16 06:42:33', 'image'),
(43, 24, 'assets/images/properties/prop_69e084d98694c_1776321753.jpg', 0, '2026-04-16 06:42:33', 'image'),
(44, 24, 'assets/images/properties/prop_69e084d98a44f_1776321753.jpg', 0, '2026-04-16 06:42:33', 'image'),
(45, 24, 'assets/images/properties/prop_69e084d98c1b2_1776321753.jpg', 0, '2026-04-16 06:42:33', 'image'),
(50, 31, 'assets/images/properties/prop_31_69e46b15190f3.jpg', 0, '2026-04-19 05:41:41', 'image'),
(51, 31, 'assets/images/properties/prop_31_69e46b1519729.jpg', 0, '2026-04-19 05:41:41', 'image'),
(52, 31, 'assets/images/properties/prop_31_69e46b1519c4f.jpg', 0, '2026-04-19 05:41:41', 'image'),
(53, 31, 'assets/images/properties/prop_31_69e46b151a019.jpg', 0, '2026-04-19 05:41:41', 'image'),
(54, 31, 'assets/images/properties/prop_31_69e46b151a44f.jpg', 0, '2026-04-19 05:41:41', 'image'),
(55, 31, 'assets/images/properties/prop_31_69e46b151a7c4.jpg', 0, '2026-04-19 05:41:41', 'image'),
(56, 32, 'assets/images/properties/prop_32_69e46db30f718.jpg', 0, '2026-04-19 05:52:51', 'image'),
(57, 32, 'assets/images/properties/prop_32_69e46db30fbd6.jpg', 0, '2026-04-19 05:52:51', 'image'),
(58, 32, 'assets/images/properties/prop_32_69e46db30ff7a.jpg', 0, '2026-04-19 05:52:51', 'image'),
(59, 32, 'assets/images/properties/prop_32_69e46db310326.jpg', 0, '2026-04-19 05:52:51', 'image'),
(60, 32, 'assets/images/properties/prop_32_69e46db3117d4.jpg', 0, '2026-04-19 05:52:51', 'image'),
(61, 32, 'assets/images/properties/prop_32_69e46db311eae.jpg', 0, '2026-04-19 05:52:51', 'image'),
(62, 33, 'assets/images/properties/prop_33_69e46e8403c3a.jpg', 0, '2026-04-19 05:56:20', 'image'),
(63, 33, 'assets/images/properties/prop_33_69e46e8404105.jpg', 0, '2026-04-19 05:56:20', 'image'),
(64, 33, 'assets/images/properties/prop_33_69e46e840444c.jpg', 0, '2026-04-19 05:56:20', 'image'),
(65, 33, 'assets/images/properties/prop_33_69e46e8404774.jpg', 0, '2026-04-19 05:56:20', 'image'),
(66, 34, 'assets/images/properties/prop_34_69e4704465ea2.jpg', 0, '2026-04-19 06:03:48', 'image'),
(67, 34, 'assets/images/properties/prop_34_69e47044681cd.jpg', 0, '2026-04-19 06:03:48', 'image'),
(68, 34, 'assets/images/properties/prop_34_69e4704469161.jpg', 0, '2026-04-19 06:03:48', 'image'),
(69, 34, 'assets/images/properties/prop_34_69e470446ad5b.jpg', 0, '2026-04-19 06:03:48', 'image'),
(70, 34, 'assets/images/properties/prop_34_69e470446cab5.jpg', 0, '2026-04-19 06:03:48', 'image'),
(71, 34, 'assets/images/properties/prop_34_69e470446f39b.jpg', 0, '2026-04-19 06:03:48', 'image');

-- --------------------------------------------------------

--
-- Table structure for table `property_reports`
--

CREATE TABLE `property_reports` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reason` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','reviewed','dismissed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `property_reports`
--

INSERT INTO `property_reports` (`id`, `property_id`, `user_id`, `reason`, `details`, `status`, `created_at`) VALUES
(2, 7, NULL, 'fraud', '', 'pending', '2026-02-26 11:08:47'),
(4, 6, NULL, 'fraud', '', 'dismissed', '2026-02-26 11:08:48');

-- --------------------------------------------------------

--
-- Table structure for table `referral_rewards`
--

CREATE TABLE `referral_rewards` (
  `id` int(11) NOT NULL,
  `referrer_user_id` int(11) NOT NULL,
  `referred_user_id` int(11) DEFAULT NULL,
  `reward_type` enum('signup_bonus','milestone_bonus') NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `referral_rewards`
--

INSERT INTO `referral_rewards` (`id`, `referrer_user_id`, `referred_user_id`, `reward_type`, `amount`, `notes`, `created_at`) VALUES
(1, 4, NULL, 'signup_bonus', 15.00, 'Reward for verified referred dealer registration', '2026-04-19 07:14:55');

-- --------------------------------------------------------

--
-- Table structure for table `rentals`
--

CREATE TABLE `rentals` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `room_number` varchar(50) DEFAULT NULL,
  `dealer_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `status` enum('active','ended','pending') DEFAULT 'active',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `rent_amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'ZMW',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_reference` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `rentals`
--

INSERT INTO `rentals` (`id`, `property_id`, `room_number`, `dealer_id`, `tenant_id`, `status`, `start_date`, `end_date`, `rent_amount`, `currency`, `created_at`, `updated_at`, `payment_reference`) VALUES
(24, 6, 'flat 3', 4, 135, 'active', '2026-04-06', NULL, 1500.00, 'ZMW', '2026-04-06 12:27:48', '2026-04-06 12:27:48', '0337469484204496');

-- --------------------------------------------------------

--
-- Table structure for table `rent_payments`
--

CREATE TABLE `rent_payments` (
  `id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `month_year` varchar(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'ZMW',
  `proof_file` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `dealer_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `payment_method` enum('cash','bank_transfer','mobile_money','lenco') DEFAULT 'bank_transfer',
  `months_paid` int(11) DEFAULT 1,
  `reference` varchar(255) DEFAULT NULL,
  `lenco_reference` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `rent_payments`
--

INSERT INTO `rent_payments` (`id`, `rental_id`, `tenant_id`, `month_year`, `amount`, `currency`, `proof_file`, `status`, `dealer_notes`, `created_at`, `payment_method`, `months_paid`, `reference`, `lenco_reference`) VALUES
(16, 24, 135, 'April 2026', 1500.00, 'ZMW', NULL, 'approved', 'Initial payment recorded by dealer', '2026-04-06 12:27:48', 'cash', 1, NULL, NULL),
(17, 24, 135, 'May 2026', 1500.00, 'ZMW', 'assets/images/proofs/proof_135_1775480361.png', 'pending', NULL, '2026-04-06 12:59:21', '', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','reviewed','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `saved_properties`
--

CREATE TABLE `saved_properties` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES
(1, 'enable_free_trial', '0'),
(2, 'free_trial_duration', '30');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `dealer_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `start_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `end_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenancy_history`
--

CREATE TABLE `tenancy_history` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `tenant_name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `condition_start` text DEFAULT NULL,
  `condition_end` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenant_ratings`
--

CREATE TABLE `tenant_ratings` (
  `id` int(11) NOT NULL,
  `dealer_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `rental_id` int(11) DEFAULT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reference` varchar(255) NOT NULL,
  `lenco_reference` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'ZMW',
  `status` varchar(50) NOT NULL,
  `message` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `reference`, `lenco_reference`, `amount`, `currency`, `status`, `message`, `payment_method`, `created_at`, `updated_at`) VALUES
(1, 4, 'SUB-1771937278534', '2605514696', 20.00, 'ZMW', 'successful', 'Synced from Lenco: Successful', 'mobile-money', '2026-02-24 12:48:34', '2026-02-26 13:11:46'),
(2, 4, 'SUB-1771938055904', '2605504827', 20.00, 'ZMW', 'successful', 'Synced from Lenco: Successful', 'mobile-money', '2026-02-24 13:01:38', '2026-04-19 14:39:21'),
(3, 145, 'SUB-1776106391472', '2610308182', 20.00, 'ZMW', 'successful', 'Synced from Lenco: Successful', 'mobile-money', '2026-04-13 18:54:13', '2026-04-18 14:17:33');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('user','dealer','admin') DEFAULT 'user',
  `whatsapp_number` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `identity_verified` tinyint(4) DEFAULT 0,
  `is_banned` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verification_token` varchar(255) DEFAULT NULL,
  `token_expiry` timestamp NULL DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `referral_code` varchar(40) DEFAULT NULL,
  `referred_by_user_id` int(11) DEFAULT NULL,
  `referral_registered_at` datetime DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `bank_details` text DEFAULT NULL,
  `verification_doc` varchar(255) DEFAULT NULL,
  `verification_document` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `role`, `whatsapp_number`, `profile_image`, `is_verified`, `identity_verified`, `is_banned`, `created_at`, `verification_token`, `token_expiry`, `google_id`, `referral_code`, `referred_by_user_id`, `referral_registered_at`, `reset_token`, `reset_expires`, `bank_details`, `verification_doc`, `verification_document`) VALUES
(2, 'System Admin', 'admin@luxestay.com', '$2y$12$NkG7PM9z.L2PzQBLbISc1uo7lEjz7gsz8gCPTgCPrLEf2688OBG4y', NULL, 'admin', NULL, NULL, 0, 1, 0, '2026-02-24 07:54:48', NULL, NULL, NULL, NULL, NULL, NULL, '924ddc4524c6fe6103a3078943b130338d9f73813383d747f7abf8ba8affa6c9', '2026-02-24 10:59:54', NULL, NULL, NULL),
(4, 'Lackson Chisala', 'chisalaluckson70@gmail.com', '$2y$12$QMF7st4Z2lIYiJbeRdlxw.kw72I.gM95OJHJYK9heifMcAJosoqDu', '', 'dealer', '', 'assets/images/users/profile_4_1771946943.jpg', 1, 1, 0, '2026-02-24 10:02:09', NULL, NULL, NULL, 'DLREE8764', NULL, NULL, '5ce14d46b34d07a90de3c65f4e023b427086b5c67c442399f2218f77893b56f8', '2026-03-24 08:14:54', '077082884', NULL, NULL),
(38, 'Joseph Kashikite', 'joekashikite@gmail.com', '$2y$12$Rvyrr5Zg95B3Jr2dNtOQKOwxvxP3nfG68U64Q5LjnUl2Q0eDaxzE2', '0973042237', 'dealer', '', NULL, 1, 0, 0, '2026-02-28 20:42:02', '83193dd4a9ae95e22716e4932798bff7a30a3ca91a3bb3e19b07c4e403baa3bc', '2026-03-01 01:45:02', NULL, NULL, NULL, NULL, '022b776cae5d9d5f91c254734353433b1cb3632b2aceb98f3c186cda330b4a4f', '2026-02-28 22:29:32', NULL, NULL, NULL),
(47, 'Nkhata Frank', 'frank.t.r.b59@gmail.com', '$2y$12$viqVTzF4appz3DMkojHjvegIT9K3gpS6j8HADZBtTGjS6ECMLUJxu', '0972232932', 'user', '', NULL, 1, 0, 0, '2026-03-02 11:46:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(49, 'John Doe', 'deleyi5268@ostahie.com', '$2y$12$tA7Z1f9bMxj/wpwHYl/RHeOTPGYwobbOzWyL.3/ScxOS52TyN3G9u', '0970000000', 'dealer', '', NULL, 1, 2, 0, '2026-03-03 04:23:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(50, 'Danny Nkhata', 'dannynkhata6@gmail.com', '$2y$12$n.4OIuEZ0LyEH5N5mK1fv.kvKLTU7hp70jl5WlTjDDbTYvjOVAVt2', '0973795625', 'dealer', '', NULL, 1, 1, 0, '2026-03-05 16:41:52', NULL, NULL, NULL, 'DLR00727D', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(130, 'Muchinga Daka', 'muchingadaka@gmail.com', '$2y$12$sK2QTJINRilC4uKvRj2gde/i3MqqkP/gyz0zysSSK5qUhYhuoCaBa', '0971943272', 'dealer', '0971943272', NULL, 1, 1, 0, '2026-03-31 04:54:45', NULL, NULL, '106370573175463505756', NULL, NULL, NULL, NULL, NULL, NULL, 'assets/images/dealer_docs/dealer_130_69cb5435a9289.jpg', NULL),
(131, 'Aaliyah Jane', 'janeaaliyah84@gmail.com', '$2y$12$b2UoPRPmcJ20h0Em7iUSBurI3TUW6djxNRcjze6Y.EyjOYGyNYUDG', '0766843216', 'user', '', NULL, 1, 0, 0, '2026-04-01 07:08:21', 'e50406cc7cf0686eddfeed946f5ff07d701910777bc5d93decfed0a4c9bb73dc', '2026-04-02 11:08:20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(132, 'Atotwe Kasonso', 'atotwe.kasonso@gmail.com', '$2y$12$1sS0KCH0x8N9L4w6DHEUNec2BV.Q/rLE1zou3ldULFEzQYuEsv2Na', '0977954018', 'dealer', NULL, NULL, 1, 1, 0, '2026-04-01 09:33:41', NULL, NULL, '103976005748749058818', NULL, NULL, NULL, NULL, NULL, NULL, 'assets/images/dealer_docs/dealer_132_69cce69c3a87d.jpg', NULL),
(135, 'Chisala', 'chisalaluckyk5@gmail.com', '$2y$12$NvXd1JY7lw23EvGbftIM0.EPlqkdC5MKnIwJ/o9Fp3Pd1vom79Hg6', '0772125121', 'user', '', NULL, 1, 0, 0, '2026-04-02 19:25:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(139, 'Samantha', 'samanthakungwa@gmail.com', '$2y$12$2F.oJfANra21GNqzIrAxkOSlntY3BHQUm1.6vDlGVNm2t5l6gJ7Bu', '0776680807', 'user', '', NULL, 1, 0, 0, '2026-04-10 06:02:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(141, 'Kangwa Chileshe', 'chileshekangwa50@gmail.com', NULL, '+260773778211', 'user', '', NULL, 1, 0, 0, '2026-04-13 12:51:30', NULL, NULL, '102202239791800174232', NULL, NULL, NULL, '4489a76db0121d671713afec571b47ac3fad94499bc909fc001ba982860182dc', '2026-04-13 14:05:41', NULL, NULL, NULL),
(142, 'Bright Simbaya', 'simbayaedgarbl@gmail.com', '$2y$12$HmL/2GT9cNsrvZ9f3SDHMetIeR3Iy2QWGq/VoSW2byaKckwEHYniS', '0972684898', 'user', '', NULL, 1, 0, 0, '2026-04-13 12:55:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(143, 'Thomson', 'mekelanie1@gmail.com', '$2y$12$mdPvxzFOh.LX/w6YuSvO1..Zis7Ajx9kJLmRbkGBCVKr1rH3UI7sq', '0976515522', 'user', '', NULL, 1, 0, 0, '2026-04-13 13:47:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(144, 'Obyhillen', 'obertyhillen@gmail.com', '$2y$12$TR2.376jEFfHvYIm3ujQ4.FOU4/Zznbp/lzokvxw9Ar2Pik8KjpPu', '0962564017', 'user', '', NULL, 1, 0, 0, '2026-04-13 14:46:48', 'e1c749f132974374c20c06de1f2d82a79dfaf0909a876370306886b52c90254b', '2026-04-14 18:46:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(145, 'Test Dealer', 'chisalaluckson27@gmail.com', '$2y$12$EIY7bYXSwHiPiXWbR20qN.0twEYrFLFalWI1v9vByVHEQUVdJRvsO', '0771355473', 'dealer', '', NULL, 1, 1, 0, '2026-04-13 16:47:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'assets/images/dealer_docs/verify_145_1776101194.png', NULL),
(146, 'Inonge', 'inongekashawa2019@gmail.com', '$2y$12$/xxe2BHcP3gIyT1EocmLjeyQqvea9k7B4lc1kdDLum9PAWRySucFu', '0980059552', 'user', '', NULL, 1, 0, 0, '2026-04-14 10:18:18', '71216bb1803cb03d2620e2c7add8f12bfdf481092318a5c54b4063cd1c5d6d9a', '2026-04-15 14:18:18', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(147, 'Wilfred Jr Khunga', 'wilfredkhunga84@gmail.com', '$2y$12$UtiGf51PlkgrtRSMSL94vO4VT9JR15xkXwdt4cyft1NA0GM87LNDK', '0975977283', 'user', '', NULL, 1, 0, 0, '2026-04-14 10:20:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(148, 'Alison phiri', 'phirialison15@gmail.com', '$2y$12$26FhZozt4DGmiExCWiVTqekH6zBW5AWchOj0Eh0OQvxoQ5ADmquUq', '0771790374', 'user', '', NULL, 1, 0, 0, '2026-04-14 10:22:59', 'c2a45eaa387bc91e16a2d81dac27f97f31dfecdae9f58998c7a28f27598df0f7', '2026-04-15 14:22:59', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(149, 'Patricia nkole Mubanga', 'patriciankolem@gmail.com', '$2y$12$v/YVDIHfWqEQkWypdz.sF.8o/NdHr5c1hbNFGLxSnJNp4tBX5hGV6', '+260978178419', 'dealer', '', NULL, 0, 0, 0, '2026-04-14 10:29:33', 'e3c829523419ab1ed5d2ef4b924dc0e1064559704d9592e1bb58e1a4689dfa92', '2026-04-19 15:19:41', '114929682004245788909', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(150, 'Yumbe Simwinga', 'yumbesimwinga@gmail.com', '$2y$12$qkvkNBjJ50k.xiPw5CV1Gew8iiJZTZA9DkyjvVOKs1Kb31Juq34zG', '0961637516', 'user', '', NULL, 1, 0, 0, '2026-04-14 10:35:15', '1aef6c2df8e139faf208aa13c11f4cf33b29c28b80d4daaf7cff1a601d50927c', '2026-04-15 14:35:14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(151, 'Enia Kapepa', 'eniakapepa1724@gmail.com', '$2y$12$gsQsxW1r3K4xWUiZq1vuouCBAU9t6OrhYf7PhEpPzmej9Ao9g7CH2', '0961686505', 'user', '', NULL, 1, 0, 0, '2026-04-14 10:35:36', '0dbbb4cc2d6170dc7ff040baa0b7e88cc128033743a5dd9915cae3436a5da3d6', '2026-04-15 14:35:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(152, 'Garry kalonje', 'gkalonje09@gmail.com', '$2y$12$zDIBdAZqN723uhe.Nr3h/ulqpuruVbfcQTqxjc9vQ3cVfFd22SL/u', '0976036334', 'user', '', NULL, 1, 0, 0, '2026-04-14 10:36:25', '6257a136f71af3725185264534ba3bf25aadbc8921d6a12f118fcc8ea9ce4c89', '2026-04-15 14:36:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(153, 'Kenny Munene', 'kennymunene220@gmail.com', '$2y$12$fXjRICFCS7UEk8ICUDAHCOqZZfvzzf8J.pIDURh9Bj9vq/BNRhplO', '0779082522', 'user', '', NULL, 1, 0, 0, '2026-04-14 10:38:54', 'a9c6b9dd891441a7ea849490261e016f17d173288e7510bda05dbad79e68e27b', '2026-04-15 14:38:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(154, 'Pauline Mulamfu', 'mulamfupauline@gmail.com', '$2y$12$XbP794imu7RhvGeDwwX2hu1x6VZMYJKSdj6F/xVpSOnvCHaBsQvBy', '0979096915', 'user', '', NULL, 1, 0, 0, '2026-04-14 10:51:33', NULL, NULL, '113858353299596858783', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(155, 'Lukas salunda', 'salundalukas@gmail.com', '$2y$12$qgHGbpoxVvtaxWbNEidC8ORTB9cIbfVVbHL6FcQfif4i64D5AlWzu', '0772782028', 'user', '', NULL, 1, 0, 0, '2026-04-14 11:12:55', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(156, 'Doris', 'dorisjdala20@gmail.com', '$2y$12$TgOBvmWCZX7LHhz7mETJMOYZ0Iz57FNdYdqNFfPjQayWxrTQ9xm9i', '0771817331', 'user', '', NULL, 1, 0, 0, '2026-04-14 11:17:51', '36898c44917497af453607500da27ff34baa34198041abd691c806f4988baba5', '2026-04-15 15:17:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(157, 'Isaac Blackson', 'isaacblackson95@gmail.com', '$2y$12$DU1zFTvWUp8sfm9uHFGJTOSStR9aTuVRZTtXo8GrLGnhGAb2Q6E7i', '0571490050', 'user', '', NULL, 1, 0, 0, '2026-04-14 11:23:54', '38f34b3911c205dd066eaecbdbbcdf039f9b2cd8c354e47b5e7ad8ff1442ecd1', '2026-04-15 15:23:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(158, 'Christopher Kalale', 'christopherkalale6@gmail.com', '$2y$12$DCmnHhZzQcQLUX.0B3K/Z.6uMv2zzS/wmeIUMpncEL8X/QR6x0UF2', '+260971582438', 'user', '', NULL, 1, 0, 0, '2026-04-14 11:58:15', '5f70d26caeb23aa78f297c76745940221c9cd1e1660c62efc509cbd7a7953286', '2026-04-15 15:58:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(159, 'McDonald Moyo', 'mcmambo09@gmail.com', '$2y$12$zAQ7rkmx/3KWqfXAvczp.eKZnVuJez1N.H7aJZhBIdtKqeZFaRwjq', '0572021741', 'user', '', NULL, 1, 0, 0, '2026-04-14 12:06:15', '8b04565d2409d69c2a140ad093e247838a584a8badc15830931eb9a7327f56e8', '2026-04-15 16:06:14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(160, 'Choolwe Darlington Maambo', 'Choolwe619@gmail.com', '$2y$12$gugX/M3XquZMfGZQIYixE.JC8F4o1T/gfKyEzoSQ9ytKy48uaI83.', '0966570341', 'user', '', NULL, 1, 0, 0, '2026-04-14 12:10:19', '738f490a11b2f40fe08e656a0027cfa651f2dc04afc850900b48f127c4330e76', '2026-04-15 16:10:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(161, 'Fredrick Pamofwe', 'chipakopamofwe@gmail.com', '$2y$12$InaFYSj0kr5FtCk9TpuqAufcYbL08ayCtPSLlNxXsKdJxRCdLYO3a', '0971582287', 'user', '', NULL, 1, 0, 0, '2026-04-14 12:22:22', '34d1c257a92a887f9e960630a4a61c1c62cf080edae3ebf38153448aeea80bf4', '2026-04-15 16:22:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(162, 'Susan lungu', 'susanlungu181@gmail.com', '$2y$12$QVUROz8TY93e7sV0wDCp/u4FMgoU8FiO7NNCh6KmfSUhqVjXY8c2G', '+260970685556', 'user', '', NULL, 1, 0, 0, '2026-04-14 12:26:32', 'b405b644334b47fa187744bd2fed278451955ddfc12d3fe7664e637efba9de24', '2026-04-15 16:26:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(163, 'Wanzi Mwanza', 'mwanzawanzi@gmail.com', '$2y$12$dq9flDrSqdXbFx0IJVcSwu/vEPW7OtbUwzt8jpebCGq1B.I.euPLO', '0971109569', 'user', '', NULL, 1, 0, 0, '2026-04-14 12:36:50', '7f3864d2eee87b266f19b93f37333eb518da8c55aa46079db235a1ffdfd5f0b3', '2026-04-15 16:36:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(164, 'Musonda mwila', 'musondamwila8@outlook.com', '$2y$12$y7GCYhRg9olVCMaIescvxO7cm6n0kENLfh3xw4g/k1pNbmq81YyJK', '0971041812', 'user', '', NULL, 1, 0, 0, '2026-04-14 12:47:34', '83d62b7e0fae4e55964f90c7866030b8b7cd0877b95e4997d31b2aac9b48688c', '2026-04-15 16:47:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(165, 'Lupyana emmanuel', 'emmanuellupyana@gmail.com', '$2y$12$hSA7nxq6mALInSsOb5ObYeFzhXfWRLN7FHE/6Te1PyJ4cCgzVq89u', '0973808980', 'user', '', NULL, 1, 0, 0, '2026-04-14 13:26:45', 'd7adf64838f3d625423253a2d3157bee95e9d72f072b52961916a57c7d6093d6', '2026-04-15 17:26:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(166, 'Derrick kangwa', 'derrick.kangwa@gmail.com', '$2y$12$jPFh9KSoPenhn7dl4VHSj.j0AT2bLZ0Aa6J79VwjX4yu6wPCMkynK', '0966800006', 'user', '', NULL, 1, 0, 0, '2026-04-14 13:45:14', '835a002a4a367b1694539a2286ec2f8ca3ae6122a440770c94c482ea81704205', '2026-04-15 17:45:14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(167, 'Dickson Chabala', 'chabalamumba@gmail.com', '$2y$12$fvp.ITub8jMIP.9217SU4OcOOu1ztzt7J4QmOP6otSzc.b5CbIYdG', '0979504411', 'user', '', NULL, 1, 0, 0, '2026-04-14 13:45:40', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(168, 'bertha tembo', 'tembobertha16@gmail.com', '$2y$12$HWatKF9WzM184aiZ69/AQ.6XBa6wpG7K7kOTU8SzCIfJHUbXq0HM6', '972384401', 'user', '', NULL, 1, 0, 0, '2026-04-14 13:45:48', '9cccc2fc35b8ba7de63c7fda3467be84c4db437351e414bc716c07d2d80ef381', '2026-04-15 17:45:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(169, 'Terry Mwale', 'terrencemwale141@gmail.com', '$2y$12$nUmwELAlLsq0quTAjxmeU.vzKWwXZslbBNAjKv05qqUn/T2gbG/Iu', '0973668014', 'user', '', NULL, 1, 0, 0, '2026-04-14 14:04:30', '1b581f3d917fe45b68711dfee1a2f16aa5449d84a8f3395e9024b34b8dc45713', '2026-04-15 18:04:30', NULL, NULL, NULL, NULL, 'b13e71ffb915f57fe384dd1085e2c0217def144e86443e4e6e6d771b242358cc', '2026-04-14 15:07:23', NULL, NULL, NULL),
(170, 'Isaac phiri', 'www.kaizerisaacphirijr@gmail.com', '$2y$12$GUC1Uc6ZV6aH4GqKbDH2NuOxawB/U6KhIhS4ZtlFcNZsQesdM8VLu', '0771628147', 'user', '', NULL, 1, 0, 0, '2026-04-14 14:34:39', '72bae333697d0883784c5e3baa1e7b1fa84414933ef36841e6ef3fd606241ec5', '2026-04-15 18:34:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(171, 'Mary Silishebo', 'marysilishebo04@gmail.com', '$2y$12$KAeyx4eEu2Z1EcBwsa/g7O5ebn/uYlgGkLTJF4CCOYrrL9OQ1DC5O', '0973134230', 'user', '', NULL, 1, 0, 0, '2026-04-14 14:42:46', 'ffcc21709f137bedb9b880534dc911f41bd2a518f0adfb1a43e32308e7949a3b', '2026-04-15 18:42:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(172, 'Harry Nyirongo', 'harrynyirongo7@gmail.com', '$2y$12$IngorRbggUDXWiDbob5IguwdEQXvXHOr9dVCHNzdSEVvQrH7fCmhO', '0972923550', 'user', '', NULL, 1, 0, 0, '2026-04-14 15:00:53', '5f6c388ec521f94c06f7f418a693a65614bb5634584d9b278ab85f3b7903ba28', '2026-04-15 19:00:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(173, 'Alice Nalwamba', 'nalwambaalice1@gmail.com', '$2y$12$4NHpUHjGm/XoU7ERu8Sn.uz3QgoXiwBjO06PIIUTob5ldvRXJHJvy', '0978996202', 'user', '', NULL, 1, 0, 0, '2026-04-14 15:26:08', '2e1e5298090132216e207edbb443fc7711edb5aa697c5a6255f88e918c50f2d2', '2026-04-15 19:26:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(174, 'Elizabeth', 'elizabethmunthali5@gmail.com', '$2y$12$OcVrduXVxMYq3cL9TwsBKOb/VDxwNTqbH2ChU.K7a6/Siwhjxln6i', '0979064640', 'user', '', NULL, 1, 0, 0, '2026-04-14 15:49:08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(175, 'Wendy Mutayomba', 'wendymutayomba3@gmail.com', '$2y$12$YOxOSpylJf54UDJ1Ru9w1eyYFa0xph4Oxhkoh53r21y2g.Pw898rG', '0978050836', 'user', '', NULL, 1, 0, 0, '2026-04-14 16:11:44', 'd46bd7e078736399627571d3e1fb1de048fb9ed148441bdea3b12f0393f8a5b8', '2026-04-15 20:11:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(176, 'Steyn Makondo', 'stanmakondo56@gmail.com', '$2y$12$3zQTfX92gN6y4N.QtI37bOgJRyaWcY4rn4kJBfYc8SU2Y1cthEqoO', '0972989491', 'user', '', NULL, 1, 0, 0, '2026-04-14 16:41:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(177, 'Ruth Daka', 'dakar7190@gmail.com', '$2y$12$ecy6HyUMAZ56CJzT3PgpFu0njlqOZ7oPFLFxWUT7i24rsvZAHHhnq', '0971534039', 'user', '', NULL, 1, 0, 0, '2026-04-14 17:16:16', 'b0e6fbd807282612ff6fae1623161cb3980a0734bed1ca234bafd0d38fb23bc9', '2026-04-15 21:16:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(178, 'bayile miamba', 'bayilemiamba416@gmail.com', NULL, '0974931571', 'user', NULL, NULL, 1, 0, 0, '2026-04-14 17:28:11', NULL, NULL, '102102600848984308557', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(180, 'Mooyer Lumamba', 'mooyerlumamba@gmail.com', '$2y$12$LhyAv7835e5OY9BnIqzIGOf5fkJ5nUGqpzueQP.BPm5f.gXV.4D02', '0962925907', 'user', '', NULL, 1, 0, 0, '2026-04-15 04:04:09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(181, 'Samuel Hamafuwa', 'samuelhamafuwa@gmail.com', '$2y$12$oHl.Pvqx2lAU1qqbhLpjquP6ZECb1/bYZi2X4E1WjB29v9vYjjvUy', '0770 908 962', 'user', '', NULL, 1, 0, 0, '2026-04-15 05:50:21', 'abca047712e76e224d4ad9c6f50db9e357eb61e5cf81ee9a8bb45ea5f62c5ed7', '2026-04-16 09:50:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(182, 'Oscar Jaila', 'oscarjaila44@gmail.com', '$2y$12$ft8ZJr09NiSot1KaTxxYbOLAKPOwGTdFw4AVkeEHbDbE1Z3wWL8YO', '+260770462474', 'user', '', NULL, 1, 0, 0, '2026-04-15 06:50:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(183, 'Sumbwanyambe Munganguta', 'nmunganguta@gmail.com', '$2y$12$UR1AkMmFynuNlzFcgf.FRuMIzD0aH7XxEIDq.VQfuPux4OXhcMXwS', '0973477317', 'user', '', NULL, 1, 0, 0, '2026-04-15 07:13:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(184, 'Komana chalimbana', 'chalimbanakomana@gmail.com', '$2y$12$MR3do0RO20dnHBrbDka8L.rNn/LtOSR76F4sFDAVpOH1hgS3jNIMG', '0968836650', 'user', '', NULL, 1, 0, 0, '2026-04-15 08:08:33', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(185, 'Edson Chilala', 'Edsonchilala7@gmail.com', '$2y$12$Pl3ireqvzXmblndAtgrkRelzvmeCGvTJDd7bcrm5cxEjvA8VAlmX.', '+260975969671', 'user', '', NULL, 1, 0, 0, '2026-04-15 08:21:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(186, 'Mwiinga MULEYA', 'mw11ngamul@gmail.com', '$2y$12$LPA8jOEV//gOZasZNm5BHOtgyWKaZNGxKZTKjX9bWkBQ0rppCQNom', '0977944680', 'user', '', NULL, 1, 0, 0, '2026-04-15 09:38:36', '41f98c6a1b77c10a56cbd253ef8dcae39798c317e1eb66aa635687ef2d1b61e3', '2026-04-16 13:38:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(187, 'Alice Kwalela', 'alicekw04@gmail.com', '$2y$12$dc.60H5ZUNGEnv7/4Yx5hOmCESvkHLTdWXfQ0.9sYBFKnD.H4m6ja', '0971994827', 'user', '', NULL, 1, 0, 0, '2026-04-15 09:40:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(188, 'Precious mubanga', 'preciousmubanga997@gmail.com', '$2y$12$9cqgsiCMwzywBiiMLUkmIusnCR8MrGBV6csDy0qxupONR/Tu2v4qK', '0978077473', 'user', '', NULL, 1, 0, 0, '2026-04-15 10:05:32', 'e5c46889ea2025b018a94211e4543623c3715190e8bc0b3b29634f42926b2379', '2026-04-16 14:05:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(189, 'Kafunya Malichi', 'kmalichi@yahoo.com', '$2y$12$..JEXKyfiiEoUdlXMTyTsuW/seoHSNdHS4g.wNpuJgpVPRAAwFs8a', '0977379170', 'dealer', '', NULL, 0, 0, 0, '2026-04-15 10:19:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(190, 'Tani', 'nyakunzutanaka@gmail.com', '$2y$12$0tITUp2cGj2CTSXM5Tob7ewEK9cE3CnUDlsSZXV82e2/H2nY2qGwa', '0977872533', 'dealer', '', NULL, 0, 0, 0, '2026-04-15 10:44:23', 'e24fd0d210dc01574098a095f7d79901054d4173579cfb55fa9e8989bd5016bf', '2026-04-16 14:44:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(191, 'David chibwela', 'davidchibwela@gmail.com', '$2y$12$ILicWHVfBnL8XFGdWN/T/.fnTqQ3TNhTvT/9Ox8fLb1OdjO3QCy4e', '0963111800', 'dealer', '', NULL, 1, 1, 0, '2026-04-15 10:47:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'assets/images/dealer_docs/verify_191_1776250742.pdf', NULL),
(192, 'Thumbiko Nyirenda', 'thumbikozoba21@gmail.com', '$2y$12$u4HvoWyIYU.ADTasu9IKqO1jiVUSWIr7DMi.Ob6b6psvmdebshe5S', '0979202023', 'user', '', NULL, 1, 0, 0, '2026-04-15 11:11:41', 'e8a1e8dd571e1a3a0673dc8b4762bdf406b2074f40eb72aeb439e8d9809e471c', '2026-04-16 15:11:41', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(193, 'Justin Muma', 'justnmums@gmail.com', '$2y$12$PZkd4zp5b.dEFoF5Q3bpdurP9Qk19O0NmmmEMevw2DPaOxNFViGAq', '0974466680', 'user', '', NULL, 1, 0, 0, '2026-04-15 11:13:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(194, 'Glenmore Mulenga', 'glenmoremulenga740@gmail.com', '$2y$12$2jrGMDGqXqFmJoC0qKjkde0ywK.bu/dc/byASeYVns25usxLfM.3S', '+260978839949', 'user', '', NULL, 1, 0, 0, '2026-04-15 11:22:03', '5b55589bc6f10b42d67578777a328a27410edf770555fe88c269c3510e6b0391', '2026-04-16 15:22:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(195, 'Jacob Kasambala', 'jkasambala37@gmail.com', '$2y$12$mH9D4CE1oY8SEQ3Dh62jJ.HpdtB6EsNWtVgWsgMWLu4kUnmwtyWmG', '0979528901', 'user', '', NULL, 1, 0, 0, '2026-04-15 11:34:26', 'e8e849c58ffab48380e0b3c01e54a23ce0aaccde90366ae3e5ed02c51dc76e99', '2026-04-16 15:34:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(196, 'Malambo Simweemba', 'msimwemba@gmail.com', '$2y$12$scHCTErwiZ0Z9Sf59TusuuaQZdEMpNGqWUzHE4MYMtKNAZij4Q1PO', '0973943892', 'dealer', '', NULL, 0, 0, 0, '2026-04-15 11:41:41', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(197, 'Givatone mumba', 'givatonemumba1994@gmail.com', '$2y$12$0g8yVwlI5juvzQifWCVGz.8Wo3hHE5/aJgZjud2FIL6O2gOBkB/Fi', '0779778007', 'user', '', NULL, 1, 0, 0, '2026-04-15 11:50:31', 'a088736bc16de9a56ad0c147bf0dff9009f3968a6b25322b96d766df823f9fd7', '2026-04-16 15:50:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(198, 'Linda Chikaka Chanda', 'linzychandanora@gmail.com', '$2y$12$tZtosHDHmD1cSqRW8s8gvemWyiJSs6ICwqYVk7P9QP53aENsEymr2', '+260972287396', 'dealer', '', NULL, 0, 0, 0, '2026-04-15 11:53:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(199, 'Mathews Chibuye', 'mathewschibuye94@gmail.com', '$2y$12$4lDkSP9VH1Lw3M5Jdvi8VuGCOSass3qOoMu37ElwsMaCFXi9dl0HO', '0973057199', 'user', '', NULL, 1, 0, 0, '2026-04-15 12:25:16', '6b056f70b6aed9f0b22a982680b9dfc9c811b41dd25a68d1b44462bf068dfa27', '2026-04-16 16:25:16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(200, 'Dre Mutuna', 'edinasfa@gmail.com', '$2y$12$xjZ/pezh5lsB2pgpp9OWtOznTUxhs0zPGup87Px91YkZv6Nt2DjoW', '0980823658', 'user', '', NULL, 1, 0, 0, '2026-04-15 12:59:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(201, 'Moses Ngulube', 'moses01ngulube4@gmail.com', '$2y$12$MTdsuSVWq51gHmbmtF3XSOS/TSXi3UsXHQkmT/O8O1uv/At8qz66m', '0979850713', 'user', '', NULL, 1, 0, 0, '2026-04-15 14:49:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(202, 'VUSI MBEWE', 'vusmuzimbewe@gmail.com', '$2y$12$jk6qow0ON9HQ98f6/BUUheCGGwWzkj0Qtf8QRH/ijN0g8yR2dQ.AS', '0973101562', 'user', '', NULL, 1, 0, 0, '2026-04-15 15:59:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(203, 'Joseph Mbale', 'www.josephmbale19@gmail.com', '$2y$12$RyoM.BSR4cNbciRuRknWROCmI3r09zOUEtm/A4wOmep34LRdQJL4a', '0968707535', 'user', '', NULL, 1, 0, 0, '2026-04-15 16:33:00', 'e2f89b6e603666212c0781335ec2845ef98abb2d389a19e78757758711f35480', '2026-04-16 20:33:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(204, 'Lameck', 'lameck.kasanga@gmail.com', '$2y$12$ZMC/TM0XhMWZuVZc1dDC8OZNlySe698lhGPY2aZinw2L4HLc676HC', '0977814494', 'dealer', '', NULL, 0, 0, 0, '2026-04-15 17:52:09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(205, 'Whyclefmwape1@gmail.com', 'whyclefmwape1@gmail.com', '$2y$12$3WGrobT6GPWVotnxCZz4ceK7hvohAq5HnG.s.5.jkTMnG.tGpuC4q', '0973628607', 'user', '', NULL, 1, 0, 0, '2026-04-15 19:56:22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(206, 'Wyclef', 'wyclefmwape@gmail.com', '$2y$12$Lc.rKwLn6XqKY9BrdPiO0esYEnEZNvBG8n2gFMBUzf6ddOqDmSCyO', '0973628607', 'user', '', NULL, 1, 0, 0, '2026-04-15 20:17:28', '72afd1e3f694b33efa967c86ca4dfa2846f597990ee7c1ca3224ba11c8a71565', '2026-04-17 00:17:28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(207, 'Samantha kabungo', 'Samanthakabungo3@gmail.com', '$2y$12$bjzGZCHugw/lKVUPPG60V.S8luRKuoWDtacaxc2RC1J1weM4cMtzC', '0968871043', 'user', '', NULL, 1, 0, 0, '2026-04-16 00:25:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(208, 'Luwisa sampala', 'sampalaluwisa@gmail.com', '$2y$12$OkYVichxq92qsIqkYYgEKOsyGyPXO9CaAW4BWLcsayaLyHCpZ1/sm', '0777573945', 'user', '', NULL, 1, 0, 0, '2026-04-16 09:59:34', '64e7bb9cd49a120a71fcda2c84afbaf6a1536155988f012af4c1ed2230bb0047', '2026-04-17 13:59:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(209, 'Michael chibwe', 'chibwemichael102@gmail.com', '$2y$12$YRznD0V/Ow1CGqtv1kbsuuynhUEjbXEQMkptVTmif9SsqTH6ba9B2', '0572093744', 'user', '', NULL, 1, 0, 0, '2026-04-16 11:42:02', '3aaf9fb2469cf377a7d54917f8112b33e79408c5a787619e18b3a5c4e2401de0', '2026-04-17 15:42:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(210, 'Kalenga lilian', 'kalengalilian689@gmail.com', '$2y$12$/o7h.Ld9IcsjQEwkY8yhluGcq.3ykMD8VpV.Ugth4gnQo4V.NuCPu', '0573356707', 'user', '', NULL, 1, 0, 0, '2026-04-16 15:26:48', '114c847f73ada6a6cd3314e47a16783a951b63de6ff34c1c5b7b4f1b111a7550', '2026-04-17 19:26:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(211, 'Christopher Phiri', 'christophermphiri52@gmail.com', '$2y$12$luq2v9Z2YPdmz4K.GnEJt.Lsq6kK79LKHRdoHRJPUMpCr6h4R2meq', '0977223540', 'user', '', NULL, 1, 0, 0, '2026-04-16 21:24:51', '7f21892d3bc1a6cd78ff7cc15877e0385663de8b8fbdd06a6e1df1787aa822f9', '2026-04-18 01:24:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(212, 'Judith chimwenye', 'Chimwenyejudith@gmail.com', '$2y$12$cAR40Ona.pudkSy8sKGy0.PTwafl2kp3/1uDmYhOg6Typzj8EaB5S', '0770299272', 'user', '', NULL, 1, 0, 0, '2026-04-17 07:06:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(213, 'Jonathan banda', 'jonathanbanda39@gmail.com', '$2y$12$FKS8pgXDcoIqELpNGPir7.BVNbop2NBOjP4f9qN2pnx8mbiSGUYmi', '0774749961', 'user', '', NULL, 1, 0, 0, '2026-04-17 07:38:11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(214, 'Mwila Kalenga', 'mwilakalenga77@gmail.com', '$2y$12$c7M8nWG2g5hkYWp/vSEgEelqaTxoJU7riwZB30MAZ/V0EtVHcbFQ.', '0573356707', 'user', '', NULL, 1, 0, 0, '2026-04-17 07:49:42', '2b7d340070410cc3e462b6efd1fb6e800594fa6bce12d4a65719e63abeb44f45', '2026-04-18 12:12:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(215, 'Wise Chilonde', 'chilondewise23@gmail.com', '$2y$12$8BRZeQW0qFshqfW4nwmFBe2PYYptNvp8FzrqJkECkuU7V8vH59I0G', '0977883407', 'user', '', NULL, 1, 0, 0, '2026-04-17 08:00:27', '52213d6a0318017f46b1218079675010dd7526591edc524db933ed4c65b19842', '2026-04-18 18:03:01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(216, 'Chiza Simwinga', 'chizasimwinga@gmail.com', '$2y$12$UnCiwynFm/cqXha2G6fNke4A8xzfSgu0nSO.H42QOiF/0L56EYBC2', '0970808645', 'user', '', NULL, 1, 0, 0, '2026-04-17 08:06:26', NULL, NULL, '104924638623191185153', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(217, 'Mwape', 'mwapeyumba60@gmail.com', '$2y$12$CMSo4ANjBOzBt.s263AjEeNFUNtyp2fc1/EHgFo4lP7a7p4WSShf.', '0973628289', 'user', '', NULL, 1, 0, 0, '2026-04-17 09:54:20', '3b73cd86637ce8f47a24f9202b5729320bda5431d07d5481f1c49815f064b547', '2026-04-18 13:54:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(218, 'Alice Daka', 'dakaalice725@gmail.com', NULL, '962200200', 'user', NULL, NULL, 1, 0, 0, '2026-04-17 11:13:35', NULL, NULL, '107346193088454782639', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(219, 'Amos Zulu', 'zuluamos14@gmail.com', '$2y$12$9RoV07/Qj3V/DdGRQrLZGOnAmOiIOGrTd6aoPPq8reHTOkAEDeLIy', '+260971795508', 'user', '', NULL, 1, 0, 0, '2026-04-17 11:48:00', '9946a705306e14b6696c9fd46e64452a426f34f7f38cadd244d710857a8845a1', '2026-04-18 15:48:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(220, 'Fanwell Chaamwe', 'fanwell69@gmail.com', '$2y$12$LsmCdXuN2h61AkwzqVQNG..XYd3WMhfBBsZNDL1vDGcXhoCwyeViq', '+260973561220', 'user', '', NULL, 1, 0, 0, '2026-04-17 13:46:33', '8e641230554cedc99ac1196a14d5e8efbf6988e7bf21fa7ec7f50c6b2f4b6e44', '2026-04-18 17:46:33', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(221, 'Chonde Hampongo', 'hampongochonde@gmail.com', '$2y$12$qZc2pFtzC6j0XAq08zhOGOe/e2tJEfpwp1sGr.cihLlEdrP4YGXmK', '0767528150', 'user', '', NULL, 1, 0, 0, '2026-04-18 14:18:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(223, 'Chisala', 'chisalavudo@gmail.com', '$2y$12$oIIeCpYqC0EdLfhFpV3rxOR3wBTByR.FIDSKDwsSI5vIFsP/XxfUi', '0771354473', 'dealer', '', NULL, 0, 0, 0, '2026-04-19 08:53:16', NULL, NULL, NULL, 'CA7217C', 4, '2026-04-19 05:46:38', NULL, NULL, NULL, NULL, NULL),
(224, 'Benjamin Jambo', 'bjambo42@gmail.com', '$2y$12$k52cLp8oVNHe4KSR9KJ6KeTSqG86cP1uHfy3FnMuEqwTaBc9NXO4.', '0779395606', 'dealer', '', NULL, 1, 1, 0, '2026-04-19 10:52:14', NULL, NULL, NULL, 'B393435', NULL, NULL, NULL, NULL, NULL, 'assets/images/dealer_docs/dealer_224_69e4b5682cbd8.jpeg', NULL),
(225, 'Kelvin', 'kelvinvoicemusic@gmail.com', '$2y$12$tvFLLk0Rug.o.s7V4Bcpd.laXuk47Wwe5icwd3fQoUznQtEnKs.oq', '0761291913', 'dealer', '', NULL, 0, 0, 0, '2026-04-20 10:42:47', '2b5df7e36ee97537a207f34b6e78ec4060ca36e44a219949cc11376152ef566f', '2026-04-21 15:41:56', NULL, 'K82110F', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(226, 'Jonathan mwale', 'jonathanmwale711@gmail.com', '$2y$12$Da8KKDMzMa/EMPrOoPuG/.wCmyy03kQeEq8P3DoWVJW9zH02/Nn.i', '0978889907', 'user', '', NULL, 1, 0, 0, '2026-04-22 07:44:01', '5cd378af9cd42d3de2b9b86f1bedda88fc64471fcb53d89b3e6b33ab1f9d0b7c', '2026-04-23 11:44:01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(227, 'Rhyan Malama', 'rhyanmalama0@gmail.com', '$2y$12$UqHNf4GI10oXs3/79.EomuNsHeXsQulGZSmdxhTKMkIWA3M0a2wY6', '0974708252', 'user', '', NULL, 1, 0, 0, '2026-04-22 08:17:54', '4fbc636dcd216f388836bd7838de91a18c81cf555216d55174a9580cd9c571fb', '2026-04-23 12:17:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(228, 'Nelson Lungu', 'nelsonlungu28@gmail.com', '$2y$12$XeQ1741M5a6vxqysz/xJieA6qIkUSi1WRYG7Mv9aoJMDKjaOM28lm', '0975091199', 'user', '', NULL, 1, 0, 0, '2026-04-22 08:37:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(229, 'Arnold Banda', 'arnoldbandachabdollar@gmail.com', '$2y$12$5v8J0i0mE9.J3SiIzGuICOsyIef7Txa3jHPDzJRyNOW3BG1PofQiW', '0771016212', 'user', '', NULL, 1, 0, 0, '2026-04-22 08:39:52', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(230, 'Twambo Nakuweza', 'twambonakuweza4@gmail.com', '$2y$12$bY3HVE.mJ2yZByY4MTvro.yXgrX3VBpHvCz23AjAf7J19kiJF3j/K', '097849622w', 'user', '', NULL, 1, 0, 0, '2026-04-22 11:29:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(231, 'Jack Chanda', 'www.chanda.jack00@gmail.com', NULL, '0979709972', 'user', '+260979709972', NULL, 1, 0, 0, '2026-04-22 12:57:20', NULL, NULL, '105667404531149960738', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(232, 'Juliet Chola', 'julietchola414@gmail.com', '$2y$12$Gahj7IR3XdxKQUnST2cGDeN5j4CJd92jIaeb6I4TSj.a3a20AKvhO', '0779429462', 'user', '', NULL, 1, 0, 0, '2026-04-22 15:01:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(233, 'lyness mwanza', 'lynessmwanza3@gmail.com', '$2y$12$vw3NdWpf8RkAwKk5O4yn4.zUhA1r0m4XCh7KK3ac4ZdIgZeKoyosm', '0777166431', 'user', '', NULL, 1, 0, 0, '2026-04-22 15:11:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(234, 'Ray Ng&amp;#039;andwe', 'ngandweraymumba@gmail.com', '$2y$12$TLLeWyj7fg4p/aPbTaQGJupgq9Eb2y8h8dGdQCO7uOb2c1cs0fqZ2', '0967643887', 'user', '', NULL, 1, 0, 0, '2026-04-22 15:36:01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(236, 'Cecilia Banda', 'bandacecilia63@gmail.com', '$2y$12$Zh1vRlBnqsLoyolxYuzQR.NxMvgHENt2OUWdumyTlo.UosklXXPCi', '0770024344', 'user', '', NULL, 1, 0, 0, '2026-04-23 12:38:31', '4657aba67c883ae4b529bd9eac73a15b15c28f07a73b87463b9402340db94bb3', '2026-04-24 16:38:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(237, 'Chongo', 'lukachongo2020@gmail.com', '$2y$12$vRINfVlXeFn69SHWiOu6ruDZMv5qj4b2UcKdZSERfGqBsB3Huv/uq', '0970046703', 'user', '', NULL, 1, 0, 0, '2026-04-23 13:37:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(238, 'Innocent Katongo', 'katongoinnocent27@gmail.com', '$2y$12$Qm/PswNQH0mjSbbbTL1pAueKuTTK1vHFMj4cXCRzSCdB5Hot9MV5e', '0971446900', 'user', '', NULL, 1, 0, 0, '2026-04-24 12:39:11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `app_feedbacks_and_requests`
--
ALTER TABLE `app_feedbacks_and_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dealers`
--
ALTER TABLE `dealers`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_prop` (`user_id`,`property_id`);

--
-- Indexes for table `landlord_ratings`
--
ALTER TABLE `landlord_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_landlord_rating_user_property` (`user_id`,`property_id`),
  ADD KEY `idx_landlord_rating_dealer` (`dealer_id`),
  ADD KEY `idx_landlord_rating_property` (`property_id`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification_reads`
--
ALTER TABLE `notification_reads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_read` (`notification_id`,`user_id`);

--
-- Indexes for table `premium_contacts`
--
ALTER TABLE `premium_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dealer_id` (`dealer_id`);

--
-- Indexes for table `property_images`
--
ALTER TABLE `property_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `property_reports`
--
ALTER TABLE `property_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `referral_rewards`
--
ALTER TABLE `referral_rewards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_referrer` (`referrer_user_id`),
  ADD KEY `idx_referred` (`referred_user_id`);

--
-- Indexes for table `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `dealer_id` (`dealer_id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `rent_payments`
--
ALTER TABLE `rent_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rental_id` (`rental_id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `saved_properties`
--
ALTER TABLE `saved_properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dealer_id` (`dealer_id`);

--
-- Indexes for table `tenancy_history`
--
ALTER TABLE `tenancy_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `tenant_ratings`
--
ALTER TABLE `tenant_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_tenant_rating_dealer_tenant` (`dealer_id`,`tenant_id`),
  ADD KEY `idx_tenant_rating_tenant` (`tenant_id`),
  ADD KEY `idx_tenant_rating_dealer` (`dealer_id`),
  ADD KEY `fk_tenant_rating_rental` (`rental_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `referral_code` (`referral_code`),
  ADD KEY `idx_users_referral_code` (`referral_code`),
  ADD KEY `idx_users_referred_by` (`referred_by_user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1214;

--
-- AUTO_INCREMENT for table `app_feedbacks_and_requests`
--
ALTER TABLE `app_feedbacks_and_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `landlord_ratings`
--
ALTER TABLE `landlord_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `notification_reads`
--
ALTER TABLE `notification_reads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `premium_contacts`
--
ALTER TABLE `premium_contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `property_images`
--
ALTER TABLE `property_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `property_reports`
--
ALTER TABLE `property_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `referral_rewards`
--
ALTER TABLE `referral_rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rentals`
--
ALTER TABLE `rentals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `rent_payments`
--
ALTER TABLE `rent_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `saved_properties`
--
ALTER TABLE `saved_properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tenancy_history`
--
ALTER TABLE `tenancy_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tenant_ratings`
--
ALTER TABLE `tenant_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=239;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `dealers`
--
ALTER TABLE `dealers`
  ADD CONSTRAINT `dealers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `landlord_ratings`
--
ALTER TABLE `landlord_ratings`
  ADD CONSTRAINT `fk_landlord_rating_dealer` FOREIGN KEY (`dealer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_landlord_rating_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_landlord_rating_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `premium_contacts`
--
ALTER TABLE `premium_contacts`
  ADD CONSTRAINT `fk_premium_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`dealer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_images`
--
ALTER TABLE `property_images`
  ADD CONSTRAINT `property_images_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `property_reports`
--
ALTER TABLE `property_reports`
  ADD CONSTRAINT `property_reports_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `referral_rewards`
--
ALTER TABLE `referral_rewards`
  ADD CONSTRAINT `fk_referral_rewards_referred` FOREIGN KEY (`referred_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_referral_rewards_referrer` FOREIGN KEY (`referrer_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rentals`
--
ALTER TABLE `rentals`
  ADD CONSTRAINT `rentals_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rentals_ibfk_2` FOREIGN KEY (`dealer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rentals_ibfk_3` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rent_payments`
--
ALTER TABLE `rent_payments`
  ADD CONSTRAINT `rent_payments_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rent_payments_ibfk_2` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `saved_properties`
--
ALTER TABLE `saved_properties`
  ADD CONSTRAINT `saved_properties_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `saved_properties_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`dealer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tenancy_history`
--
ALTER TABLE `tenancy_history`
  ADD CONSTRAINT `tenancy_history_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tenant_ratings`
--
ALTER TABLE `tenant_ratings`
  ADD CONSTRAINT `fk_tenant_rating_dealer` FOREIGN KEY (`dealer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tenant_rating_rental` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_tenant_rating_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_referred_by` FOREIGN KEY (`referred_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
