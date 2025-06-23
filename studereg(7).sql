-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 09, 2025 at 10:02 AM
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
-- Database: `studereg`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `semester` varchar(50) NOT NULL DEFAULT 'Semester 1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `course_name`, `department`, `semester`) VALUES
(1, 'Programming', 'Computer Science', 'Semester 2'),
(2, 'Database', 'Computer Science', 'Semester 2'),
(3, 'Internet Programming', 'Computer Science', 'Semester 1'),
(4, 'Marketing', 'Accounting', 'Semester 2'),
(5, 'Economics', 'Accounting', 'Semester 1'),
(6, 'Maths', 'Marketing', 'Semester 2'),
(7, 'Management', 'Marketing', 'Semester 1'),
(8, 'integrate accounting', 'Accounting', 'Semester 1'),
(9, 'cs', 'Computer Science', 'Semester 1'),
(10, 'ip2', 'Computer Science', 'Semester 2');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `dept_name` varchar(100) DEFAULT NULL,
  `dept_code` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `dept_name`, `dept_code`) VALUES
(2, 'pharmacist ', NULL),
(3, 'data science', NULL),
(4, 'radiologu', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `grade` char(2) NOT NULL,
  `credit_hour` int(11) NOT NULL,
  `status` enum('Draft','Published') DEFAULT 'Draft',
  `semester` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `username`, `course_name`, `grade`, `credit_hour`, `status`, `semester`) VALUES
(1, 'tomi', 'Internet Programming', 'F', 4, 'Published', 0);

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `course_id` int(11) NOT NULL,
  `semester` varchar(50) NOT NULL,
  `registered_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`id`, `username`, `course_id`, `semester`, `registered_at`) VALUES
(1, 'nehm', 1, 'Semester 2', '2025-05-08 05:48:27'),
(2, 'nehm', 10, 'Semester 2', '2025-05-08 05:48:27'),
(3, 'q', 5, 'Semester 1', '2025-05-08 06:46:02'),
(4, 'tomi', 3, 'Semester 1', '2025-05-09 03:11:04');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstname` varchar(100) DEFAULT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `usertype` enum('admin','student','registeral') DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `course_name` varchar(255) NOT NULL,
  `registered_at` datetime DEFAULT current_timestamp(),
  `semester` varchar(50) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `dob`, `email`, `phone`, `username`, `password`, `department`, `usertype`, `course`, `course_name`, `registered_at`, `semester`, `profile_picture`) VALUES
(1, 'n', 'n', '1111-11-11', 'n@gamil.com', '000', 'nn', '$2y$10$XrwgclBwSbauvswcsk4dreil3WP20dd/Jso0MterXMhq9E5SgMFua', 'Accounting', 'admin', NULL, '', '2025-04-23 08:50:59', NULL, NULL),
(3, 'q', 'q', '1111-11-01', 'a@gamil.com', '888', 'c', '$2y$10$znRRLv8Ic0XuTJ.HQf0M1OI6hL6y4L03VadxsU17gU/6u6iT5l2OW', 'Accounting', 'student', NULL, '', '2025-04-23 08:50:59', NULL, NULL),
(4, 'q', 'q', '1111-11-11', 'q@gmail.com', '777', 'a', '$2y$10$CcY9Mfzr8HH9Jhwn6nFJAOTGJC9IvB5twS/lZgrb0iWqdn9Gz1SMW', 'Accounting', 'student', NULL, '', '2025-04-23 08:50:59', NULL, NULL),
(5, 'a', 'a', '1111-11-11', 'q@gmail.com', '9909', 'b', '$2y$10$kgsCe7778GoaI9fuC/.cFOAsMrZ17KxkLCugQB7PX8/sq37oGaU5m', 'Accounting', 'admin', NULL, '', '2025-04-23 08:50:59', NULL, NULL),
(6, 'd', 'd', '1222-02-22', 'a@gmail.com', '99', 'q', '$2y$10$SDaNLxOclXUtJqnYgNui5..TpFtmHEt74JARghlMM/DB.VwCM9YNW', 'Accounting', 'student', NULL, '', '2025-04-23 08:50:59', 'Semester 1', NULL),
(13, 'nehmya', 'biruk', '1111-11-11', 'mimneh@gmail.com', '909090', 'mimneh', '$2y$10$bqRvId7s8m4UUXznPB5YUuXtetW6SGrbcNd/JngEfA01ZW/TUF3pu', 'Computer Science', 'student', NULL, '', '2025-04-23 09:07:50', 'Semester 2', NULL),
(15, 'm', 'm', '1111-11-11', 'm@gamil.com', '000', 'mm', '$2y$10$9E60gfSNVLTFQDAiJp4MfuM8Fg6WvQWPRhPHVVS1sobjwmrqJoFBS', 'Marketing', 'registeral', NULL, '', '2025-04-23 09:09:55', NULL, NULL),
(18, 'thomas', 'habtamu', '1111-11-11', 'tomi@gmail.com', '09222222', 't', '$2y$10$OeMkVh5qCjb1CEY1o9SSpuFWsAL5c7RfEErkjGtkMID4O/NG4FzAW', 'Computer Science', 'student', NULL, '', '2025-04-24 08:17:48', 'Semester 2', NULL),
(20, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'PE', NULL, NULL, '', '2025-04-24 08:40:49', NULL, NULL),
(21, 'j', 'q', '1111-11-11', 'm@gmail.com', '5687', 'k', '$2y$10$qxYi4gxWLF5ujy5YZ4vnpe5crNiN4nSkeL3eaXMXonZhPdvGrjIgq', 'Accounting', 'admin', NULL, '', '2025-04-26 12:08:57', NULL, NULL),
(25, NULL, NULL, NULL, NULL, NULL, 'admin', '$2y$10$ToGT0WxY/J1GkDRqyNWH1eGdIGlWx9bncyYgbl7FHRhFSkblhZmSG', '', 'admin', NULL, '', '2025-04-27 06:48:41', NULL, NULL),
(26, NULL, NULL, NULL, NULL, NULL, 'registrar', '$2y$10$2UGZTZXZTZTIRyqg2u5GjOkufmrMxaHfGoGyV1fMQ8Xb7P/jPHNsK', '', '', NULL, '', '2025-04-27 06:48:41', NULL, NULL),
(27, 'a', 'a', '1111-11-11', 'b@gmail.com', '0808', 'nehmya', '$2y$10$hys9C4UXwftxy8PZkT/V8enVtdfiei2bEBwk8pE/ciqGamoCn0JI2', 'Accounting', 'registeral', NULL, '', '2025-04-27 06:52:09', NULL, NULL),
(28, 'q', 'q', '1111-11-01', 'c@gmail.com', '080', 'i', '$2y$10$egqK20i3kzmbaJYLP0pUtebE/J2PUfqNXgseCF7K9YMY42pShjie.', 'Accounting', 'student', NULL, '', '2025-04-27 07:02:42', NULL, NULL),
(29, 'q', 'qqq', '1111-11-01', 'd@gmail.com', '65656', 'we', '$2y$10$i/wO4cSZw5kYy5vHpZTywuM2C/xQt9hZIXPNUIeCtIZaPRIiTefLK', 'Computer Science', 'student', NULL, '', '2025-04-28 10:02:20', NULL, NULL),
(30, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'nursing', NULL, NULL, '', '2025-04-29 03:36:34', NULL, NULL),
(33, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pharmacist', NULL, NULL, '', '2025-04-29 06:40:22', NULL, NULL),
(34, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'innovation ', NULL, NULL, '', '2025-04-29 06:40:43', NULL, NULL),
(38, 'abe', 'qwe', '1111-11-11', 'm@gmail.com', '080', 'babi', '$2y$10$Pk/nE/STUKoWRgGFYwoB2eurPWUE7HKsdQjQYvSZLME1bEfcrbot2', 'data science', 'student', NULL, '', '2025-04-29 10:46:51', 'Semester 1', NULL),
(39, 'nehmya', 'biruk', '1111-11-11', 'mimneh@gmail.co', '09012634617', 'nehm', '$2y$10$BOD.y6CEEJNRMhrj1QoO9.nyJNRZD4kALaSSNdSkkLQ.lB3CtG8cm', 'Computer Science', 'student', NULL, '', '2025-05-08 05:48:01', 'Semester 2', NULL),
(40, 'thomas', 'habtamu', '1111-11-01', 'tomm@gmail.com', '0901222222', 'tomi', '$2y$10$8L/2ohhmel5PPDJo4J8dOODy2jj.Xzb.0eF.Ac3sFzpl6c0S6ywJq', 'pharmacist ', 'student', NULL, '', '2025-05-09 03:09:20', 'Semester 1', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_registration` (`username`,`course_id`,`semester`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `fk_grades_username` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`);

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `fk_registrations_username` FOREIGN KEY (`username`) REFERENCES `users` (`username`) ON DELETE CASCADE,
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`username`) REFERENCES `users` (`username`),
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
