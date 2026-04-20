-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 02, 2026 at 12:29 PM
-- Server version: 11.4.9-MariaDB-cll-lve
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
(30, 2, 'admin', 'login', 'User logged in successfully', '165.58.129.192', '2026-03-02 16:51:11');

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
  `subscription_expiry` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dealers`
--

INSERT INTO `dealers` (`user_id`, `company_name`, `office_address`, `bio`, `subscription_status`, `subscription_expiry`) VALUES
(4, NULL, NULL, NULL, 'active', '2026-03-28 04:00:00'),
(30, NULL, NULL, NULL, 'active', '2026-03-27 17:52:49'),
(38, NULL, NULL, NULL, 'active', '2026-03-31 00:42:02');

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
(5, 6, 4, 'ZAP', 'foo-bar@example.com', 'ZAP', 'I&#039;m interested in this property.', '2026-02-26 06:08:48');

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
  `property_type` enum('house','apartment','flat','boarding_house') NOT NULL,
  `listing_purpose` enum('rent','sale') NOT NULL DEFAULT 'rent',
  `location` varchar(255) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `status` enum('available','rented') DEFAULT 'available',
  `amenities` text DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_boosted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`id`, `dealer_id`, `title`, `description`, `price`, `currency`, `bedrooms`, `bathrooms`, `rooms`, `size_sqm`, `property_type`, `listing_purpose`, `location`, `city`, `country`, `latitude`, `longitude`, `status`, `amenities`, `video_url`, `views`, `is_featured`, `is_boosted`, `created_at`) VALUES
(6, 4, 'Chunga hill', 'Very neat ', 2500.00, 'ZMW', 3, 2, 6, 8.00, 'flat', 'rent', '257', 'Lusaka', 'Zambia', -15.37027407, 28.29425812, 'available', '', '', 118, 1, 0, '2026-02-24 12:38:43'),
(7, 4, 'Chalala ', 'Very neat', 5800.00, 'ZMW', 4, 3, 10, 80.00, 'house', 'rent', 'Chalala mall', 'Chipata', 'Zambia', -15.46459998, 28.34266663, 'available', 'Wifi, solar', '', 177, 1, 0, '2026-02-24 12:40:09'),
(9, 4, 'Neat house ', 'This house it&#039;s very neat.\r\nHas everything you need ', 3000.00, 'ZMW', 4, 2, 8, 100.00, 'house', 'rent', 'Chalala mall', 'Lusaka', 'Zambia', NULL, NULL, 'available', '', '', 2, 1, 0, '2026-03-02 15:43:35');

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
(27, 9, 'assets/images/properties/prop_9_69a5b0362987b.jpg', 0, '2026-03-02 15:43:50', 'image');

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
-- Table structure for table `rentals`
--

CREATE TABLE `rentals` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
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

INSERT INTO `rentals` (`id`, `property_id`, `dealer_id`, `tenant_id`, `status`, `start_date`, `end_date`, `rent_amount`, `currency`, `created_at`, `updated_at`, `payment_reference`) VALUES
(3, 7, 4, 37, 'active', '2026-02-27', NULL, 100.00, 'ZMW', '2026-02-27 13:33:20', '2026-02-27 13:33:20', '1542344937928684');

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
  `payment_method` enum('cash','bank_transfer','mobile_money') DEFAULT 'bank_transfer'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
(1, 'enable_free_trial', '1'),
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
(2, 4, 'SUB-1771938055904', '2605504827', 20.00, 'ZMW', 'successful', 'Synced from Lenco: Successful', 'mobile-money', '2026-02-24 13:01:38', '2026-02-26 13:12:13');

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
  `is_banned` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verification_token` varchar(255) DEFAULT NULL,
  `token_expiry` timestamp NULL DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `bank_details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `role`, `whatsapp_number`, `profile_image`, `is_verified`, `is_banned`, `created_at`, `verification_token`, `token_expiry`, `google_id`, `reset_token`, `reset_expires`, `bank_details`) VALUES
(2, 'System Admin', 'admin@luxestay.com', '$2y$12$NkG7PM9z.L2PzQBLbISc1uo7lEjz7gsz8gCPTgCPrLEf2688OBG4y', NULL, 'admin', NULL, NULL, 0, 0, '2026-02-24 07:54:48', NULL, NULL, NULL, '924ddc4524c6fe6103a3078943b130338d9f73813383d747f7abf8ba8affa6c9', '2026-02-24 10:59:54', NULL),
(4, 'Lackson Chisala', 'chisalaluckson70@gmail.com', '$2y$12$QMF7st4Z2lIYiJbeRdlxw.kw72I.gM95OJHJYK9heifMcAJosoqDu', '0771355473', 'dealer', '0771355473', 'assets/images/users/profile_4_1771946943.jpg', 1, 0, '2026-02-24 10:02:09', NULL, NULL, NULL, 'd076d7b3a3af41b28cd70917a5cbe2460c6b56abc54bd0e685193de621bf8cce', '2026-02-28 22:37:23', '077082884'),
(30, 'Lackson Chisala', 'lucksonchisala17@gmail.com', '$2y$12$Gq.uAVlseFaV3xPce.umaOBFVQuRqVUqsi9h4GJ7MrEHKI9fQz.vG', '0771355473', 'dealer', '', NULL, 1, 0, '2026-02-25 13:52:49', NULL, NULL, '100693985233618575636', NULL, NULL, NULL),
(37, 'Luckson Chisala', 'chisalaluckson27@gmail.com', NULL, '+2600770812506', 'user', '', 'assets/images/users/profile_37_1772198479.png', 1, 0, '2026-02-27 13:09:27', NULL, NULL, '103442805327043265733', NULL, NULL, NULL),
(38, 'Joseph Kashikite', 'joekashikite@gmail.com', '$2y$12$Rvyrr5Zg95B3Jr2dNtOQKOwxvxP3nfG68U64Q5LjnUl2Q0eDaxzE2', '0973042237', 'dealer', '', NULL, 1, 0, '2026-02-28 20:42:02', '83193dd4a9ae95e22716e4932798bff7a30a3ca91a3bb3e19b07c4e403baa3bc', '2026-03-01 01:45:02', NULL, '022b776cae5d9d5f91c254734353433b1cb3632b2aceb98f3c186cda330b4a4f', '2026-02-28 22:29:32', NULL),
(47, 'Nkhata Frank', 'frank.t.r.b59@gmail.com', '$2y$12$viqVTzF4appz3DMkojHjvegIT9K3gpS6j8HADZBtTGjS6ECMLUJxu', '0972232932', 'user', '', NULL, 1, 0, '2026-03-02 11:46:37', NULL, NULL, NULL, NULL, NULL, NULL);

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
-- Indexes for table `dealers`
--
ALTER TABLE `dealers`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`);

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
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `property_images`
--
ALTER TABLE `property_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `property_reports`
--
ALTER TABLE `property_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `rentals`
--
ALTER TABLE `rentals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `rent_payments`
--
ALTER TABLE `rent_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `saved_properties`
--
ALTER TABLE `saved_properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

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
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
