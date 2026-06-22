<?php
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$leaderboard_data = [];

// --- Fetch Top Donors ---
// We select users who are donors and order them by their points in descending order
$sql = "SELECT full_name, blood_group, points FROM users WHERE user_type = 'donor' AND points > 0 ORDER BY points DESC LIMIT 50";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $leaderboard_data[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Leaderboard - LifeFlow Connect</title>
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
                <i class="fas fa-heartbeat text-3xl mr-2 text-red-500"></i>
                LifeFlow
            </a>
            <nav>
                <a href="dashboard_redirect.php" class="flex items-center p-3 mt-4 text-lg hover:bg-gray-700 rounded-lg">
                    <i class="fas fa-tachometer-alt w-6"></i><span class="ml-4">Dashboard</span>
                </a>
                <a href="profile.php" class="flex items-center p-3 mt-4 text-lg hover:bg-gray-700 rounded-lg">
                    <i class="fas fa-user-circle w-6"></i><span class="ml-4">Profile</span>
                </a>
                <a href="leaderboard.php" class="flex items-center p-3 text-lg bg-red-500 rounded-lg">
                    <i class="fas fa-trophy w-6"></i><span class="ml-4">Leaderboard</span>
                </a>
                <a href="logout.php" class="flex items-center p-3 mt-10 text-lg hover:bg-red-600 rounded-lg">
                    <i class="fas fa-sign-out-alt w-6"></i><span class="ml-4">Logout</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <main class="flex-1 p-10 overflow-y-auto">
            <header class="mb-8 text-center">
                <i class="fas fa-trophy text-6xl text-yellow-400"></i>
                <h1 class="text-4xl font-bold text-gray-800 mt-4">Top Donors</h1>
                <p class="text-gray-500 max-w-2xl mx-auto mt-2">Our heartfelt thanks to our most frequent donors. Top contributors are eligible for free medical camp vouchers as a token of our appreciation!</p>
            </header>
            
            <div class="bg-white p-8 rounded-xl shadow-lg max-w-4xl mx-auto">
                 <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                            <tr>
                                <th class="py-3 px-6 text-center">Rank</th>
                                <th class="py-3 px-6 text-left">Donor Name</th>
                                <th class="py-3 px-6 text-center">Blood Group</th>
                                <th class="py-3 px-6 text-center">Points</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700 text-md">
                            <?php if (empty($leaderboard_data)): ?>
                                <tr>
                                    <td colspan="4" class="py-4 px-6 text-center text-gray-500">No donor data available yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($leaderboard_data as $index => $donor): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50 <?php if($index < 3) echo 'font-bold'; ?>">
                                    <td class="py-4 px-6 text-center">
                                        <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center
                                        <?php 
                                            if($index == 0) echo 'bg-yellow-400 text-white';
                                            elseif($index == 1) echo 'bg-gray-400 text-white';
                                            elseif($index == 2) echo 'bg-yellow-600 text-white';
                                            else echo 'bg-gray-200';
                                        ?>">
                                        <?php echo $index + 1; ?>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6"><?php echo htmlspecialchars($donor['full_name']); ?></td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="font-semibold text-red-600"><?php echo htmlspecialchars($donor['blood_group']); ?></span>
                                    </td>
                                    <td class="py-4 px-6 text-center"><?php echo htmlspecialchars($donor['points']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
