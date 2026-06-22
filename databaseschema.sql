-- This SQL script creates the database and all necessary tables for the Blood Donation Management System.
-- To use, open phpMyAdmin, create a new database named 'blood_donation_db', and import this file.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `blood_donation_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL, -- Always store hashed passwords!
  `phone` VARCHAR(15) NOT NULL,
  `address` TEXT,
  `blood_group` VARCHAR(5) NOT NULL,
  `user_type` ENUM('donor', 'receiver', 'admin', 'hospital_staff', 'ambulance_driver') NOT NULL,
  `points` INT DEFAULT 0, -- For the leaderboard
  `last_donation_date` DATE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `hospitals`
--

CREATE TABLE `hospitals` (
  `hospital_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `address` TEXT NOT NULL,
  `phone` VARCHAR(15) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `managed_by_user_id` INT, -- Link to a hospital_staff user
  FOREIGN KEY (`managed_by_user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `blood_units`
--

CREATE TABLE `blood_units` (
  `unit_id` INT AUTO_INCREMENT PRIMARY KEY,
  `hospital_id` INT NOT NULL,
  `blood_group` VARCHAR(5) NOT NULL,
  `quantity` INT NOT NULL, -- Number of units available
  `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`hospital_id`) REFERENCES `hospitals`(`hospital_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `blood_requests`
--

CREATE TABLE `blood_requests` (
  `request_id` INT AUTO_INCREMENT PRIMARY KEY,
  `requester_id` INT NOT NULL,
  `hospital_id` INT, -- Hospital where blood is needed
  `blood_group` VARCHAR(5) NOT NULL,
  `units_required` INT NOT NULL,
  `reason` TEXT,
  `is_emergency` BOOLEAN DEFAULT FALSE,
  `status` ENUM('Pending', 'Approved', 'Rejected', 'Fulfilled') DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`requester_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`hospital_id`) REFERENCES `hospitals`(`hospital_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `donation_id` INT AUTO_INCREMENT PRIMARY KEY,
  `donor_id` INT NOT NULL,
  `hospital_id` INT NOT NULL,
  `units_donated` INT NOT NULL,
  `donation_type` ENUM('Free', 'Paid') DEFAULT 'Free',
  `donation_date` DATETIME NOT NULL,
  `status` ENUM('Scheduled', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
  FOREIGN KEY (`donor_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`hospital_id`) REFERENCES `hospitals`(`hospital_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` INT AUTO_INCREMENT PRIMARY KEY,
  `donation_id` INT NOT NULL,
  `amount` DECIMAL(10, 2) NOT NULL,
  `transaction_id` VARCHAR(255) NOT NULL,
  `payment_status` VARCHAR(50) NOT NULL,
  `payment_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`donation_id`) REFERENCES `donations`(`donation_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ambulances`
--

CREATE TABLE `ambulances` (
  `ambulance_id` INT AUTO_INCREMENT PRIMARY KEY,
  `driver_user_id` INT NOT NULL,
  `vehicle_number` VARCHAR(20) NOT NULL,
  `current_location` VARCHAR(255), -- Could be updated via GPS in a full app
  `status` ENUM('Available', 'Busy', 'On-call') DEFAULT 'Available',
  FOREIGN KEY (`driver_user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
