-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 18, 2026 at 08:20 AM
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
-- Database: `assignment`
--

-- --------------------------------------------------------

--
-- Table structure for table `diary_entries`
--

CREATE TABLE `diary_entries` (
  `entry_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `mood_status` enum('Happy','Excited','Neutral','Sad','Tired','Anxious','Angry') NOT NULL DEFAULT 'Neutral',
  `entry_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exercise_records`
--

CREATE TABLE `exercise_records` (
  `exercise_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `duration` int(11) NOT NULL,
  `calories_burned` int(11) NOT NULL,
  `exercise_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exercise_records`
--

INSERT INTO `exercise_records` (`exercise_id`, `user_id`, `activity_type`, `duration`, `calories_burned`, `exercise_date`, `created_at`) VALUES
(2, 1, 'Jogging', 30, 200, '2026-08-09', '2026-08-09 06:18:53'),
(3, 2, 'Cycling', 45, 300, '2026-08-09', '2026-08-09 06:21:56');

-- --------------------------------------------------------

--
-- Table structure for table `habits`
--

CREATE TABLE `habits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `habit_name` varchar(255) NOT NULL,
  `target_frequency` varchar(100) NOT NULL,
  `completion_status` varchar(50) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `money_tracker`
--

CREATE TABLE `money_tracker` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('income','expense') NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `money_tracker`
--

INSERT INTO `money_tracker` (`id`, `user_id`, `amount`, `category`, `description`, `type`, `date`, `created_at`) VALUES
(1, 1, 1500.00, 'Salary', 'Part-time job salary', 'income', '2026-08-01', '2026-08-17 11:42:17'),
(2, 1, 200.00, 'Allowance', 'Monthly allowance', 'income', '2026-08-03', '2026-08-17 11:42:17'),
(3, 1, 35.50, 'Food', 'Lunch with friends', 'expense', '2026-08-05', '2026-08-17 11:42:17'),
(4, 1, 15.00, 'Transport', 'Grab to campus', 'expense', '2026-08-06', '2026-08-17 11:42:17'),
(5, 1, 89.90, 'Entertainment', 'Movie and snacks', 'expense', '2026-08-08', '2026-08-17 11:42:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'student',
  `reg_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `role`, `reg_date`) VALUES
(1, 'student1', 'student1@test.com', '$2y$10$GBWEPHOhlt0zzUtefxR/vu0fJEDAlycFQ9njynvlLmx.rtPBns.hy', 'student', '2026-08-09 08:11:14'),
(2, 'student2', 'student2@test.com', '$2y$10$HtLECzPXY9lzdZ6xYYqX9.97q.ciVRkNOcTOTfnqepP4Z1ExTD1hS', 'student', '2026-08-09 08:20:33'),
(3, 'admin1', 'admin1@test.com', '$2y$10$zmE7iqf5p3zfyfNBEh/UkO2ZmDARLHeetlyVN6Wi4ljhyN9R125aK', 'admin', '2026-08-09 08:24:37'),
(4, 'enica', 'enica5268@gmail.com', '$2y$10$OPdnRup9kS9pTi2Fzgb2Yeq8oIEKzYTXGnOzyNsu1BlpFQTX7epqe', 'student', '2026-08-12 15:31:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `diary_entries`
--
ALTER TABLE `diary_entries`
  ADD PRIMARY KEY (`entry_id`),
  ADD KEY `fk_diary_user` (`user_id`);

--
-- Indexes for table `exercise_records`
--
ALTER TABLE `exercise_records`
  ADD PRIMARY KEY (`exercise_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `habits`
--
ALTER TABLE `habits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `money_tracker`
--
ALTER TABLE `money_tracker`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `diary_entries`
--
ALTER TABLE `diary_entries`
  MODIFY `entry_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exercise_records`
--
ALTER TABLE `exercise_records`
  MODIFY `exercise_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `habits`
--
ALTER TABLE `habits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `money_tracker`
--
ALTER TABLE `money_tracker`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `diary_entries`
--
ALTER TABLE `diary_entries`
  ADD CONSTRAINT `fk_diary_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `exercise_records`
--
ALTER TABLE `exercise_records`
  ADD CONSTRAINT `fk_exercise_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `money_tracker`
--
ALTER TABLE `money_tracker`
  ADD CONSTRAINT `money_tracker_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
