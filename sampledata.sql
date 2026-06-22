-- This script inserts some initial data into your tables.
-- Run this in phpMyAdmin after creating the tables with `database.sql`.

INSERT INTO `users` (`full_name`, `email`, `password`, `phone`, `address`, `blood_group`, `user_type`, `points`) VALUES
('Admin User', 'admin@LifeLink.com', '$2y$10$your_hashed_password_here', '1112223330', '1 Admin Way, Central City', 'O+', 'admin', 0),
('John Doe', 'john.d@example.com', '$2y$10$your_hashed_password_here', '9876543210', '123 Life St, Donorville', 'A+', 'donor', 50),
('Jane Smith', 'jane.s@example.com', '$2y$10$your_hashed_password_here', '8765432109', '456 Health Ave, Receiverton', 'B-', 'receiver', 0),
('Dr. Emily Carter', 'ecarter@cityhospital.com', '$2y$10$your_hashed_password_here', '7654321098', '789 Cure Blvd, Medicity', 'AB+', 'hospital_staff', 0),
('Mike Ross', 'mike.ambulance@example.com', '$2y$10$your_hashed_password_here', '6543210987', 'On the road', 'O-', 'ambulance_driver', 0);

INSERT INTO `hospitals` (`name`, `address`, `phone`, `email`, `managed_by_user_id`) VALUES
('City General Hospital', '789 Cure Blvd, Medicity', '7654321098', 'contact@cityhospital.com', 4),
('Unity Medical Center', '101 Healing Cross, Medicity', '7654321000', 'info@unitymedical.com', NULL);

INSERT INTO `blood_units` (`hospital_id`, `blood_group`, `quantity`) VALUES
(1, 'A+', 15),
(1, 'B+', 8),
(1, 'O-', 12),
(1, 'AB+', 5),
(2, 'A+', 10),
(2, 'O+', 20);

INSERT INTO `donations` (`donor_id`, `hospital_id`, `units_donated`, `donation_type`, `donation_date`, `status`) VALUES
(2, 1, 1, 'Free', '2025-09-15 10:00:00', 'Completed');

INSERT INTO `blood_requests` (`requester_id`, `hospital_id`, `blood_group`, `units_required`, `reason`, `is_emergency`, `status`) VALUES
(3, 1, 'B-', 2, 'Scheduled Surgery', 0, 'Pending');

INSERT INTO `ambulances` (`driver_user_id`, `vehicle_number`, `current_location`, `status`) VALUES
(5, 'KL-08-AZ-5555', 'Near City General Hospital', 'Available');
