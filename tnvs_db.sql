-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 14, 2025 at 05:30 PM
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
  `status` enum('open','pending','resolved','Open','In Progress','Closed') DEFAULT 'open',
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `case_records`
--

INSERT INTO `case_records` (`id`, `title`, `complainant`, `respondent`, `status`, `details`, `created_at`) VALUES
(5, 'Traffic Violation Case', 'John Doe', 'Jane Smith', 'Open', 'Speeding violation on Main Street', '2025-10-09 09:49:19'),
(6, 'Driver Complaint', 'Passenger A', 'Driver B', 'Open', 'Rude behavior and unsafe driving', '2025-10-09 09:56:29'),
(7, 'Vehicle Maintenance Issue', 'TNVS Admin', 'Fleet Manager', 'In Progress', 'Regular maintenance check required', '2025-10-14 08:00:00');

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
(7, 'John Smith', 'TNVS Corp', 'Driver', 'Transportation', 'EMP001', 35, 'Full-time', '2025-01-01', '2025-12-31', 'Active', '../uploads/contracts/1759640446_profile1.jpg', '2025-10-05 05:00:46'),
(8, 'Maria Garcia', 'TNVS Corp', 'Dispatcher', 'Operations', 'EMP002', 28, 'Full-time', '2025-02-01', '2025-12-31', 'Active', '../uploads/contracts/1759647260_profile2.jpg', '2025-10-05 06:54:20'),
(9, 'Robert Johnson', 'TNVS Corp', 'Mechanic', 'Maintenance', 'EMP003', 42, 'Part-time', '2025-03-01', '2025-11-30', 'Active', '../uploads/contracts/1759650000_profile3.jpg', '2025-10-14 08:30:00');

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
(21, 'TNVS Operations Manual', '1759640390_operations_manual.pdf', 'uploads/1759640390_operations_manual.pdf', 'admin', '2025-10-05 04:59:50', 0),
(22, 'Safety Guidelines', '1759700362_safety_guidelines.docx', 'uploads/1759700362_safety_guidelines.docx', 'admin', '2025-10-05 21:39:22', 0),
(23, 'Driver Training Materials', '1760003607_driver_training.pdf', '../uploads/1760003607_driver_training.pdf', 'admin', '2025-10-09 09:53:27', 0),
(24, 'Vehicle Inspection Checklist', '1760169973_inspection_checklist.xlsx', '../uploads/1760169973_inspection_checklist.xlsx', 'superadmin', '2025-10-11 08:06:13', 0),
(25, 'Emergency Procedures', '1760200000_emergency_procedures.pdf', '../uploads/1760200000_emergency_procedures.pdf', 'admin', '2025-10-14 09:00:00', 0);

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
(13, 21, 'Admin', 'Edit', 'Permissions updated for document ID: 21', '2025-10-05 05:00:04', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(14, 21, 'Admin', 'View', 'Document viewed by admin', '2025-10-05 05:00:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(15, 22, 'Admin', 'Download', 'Document downloaded by admin', '2025-10-05 21:39:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(16, 23, 'Employee', 'View', 'Training materials accessed', '2025-10-09 10:15:22', '192.168.1.100', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'),
(17, 24, 'Admin', 'Edit', 'Checklist updated with new requirements', '2025-10-11 08:30:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

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
(392, 21, 'Admin', 'View', '2025-10-11 08:56:33'),
(393, 21, 'Admin', 'Download', '2025-10-11 08:56:33'),
(394, 21, 'Admin', 'Edit', '2025-10-11 08:56:33'),
(395, 22, 'Admin', 'View', '2025-10-11 08:57:00'),
(396, 22, 'Employee', 'View', '2025-10-11 08:57:00'),
(397, 23, 'Admin', 'View', '2025-10-11 08:57:15'),
(398, 23, 'Admin', 'Download', '2025-10-11 08:57:15'),
(399, 23, 'Employee', 'View', '2025-10-11 08:57:15'),
(400, 24, 'Admin', 'View', '2025-10-11 08:57:30'),
(401, 24, 'Admin', 'Edit', '2025-10-11 08:57:30');

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `facility_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL,
  `location` varchar(150) NOT NULL,
  `status` enum('Pending','Approved','Rejected','Under Maintenance','Maintenance') DEFAULT 'Pending',
  `picture` varchar(255) DEFAULT NULL,
  `available_date` date NOT NULL,
  `available_time` time NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`facility_id`, `name`, `capacity`, `location`, `status`, `picture`, `available_date`, `available_time`, `created_at`) VALUES
(35, 'Conference Room A', 20, '2nd Floor Main Building', 'Approved', 'conference_room_a.jpg', '2025-10-15', '08:00:00', '2025-10-14 00:48:51'),
(36, 'Training Center', 50, '1st Floor Training Wing', 'Approved', 'training_center.jpg', '2025-10-16', '09:00:00', '2025-10-14 00:50:50'),
(37, 'Vehicle Maintenance Bay', 5, 'Ground Floor Garage', 'Under Maintenance', 'maintenance_bay.jpg', '2025-10-17', '07:00:00', '2025-10-14 00:54:26'),
(38, 'Driver Rest Area', 15, '2nd Floor East Wing', 'Pending', 'rest_area.jpg', '2025-10-18', '24:00:00', '2025-10-14 09:00:00'),
(39, 'Dispatch Center', 10, '1st Floor Operations', 'Approved', 'dispatch_center.jpg', '2025-10-19', '06:00:00', '2025-10-14 09:15:00');

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
(3, 'Safe Driving Policy', 'administrative', 'Guidelines for safe driving practices', 'All TNVS drivers must adhere to traffic laws, maintain safe following distances, and undergo regular safety training.', '2025-10-05 05:01:26'),
(4, 'Vehicle Maintenance Standards', 'administrative', 'Regular maintenance requirements for all vehicles', 'Vehicles must undergo daily inspections, weekly maintenance checks, and monthly comprehensive evaluations.', '2025-10-07 02:31:30'),
(5, 'Emergency Response Procedures', 'admin-only', 'Protocols for handling emergency situations', 'Detailed procedures for accidents, breakdowns, and other emergency situations requiring immediate response.', '2025-10-14 09:30:00'),
(6, 'Customer Service Guidelines', 'administrative', 'Standards for passenger interaction', 'Professional conduct, courtesy, and service quality standards for all customer-facing staff.', '2025-10-14 09:45:00');

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

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`reservation_id`, `facility_id`, `reserved_by`, `reservation_date`, `reservation_time`, `status`, `created_at`) VALUES
(1, 35, 'John Smith', '2025-10-20', '10:00:00', 'Approved', '2025-10-14 10:00:00'),
(2, 36, 'Maria Garcia', '2025-10-21', '14:00:00', 'Pending', '2025-10-14 10:15:00'),
(3, 39, 'Robert Johnson', '2025-10-22', '08:00:00', 'Approved', '2025-10-14 10:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','employee') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`, `email`, `profile_picture`) VALUES
(1, 'superadmin', '$2y$10$zD0.C64gaSWx4tYKqnwujeLu9c2JQllb7FEYiKZPN4vmR.xyiKpLy', 'super_admin', '2025-10-05 23:51:23', 'admin@tnvs.com', NULL),
(2, 'admin', '$2y$10$GF5KxjPdFxsIdy7gFmpgvuPICaMZMv8rndDOxkN6ndMdrT53FPpfO', 'admin', '2025-10-05 23:51:23', 'manager@tnvs.com', 'profile_2_1760008084.jpg'),
(3, 'employee', '$2y$10$ybgIDkNQSxBi/NrDGZBhy.t.gngwvcJqUvZz987Q1byl5SoLR1BSO', 'employee', '2025-10-05 23:51:24', 'staff@tnvs.com', 'profile_3_1760007799.jpg'),
(4, 'dispatcher', '$2y$10$VM6GSsgtGPUh3lQjTjlI6uX5G5YpBWFTpxKlVZcB5KLjNRFgFt142', 'employee', '2025-10-14 10:45:00', 'dispatch@tnvs.com', NULL),
(5, 'maintenance', '$2y$10$ABC123DefGhiJklMnoPqRsTuVwXyZ0123456789AbCdEfGhIjKlMn', 'employee', '2025-10-14 11:00:00', 'maintenance@tnvs.com', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_otps`
--

CREATE TABLE `user_otps` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `otp_code` varchar(10) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `is_used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_otps`
--

INSERT INTO `user_otps` (`id`, `user_id`, `username`, `otp_code`, `email`, `created_at`, `expires_at`, `is_used`) VALUES
(2, 1, 'superadmin', '708918', 'admin@tnvs.com', '2025-10-09 11:37:47', '2025-10-09 13:42:47', 0),
(3, 1, 'superadmin', '480209', 'admin@tnvs.com', '2025-10-09 11:39:45', '2025-10-09 13:44:45', 1),
(4, 1, 'superadmin', '251790', 'admin@tnvs.com', '2025-10-09 11:43:47', '2025-10-09 13:48:47', 0),
(5, 1, 'superadmin', '459804', 'admin@tnvs.com', '2025-10-09 11:49:21', '2025-10-09 13:54:21', 1),
(6, 2, 'admin', '617948', 'manager@tnvs.com', '2025-10-09 11:50:22', '2025-10-09 13:55:22', 1),
(7, 1, 'superadmin', '170023', 'admin@tnvs.com', '2025-10-09 12:02:37', '2025-10-09 14:07:37', 1),
(8, 1, 'superadmin', '253563', 'admin@tnvs.com', '2025-10-11 08:44:18', '2025-10-11 10:49:18', 1),
(9, 2, 'admin', '299007', 'manager@tnvs.com', '2025-10-11 08:56:04', '2025-10-11 11:01:04', 1),
(10, 1, 'superadmin', '151207', 'admin@tnvs.com', '2025-10-11 08:56:48', '2025-10-11 11:01:48', 1),
(11, 1, 'superadmin', '474533', 'admin@tnvs.com', '2025-10-14 00:23:14', '2025-10-14 02:28:14', 1);

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
  `status` enum('Checked-in','Checked-out','Pre-Registered','Visiting','Visit Complete') DEFAULT 'Checked-in',
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
(7, 'Michael Brown', '09123456789', 'Business Meeting', '2025-10-18', '10:30:00', 'John Smith', '2025-10-05 06:54:42', '2025-10-06 18:46:22', '2025-10-07 04:39:58', 'Visit Complete', 'uploads/visitor_pictures/68e216324e5c1_visitor1.jpg', 'approved', 'PASS001', '2025-10-06 18:45:00', NULL),
(8, 'Sarah Wilson', '09987654321', 'Training Session', '2025-10-19', '09:00:00', 'Maria Garcia', '2025-10-06 13:32:19', NULL, NULL, 'Pre-Registered', 'uploads/visitor_pictures/68e3c4e360555_visitor2.jpg', 'pending', NULL, NULL, NULL),
(9, 'David Lee', '09555123456', 'Vehicle Inspection', '2025-10-20', '14:00:00', 'Robert Johnson', '2025-10-14 11:30:00', NULL, NULL, 'Pre-Registered', 'uploads/visitor_pictures/visitor3.jpg', 'approved', 'PASS002', '2025-10-14 11:45:00', NULL),
(10, 'Lisa Chen', '09444987654', 'System Audit', '2025-10-21', '11:00:00', 'Admin Department', '2025-10-14 12:00:00', NULL, NULL, 'Pre-Registered', NULL, 'pending', NULL, NULL, NULL);

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
-- Indexes for table `user_otps`
--
ALTER TABLE `user_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_otp_code` (`otp_code`),
  ADD KEY `idx_expires_at` (`expires_at`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `document_actions`
--
ALTER TABLE `document_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `document_permissions`
--
ALTER TABLE `document_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=402;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `facility_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `policies`
--
ALTER TABLE `policies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_otps`
--
ALTER TABLE `user_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `visitors`
--
ALTER TABLE `visitors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `document_actions`
--
ALTER TABLE `document_actions`
  ADD CONSTRAINT `document_actions_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE;

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

--
-- Constraints for table `user_otps`
--
ALTER TABLE `user_otps`
  ADD CONSTRAINT `user_otps_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;