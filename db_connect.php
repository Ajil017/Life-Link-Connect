<?php
// This file contains the database connection logic.
// Include this file in other PHP scripts to access the database.

// --- IMPORTANT ---
// The error "Access denied...(using password: NO)" means your MySQL 'root' user has a password set.
// You MUST enter that password below for the connection to work.
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Default XAMPP username
define('DB_PASSWORD', 'Ambika@2005');     // <-- ENTER YOUR MYSQL ROOT PASSWORD HERE
define('DB_NAME', 'blood_donation_db');

// Attempt to connect to MySQL database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($conn === false){
    die("ERROR: Could not connect. " . $conn->connect_error);
}

// Optional: Set charset to utf8mb4 for full Unicode support
$conn->set_charset("utf8mb4");

// Start session on every page that includes this file
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>