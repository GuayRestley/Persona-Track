-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2025 at 06:01 PM
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
-- Database: `personatrack`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `account_id` int(10) NOT NULL,
  `username` varchar(15) NOT NULL,
  `password_hash` varchar(100) NOT NULL,
  `role` enum('admin','CAPTAIN','SECRETARY','KAGAWAD','STAFF','RESIDENT') NOT NULL,
  `status` enum('Active','Inactive','Suspended') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`account_id`, `username`, `password_hash`, `role`, `status`, `created_at`, `updated_at`, `last_login`) VALUES
(2, 'admin', '$2y$10$IRyFXu5.zb/ilLfspuZwLeS2VbjztYXTvfbQr3cDC.Gk8BdnSIETW', 'admin', 'Active', '2025-11-29 04:12:32', '2025-11-26 14:06:06', '2025-11-29 04:12:32'),
(3, 'admin101', '$2y$10$FVd6KPajNy6QfSOr4fV7F.eMAwA/8z6mJxP6oB/N5Ve2sdzKrbvu6', 'admin', 'Active', '2025-11-29 06:50:42', '2025-11-29 04:13:42', '2025-11-29 06:50:42'),
(4, 'captain', '$2y$10$/GYuc03MHvCDea9RPtH8VuefRjKMYHottPsCG4FRIs5qnruxUYBeW', 'CAPTAIN', 'Active', '2025-11-29 04:14:28', '2025-11-29 04:14:28', '2025-11-29 04:14:28');

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(10) NOT NULL,
  `account_id` int(10) NOT NULL,
  `action_type` enum('Add','Update','Delete','View','Login','Logout') NOT NULL,
  `action_description` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `remarks` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `account_id`, `action_type`, `action_description`, `created_at`, `remarks`) VALUES
(1, 3, 'Add', 'add resident.', '2025-12-02 17:00:45', '');

-- --------------------------------------------------------

--
-- Table structure for table `residents`
--

CREATE TABLE `residents` (
  `resident_id` int(11) NOT NULL,
  `first_name` varchar(18) NOT NULL,
  `middle_name` varchar(15) NOT NULL,
  `last_name` varchar(15) NOT NULL,
  `suffix` varchar(3) NOT NULL,
  `birth_date` date NOT NULL,
  `gender` enum('M','F','O') NOT NULL,
  `civil_status` enum('Single','Married','Widowed','Separated') NOT NULL,
  `nationality` varchar(20) NOT NULL,
  `religion` varchar(20) NOT NULL,
  `occupation` varchar(20) NOT NULL,
  `educational_attainment` varchar(30) NOT NULL,
  `social_status` enum('Employed','Unemployed','Student','Senior Citizen','PWD') NOT NULL,
  `contact_no` varchar(11) NOT NULL,
  `email` varchar(20) DEFAULT NULL,
  `house_no` varchar(10) NOT NULL,
  `street` varchar(20) NOT NULL,
  `purok` varchar(10) NOT NULL,
  `barangay` varchar(15) NOT NULL,
  `city` varchar(20) NOT NULL,
  `province` varchar(20) NOT NULL,
  `zipcode` char(4) NOT NULL,
  `residency_status` enum('Active','Deceased','Moved Out') NOT NULL,
  `date_registered` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `registered_by` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `residents`
--

INSERT INTO `residents` (`resident_id`, `first_name`, `middle_name`, `last_name`, `suffix`, `birth_date`, `gender`, `civil_status`, `nationality`, `religion`, `occupation`, `educational_attainment`, `social_status`, `contact_no`, `email`, `house_no`, `street`, `purok`, `barangay`, `city`, `province`, `zipcode`, `residency_status`, `date_registered`, `updated_at`, `registered_by`) VALUES
(1, 'Lebron', 'Bryant', 'Jordan', '', '2002-02-13', 'M', 'Single', 'Filipino', 'Evangelical', 'Basketball Player', 'College Graduate', 'Employed', '09561239585', '', '244h', 'Quezon st.', '3', 'Aurora', 'Baguio City', 'Benguey', '2600', 'Active', '2025-11-30 23:27:50', '2025-11-30 23:29:22', 3),
(2, 'Fraeze', 'Rioette', 'Pallay', '', '2003-12-25', 'M', 'Single', 'Filipino', 'Catholic', 'Student', 'Senior High School Graduate', 'Student', '09867436737', 'roiette@gmai.com', '65', 'tranco', '4', 'Bayan', 'Baguio City', 'Benguet', '2600', 'Active', '2025-12-01 18:57:20', '2025-12-01 18:57:20', 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`account_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `residents`
--
ALTER TABLE `residents`
  ADD PRIMARY KEY (`resident_id`),
  ADD UNIQUE KEY `contact_no` (`contact_no`),
  ADD KEY `registered_by` (`registered_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `account_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `resident_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`);

--
-- Constraints for table `residents`
--
ALTER TABLE `residents`
  ADD CONSTRAINT `residents_ibfk_2` FOREIGN KEY (`registered_by`) REFERENCES `accounts` (`account_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
