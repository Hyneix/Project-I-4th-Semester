-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 02, 2026 at 02:12 PM
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
-- Database: `room_booking_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_roles`
--

CREATE TABLE `admin_roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `check_in_date` date DEFAULT NULL,
  `check_out_date` date DEFAULT NULL,
  `guests` int(11) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `booking_status` enum('Pending','Approved','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `privileges`
--

CREATE TABLE `privileges` (
  `privilege_id` int(11) NOT NULL,
  `privilege_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_privileges`
--

CREATE TABLE `role_privileges` (
  `role_id` int(11) NOT NULL,
  `privilege_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `room_name` varchar(100) NOT NULL,
  `room_type` varchar(50) DEFAULT NULL,
  `capacity` int(11) NOT NULL,
  `price_per_night` decimal(10,2) NOT NULL DEFAULT 0.00,
  `location` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('Available','Booked','Maintenance') DEFAULT 'Available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_name`, `room_type`, `capacity`, `price_per_night`, `location`, `description`, `image`, `status`, `created_at`) VALUES
(1, 'Classic 2 Beds', 'Classic', 2, 3000.00, 'Kathmandu', 'Comfortable room with two beds and basic facilities.', 'classic_2beds.jpg', 'Available', '2026-10-01 10:46:16'),
(2, 'Deluxe 1 Bed', 'Deluxe', 2, 3000.00, 'Kathmandu', 'Spacious deluxe room with a comfortable king-size bed.', 'deluxe_1bed.jpg', 'Available', '2026-10-01 11:10:54'),
(3, 'Family 3 Beds', 'Family', 3, 3000.00, 'Lalitpur', 'Large family room with three beds and additional space.', 'family_3beds.jpg', 'Available', '2026-10-01 11:10:54'),
(4, 'Premium 2 Beds', 'Premium', 2, 3000.00, 'Bhaktapur', 'Premium room with modern facilities and two beds.', 'premium_2beds.jpg', 'Available', '2026-10-01 11:10:54'),
(5, 'Standard 1 Bed', 'Standard', 1, 3000.00, 'Kathmandu', 'Simple and comfortable room suitable for a single guest.', 'standard_1bed.jpg', 'Available', '2026-10-01 11:16:20'),
(6, 'Deluxe 2 Beds', 'Deluxe', 2, 3000.00, 'Lalitpur', 'Spacious deluxe room with two comfortable beds and modern facilities.', 'deluxe_2beds.jpg', 'Available', '2026-10-01 11:16:20'),
(7, 'Family 4 Beds', 'Family', 4, 3000.00, 'Bhaktapur', 'Large family room with four beds and plenty of space for guests.', 'family_4beds.jpg', 'Available', '2026-10-01 11:16:20'),
(8, 'Premium 1 Bed', 'Premium', 2, 3000.00, 'Kathmandu', 'Elegant premium room with a large bed and modern amenities.', 'premium_1bed.jpg', 'Available', '2026-10-01 11:16:20'),
(9, 'Executive 2 Beds', 'Executive', 2, 3000.00, 'Lalitpur', 'Modern executive room with two beds and a comfortable working area.', 'executive_2beds.jpg', 'Booked', '2026-10-01 11:16:20'),
(10, 'Luxury 1 Bed', 'Luxury', 2, 3000.00, 'Bhaktapur', 'Luxury room with a spacious bed, stylish interior and premium facilities.', 'luxury_1bed.jpg', 'Available', '2026-10-01 11:16:20');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `phone`, `created_at`) VALUES
(1, 'Sushant Karki', 'whatgraza@gmail.com', '$2y$10$apL78.D0hSbePa29MICY2.Wmfn17Mkz3FQ7QW.g6Kh1SZW.vkhMwe', '9876565658', '2026-08-13 11:13:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `admin_roles`
--
ALTER TABLE `admin_roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `privileges`
--
ALTER TABLE `privileges`
  ADD PRIMARY KEY (`privilege_id`),
  ADD UNIQUE KEY `privilege_name` (`privilege_name`);

--
-- Indexes for table `role_privileges`
--
ALTER TABLE `role_privileges`
  ADD PRIMARY KEY (`role_id`,`privilege_id`),
  ADD KEY `privilege_id` (`privilege_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_roles`
--
ALTER TABLE `admin_roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `privileges`
--
ALTER TABLE `privileges`
  MODIFY `privilege_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `admin_roles` (`role_id`) ON UPDATE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `role_privileges`
--
ALTER TABLE `role_privileges`
  ADD CONSTRAINT `role_privileges_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `admin_roles` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `role_privileges_ibfk_2` FOREIGN KEY (`privilege_id`) REFERENCES `privileges` (`privilege_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
