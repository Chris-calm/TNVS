-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 06, 2025 at 12:58 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tnvs_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `case_records`
--

CREATE TABLE `case_records` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `complainant` varchar(255) NOT NULL,
  `respondent` varchar(255) NOT NULL,
  `status` enum('open','pending','resolved') DEFAULT 'open',
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `case_records`
--

INSERT INTO `case_records` (`id`, `title`, `complainant`, `respondent`, `status`, `details`, `created_at`) VALUES
(3, 'Drunk Driver', 'Almost hit a citizen', 'Passenger', 'open', 'aslfhaslofihwesf', '2025-10-05 05:02:57'),
(4, 'asdsad', 'asdasd', 'asdasdasd', 'open', 'asdasdasasd', '2025-10-05 06:53:47');

-- --------------------------------------------------------

--
-- Table structure for table `contracts`
--

CREATE TABLE `contracts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `company` varchar(100) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `contract_type` varchar(50) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('Active','Pending','Expired') DEFAULT 'Active',
  `picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contracts`
--

INSERT INTO `contracts` (`id`, `name`, `company`, `position`, `department`, `employee_id`, `age`, `contract_type`, `start_date`, `end_date`, `status`, `picture`, `created_at`) VALUES
(7, 'Maple', 'TrailAd.co', 'Leader', 'IT', '2211205', 1, '0', '2025-10-05', '2025-11-06', 'Active', '../uploads/contracts/1759640446_552688220_2265769740587794_6183573623937394502_n.jpg', '2025-10-05 05:00:46'),
(8, 'Maple', 'TrailAd.co', 'Leader', 'IT', '2211205', 1, '0', '2025-10-25', '2025-10-29', 'Active', '../uploads/contracts/1759647260_ac7ccdc4-f5a8-4b63-bad9-20b2dffeaad4.jpg', '2025-10-05 06:54:20');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(500) NOT NULL,
  `uploaded_by` varchar(100) DEFAULT 'Admin',
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `title`, `filename`, `filepath`, `uploaded_by`, `uploaded_at`, `is_archived`) VALUES
(21, 'PMact', '1759640390_PMactivity.docx', 'uploads/1759640390_PMactivity.docx', 'Admin', '2025-10-05 04:59:50', 0),
(22, 'RCA', '1759700362_Root-Cause-Analysis.docx', 'uploads/1759700362_Root-Cause-Analysis.docx', 'Admin', '2025-10-05 21:39:22', 0);

-- --------------------------------------------------------

--
-- Table structure for table `document_actions`
--

CREATE TABLE `document_actions` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `user_role` varchar(100) NOT NULL,
  `action_type` enum('Edit','Delete','View','Download','Archive') NOT NULL,
  `action_description` text DEFAULT NULL,
  `performed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_actions`
--

INSERT INTO `document_actions` (`id`, `document_id`, `user_role`, `action_type`, `action_description`, `performed_at`, `ip_address`, `user_agent`) VALUES
(13, 21, 'Admin', 'Edit', 'Permissions updated for document ID: 21', '2025-10-05 05:00:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0'),
(14, 21, 'Admin', 'Edit', 'Permissions updated for document ID: 21', '2025-10-05 05:00:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0'),
(15, 22, 'Admin', 'Edit', 'Permissions updated for document ID: 22', '2025-10-05 21:39:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0'),
(16, 22, 'Admin', 'Edit', 'Permissions updated for document ID: 22', '2025-10-05 22:04:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0'),
(17, 22, 'Admin', 'Edit', 'Permissions updated for document ID: 22', '2025-10-05 22:07:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0'),
(18, 22, 'Admin', 'Edit', 'Permissions updated for document ID: 22', '2025-10-05 22:07:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0'),
(19, 22, 'Admin', 'Edit', 'Permissions updated for document ID: 22', '2025-10-05 22:12:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0');

-- --------------------------------------------------------

--
-- Table structure for table `document_permissions`
--

CREATE TABLE `document_permissions` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `user_role` varchar(100) NOT NULL,
  `permission_type` enum('View','Download','Edit','Archive') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_permissions`
--

INSERT INTO `document_permissions` (`id`, `document_id`, `user_role`, `permission_type`, `created_at`) VALUES
(315, 21, 'Admin', 'View', '2025-10-05 05:00:22'),
(340, 22, 'Admin', 'View', '2025-10-05 22:12:32');

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `facility_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL,
  `location` varchar(150) NOT NULL,
  `status` enum('Pending','Approved','Rejected','Under Maintenance') DEFAULT 'Pending',
  `picture` varchar(255) DEFAULT NULL,
  `available_date` date NOT NULL,
  `available_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`facility_id`, `name`, `capacity`, `location`, `status`, `picture`, `available_date`, `available_time`, `created_at`) VALUES
(27, 'Maple', 1, '2ND FLOOR MV', 'Approved', '0c47b381-65f4-40bd-85cf-55a29965f738.jpg', '2025-10-17', '08:58:00', '2025-10-05 04:58:44'),
(28, 'Maple', 1, 'Top & Bottom zingzing', 'Approved', '0c47b381-65f4-40bd-85cf-55a29965f738.jpg', '2025-10-14', '10:53:00', '2025-10-05 06:53:28'),
(29, 'TEST FACILITY', 1, 'Top & Bottom zingzing', 'Approved', '552688220_2265769740587794_6183573623937394502_n.jpg', '2025-10-06', '23:35:00', '2025-10-05 19:35:35'),
(30, 'Coco Martin', 1, 'undefined', 'Pending', 'ac7ccdc4-f5a8-4b63-bad9-20b2dffeaad4.jpg', '2025-10-31', '23:44:00', '2025-10-05 19:45:01'),
(31, 'asdsad', 12, '1233421', 'Rejected', 'Minimalist Login Interface Design.png', '2025-10-14', '23:56:00', '2025-10-05 19:56:46'),
(32, 'TEST FACILITY', 50, '2ND FLOOR MV', 'Under Maintenance', '552688220_2265769740587794_6183573623937394502_n.jpg', '2025-10-16', '16:34:00', '2025-10-05 21:34:05');

-- --------------------------------------------------------

--
-- Table structure for table `policies`
--

CREATE TABLE `policies` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `role` enum('administrative','student','admin-only') NOT NULL,
  `short_desc` text DEFAULT NULL,
  `full_policy` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `policies`
--

INSERT INTO `policies` (`id`, `title`, `role`, `short_desc`, `full_policy`, `created_at`) VALUES
(3, 'Safe Conduct for Admin', 'admin-only', 'A policy for safe driving', 'A policy for safe admins', '2025-10-05 05:01:26');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `reserved_by` varchar(100) NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','employee') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
-- Note: Passwords are hashed using PASSWORD_DEFAULT (bcrypt)
-- Credentials: superadmin/super123, admin/admin123, employee/employee123
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin', '2025-10-05 22:53:26'),
(2, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2025-10-05 22:53:26'),
(3, 'employee', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'employee', '2025-10-05 22:53:26');

-- --------------------------------------------------------

--
-- Table structure for table `visitors`
--

CREATE TABLE `visitors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact` varchar(20) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `visit_date` date NOT NULL,
  `visit_time` time NOT NULL,
  `person_to_visit` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `checkin` datetime DEFAULT NULL,
  `checkout` datetime DEFAULT NULL,
  `status` enum('Checked-in','Checked-out') DEFAULT 'Checked-in',
  `picture_path` varchar(255) DEFAULT NULL,
  `request_status` enum('pending','approved','denied') DEFAULT 'pending',
  `pass_id` varchar(50) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `denied_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visitors`
--

INSERT INTO `visitors` (`id`, `name`, `contact`, `purpose`, `visit_date`, `visit_time`, `person_to_visit`, `created_at`, `checkin`, `checkout`, `status`, `picture_path`, `request_status`, `pass_id`, `approved_at`, `denied_at`) VALUES
(6, 'Maple', '0994591242', 'makakakita ng dreamybull', '2025-10-09', '09:03:00', 'Ako syempre ako may ari eh', '2025-10-05 05:03:43', NULL, '2025-10-05 07:03:52', 'Checked-out', 'uploads/visitor_pictures/68e1fc2f27fe8_552688220_2265769740587794_6183573623937394502_n.jpg', 'pending', NULL, NULL, NULL),
(7, 'Maple', '0994591242', 'makakakita ng dreamybull', '2025-10-18', '10:54:00', 'sdada', '2025-10-05 06:54:42', NULL, NULL, 'Checked-in', 'uploads/visitor_pictures/68e216324e5c1_552688220_2265769740587794_6183573623937394502_n.jpg', 'pending', NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `case_records`
--
ALTER TABLE `case_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `document_actions`
--
ALTER TABLE `document_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_document_id` (`document_id`),
  ADD KEY `idx_action_type` (`action_type`),
  ADD KEY `idx_performed_at` (`performed_at`);

--
-- Indexes for table `document_permissions`
--
ALTER TABLE `document_permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_id` (`document_id`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`facility_id`);

--
-- Indexes for table `policies`
--
ALTER TABLE `policies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `facility_id` (`facility_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `visitors`
--
ALTER TABLE `visitors`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `case_records`
--
ALTER TABLE `case_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `document_actions`
--
ALTER TABLE `document_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `document_permissions`
--
ALTER TABLE `document_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=341;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `facility_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `policies`
--
ALTER TABLE `policies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `visitors`
--
ALTER TABLE `visitors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `document_permissions`
--
ALTER TABLE `document_permissions`
  ADD CONSTRAINT `document_permissions_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`facility_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
