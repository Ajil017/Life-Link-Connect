<?php
require_once 'db_connect.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'admin') {
    header("location: login.php");
    exit;
}

$full_name = $_SESSION["full_name"];

// --- Initialize Stats ---
$stats = [
    'total_users' => 0,
    'total_donors' => 0,
    'total_hospitals' => 0,
    'total_donations' => 0
];
$recent_donations = [];
$all_users = [];

// --- Fetch Dashboard Statistics ---
// Total Users (donors and receivers)
$stats['total_users'] = $conn->query("SELECT COUNT(user_id) as count FROM users WHERE user_type IN ('donor', 'receiver')")->fetch_assoc()['count'];
// Total Donors
$stats['total_donors'] = $conn->query("SELECT COUNT(user_id) as count FROM users WHERE user_type = 'donor'")->fetch_assoc()['count'];
// Total Hospitals
$stats['total_hospitals'] = $conn->query("SELECT COUNT(hospital_id) as count FROM hospitals")->fetch_assoc()['count'];
// Total Completed Donations
$stats['total_donations'] = $conn->query("SELECT COUNT(donation_id) as count FROM donations WHERE status = 'Completed'")->fetch_assoc()['count'];


// --- Fetch All Users (for display) ---
$sql_users = "SELECT user_id, full_name, email, phone, blood_group, user_type, created_at FROM users WHERE user_type != 'admin' ORDER BY created_at DESC LIMIT 100";
$result_users = $conn->query($sql_users);
if($result_users->num_rows > 0){
    while($row = $result_users->fetch_assoc()){
        $all_users[] = $row;
    }
}

// --- Fetch Recent Donations ---
$sql_donations = "SELECT d.donation_date, u.full_name as donor_name, h.name as hospital_name, d.status 
                  FROM donations d
                  JOIN users u ON d.donor_id = u.user_id
                  JOIN hospitals h ON d.hospital_id = h.hospital_id
                  ORDER BY d.donation_date DESC 
                  LIMIT 10";
$result_donations = $conn->query($sql_donations);
if($result_donations->num_rows > 0){
     while($row = $result_donations->fetch_assoc()){
        $recent_donations[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LifeLink Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100">

    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="bg-gray-800 text-white w-64 p-6 space-y-6">
            <a href="index.html" class="text-2xl font-bold text-white flex items-center mb-10">
                <i class="fas fa-user-shield text-3xl mr-2 text-red-500"></i>
                LifeLink
            </a>
            <nav>
                <a href="admin_dashboard.php" class="flex items-center p-3 text-lg bg-red-500 rounded-lg">
                    <i class="fas fa-tachometer-alt w-6"></i><span class="ml-4">Dashboard</span>
                </a>
                 <a href="profile.php" class="flex items-center p-3 mt-4 text-lg hover:bg-gray-700 rounded-lg">
                    <i class="fas fa-user-circle w-6"></i><span class="ml-4">Profile</span>
                </a>
                <a href="logout.php" class="flex items-center p-3 mt-10 text-lg hover:bg-red-600 rounded-lg">
                    <i class="fas fa-sign-out-alt w-6"></i><span class="ml-4">Logout</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <main class="flex-1 p-10 overflow-y-auto">
            <header class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Admin Dashboard</h1>
                <p class="font-semibold text-lg">Welcome, <?php echo htmlspecialchars($full_name); ?>!</p>
            </header>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Users</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $stats['total_users']; ?></p>
                    </div>
                    <i class="fas fa-users text-4xl text-blue-400"></i>
                </div>
                 <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Registered Donors</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $stats['total_donors']; ?></p>
                    </div>
                    <i class="fas fa-user-plus text-4xl text-green-400"></i>
                </div>
                 <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Partner Hospitals</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $stats['total_hospitals']; ?></p>
                    </div>
                    <i class="fas fa-hospital text-4xl text-red-400"></i>
                </div>
                 <div class="bg-white p-6 rounded-lg shadow-md flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Completed Donations</p>
                        <p class="text-3xl font-bold text-gray-800"><?php echo $stats['total_donations']; ?></p>
                    </div>
                    <i class="fas fa-hand-holding-heart text-4xl text-purple-400"></i>
                </div>
            </div>

            <!-- Main Data Tables -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- All Users Table -->
                <div class="lg:col-span-2 bg-white p-8 rounded-xl shadow-lg">
                     <h2 class="text-2xl font-bold text-gray-700 mb-6">User Management</h2>
                     <div class="overflow-y-auto max-h-96">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-200 text-gray-600 sticky top-0">
                                <tr>
                                    <th class="py-3 px-6 text-left">Name</th>
                                    <th class="py-3 px-6 text-left">Email</th>
                                    <th class="py-3 px-6 text-center">Role</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                <?php foreach($all_users as $user): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="py-3 px-6"><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td class="py-3 px-6"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="py-3 px-6 text-center capitalize"><?php echo htmlspecialchars($user['user_type']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Donations Log -->
                <div class="bg-white p-8 rounded-xl shadow-lg">
                    <h2 class="text-2xl font-bold text-gray-700 mb-6">Recent Activity</h2>
                    <div class="space-y-4 overflow-y-auto max-h-96">
                        <?php foreach($recent_donations as $donation): ?>
                        <div class="flex items-start">
                             <div class="w-10 h-10 flex-shrink-0 rounded-full flex items-center justify-center 
                                <?php 
                                    if($donation['status'] == 'Completed') echo 'bg-green-100';
                                    elseif($donation['status'] == 'Scheduled') echo 'bg-blue-100';
                                    else echo 'bg-red-100';
                                ?>">
                                <i class="fas 
                                <?php 
                                    if($donation['status'] == 'Completed') echo 'fa-check-circle text-green-500';
                                    elseif($donation['status'] == 'Scheduled') echo 'fa-calendar-alt text-blue-500';
                                    else echo 'fa-times-circle text-red-500';
                                ?>"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-800 font-semibold"><?php echo htmlspecialchars($donation['donor_name']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($donation['status']); ?> donation at <?php echo htmlspecialchars($donation['hospital_name']); ?></p>
                                <p class="text-xs text-gray-400"><?php echo date("d M Y, h:i A", strtotime($donation['donation_date'])); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </main>
    </div>
</body>
</html>

