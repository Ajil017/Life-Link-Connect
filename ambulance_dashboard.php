<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'ambulance_driver') {
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Ambulance Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <h1 class="text-3xl font-bold">Welcome, Driver <?php echo htmlspecialchars($_SESSION["full_name"]); ?>!</h1>
        <p>This is the Ambulance Dashboard. View emergency calls and update status here.</p>
        <a href="logout.php" class="text-red-500 hover:underline mt-4 inline-block">Logout</a>
    </div>
</body>
</html>
