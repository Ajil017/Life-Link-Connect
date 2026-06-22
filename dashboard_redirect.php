<?php
// Start session
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Redirect based on user type
$user_type = $_SESSION["user_type"];

switch ($user_type) {
    case 'admin':
        header("location: admin_dashboard.php");
        break;
    case 'hospital_staff':
        header("location: hospital_dashboard.php");
        break;
    case 'donor':
        header("location: donor_dashboard.php");
        break;
    case 'receiver':
        header("location: receiver_dashboard.php");
        break;
    case 'ambulance_driver':
        header("location: ambulance_dashboard.php");
        break;
    default:
        // If user type is not recognized, log out and redirect to login
        header("location: logout.php");
        break;
}
exit;
?>
