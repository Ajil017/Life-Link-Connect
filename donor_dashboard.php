<?php
require_once 'db_connect.php';

// Check if user is logged in and is a donor
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'donor') {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$full_name = $_SESSION["full_name"];
$donation_history = [];
$hospitals = [];
$success_message = "";
$error_message = "";

// --- Fetch Donor's Information ---
$sql_user = "SELECT blood_group, points, last_donation_date FROM users WHERE user_id = ?";
$donor_info = [];
if($stmt_user = $conn->prepare($sql_user)){
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    $donor_info = $result_user->fetch_assoc();
    $stmt_user->close();
}

// --- Fetch available hospitals for donation ---
$sql_hospitals = "SELECT hospital_id, name FROM hospitals ORDER BY name ASC";
$result_hospitals = $conn->query($sql_hospitals);
if($result_hospitals->num_rows > 0){
    while($row = $result_hospitals->fetch_assoc()){
        $hospitals[] = $row;
    }
}

// --- Handle Form Submission for Scheduling a Donation ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['schedule_donation'])) {
    $hospital_id = $_POST['hospital_id'];
    $donation_date = $_POST['donation_date'];
    
    // Basic validation
    if (empty($hospital_id) || empty($donation_date)) {
        $error_message = "Please select a hospital and a date.";
    } else {
        $sql_schedule = "INSERT INTO donations (donor_id, hospital_id, units_donated, donation_type, donation_date, status) VALUES (?, ?, 1, 'Free', ?, 'Scheduled')";
        if($stmt_schedule = $conn->prepare($sql_schedule)){
            $stmt_schedule->bind_param("iis", $user_id, $hospital_id, $donation_date);
            if($stmt_schedule->execute()){
                $success_message = "Your donation has been scheduled successfully!";
            } else {
                $error_message = "Something went wrong. Please try again.";
            }
            $stmt_schedule->close();
        }
    }
}


// --- Fetch Donor's Donation History ---
$sql_history = "SELECT h.name as hospital_name, d.units_donated, d.donation_date, d.status FROM donations d JOIN hospitals h ON d.hospital_id = h.hospital_id WHERE d.donor_id = ? ORDER BY d.donation_date DESC";
if($stmt_history = $conn->prepare($sql_history)){
    $stmt_history->bind_param("i", $user_id);
    $stmt_history->execute();
    $result_history = $stmt_history->get_result();
    while($row = $result_history->fetch_assoc()){
        $donation_history[] = $row;
    }
    $stmt_history->close();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Dashboard - LifeFlow Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
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
                <a href="donor_dashboard.php" class="flex items-center p-3 text-lg bg-red-500 rounded-lg">
                    <i class="fas fa-tachometer-alt w-6"></i><span class="ml-4">Dashboard</span>
                </a>
                <a href="profile.php" class="flex items-center p-3 mt-4 text-lg hover:bg-gray-700 rounded-lg">
                    <i class="fas fa-user-circle w-6"></i><span class="ml-4">Profile</span>
                </a>
                <a href="leaderboard.php" class="flex items-center p-3 mt-4 text-lg hover:bg-gray-700 rounded-lg">
                    <i class="fas fa-trophy w-6"></i><span class="ml-4">Leaderboard</span>
                </a>
                <a href="logout.php" class="flex items-center p-3 mt-10 text-lg hover:bg-red-600 rounded-lg">
                    <i class="fas fa-sign-out-alt w-6"></i><span class="ml-4">Logout</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <main class="flex-1 p-10 overflow-y-auto">
            <header class="flex justify-between items-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Donor Dashboard</h1>
                <div class="text-right">
                    <p class="font-semibold text-lg"><?php echo htmlspecialchars($full_name); ?></p>
                    <p class="text-sm text-gray-500">Blood Group: <span class="font-bold text-red-600"><?php echo htmlspecialchars($donor_info['blood_group'] ?? 'N/A'); ?></span></p>
                </div>
            </header>
            
            <?php if ($success_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo $success_message; ?></span>
            </div>
            <?php endif; ?>
             <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo $error_message; ?></span>
            </div>
            <?php endif; ?>

            <!-- Schedule Donation Section -->
            <div class="bg-white p-8 rounded-xl shadow-lg mb-8">
                <h2 class="text-2xl font-bold text-gray-700 mb-6">Schedule a New Donation</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                    <div>
                        <label for="hospital_id" class="block text-sm font-medium text-gray-700">Choose a Hospital</label>
                        <select name="hospital_id" id="hospital_id" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                            <option value="">Select Hospital</option>
                            <?php foreach($hospitals as $hospital): ?>
                                <option value="<?php echo $hospital['hospital_id']; ?>"><?php echo htmlspecialchars($hospital['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="donation_date" class="block text-sm font-medium text-gray-700">Preferred Date & Time</label>
                        <input type="datetime-local" name="donation_date" id="donation_date" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                    </div>
                    <div>
                        <button type="submit" name="schedule_donation" class="w-full px-6 py-2.5 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                           <i class="fas fa-calendar-check mr-2"></i>Schedule Now
                        </button>
                    </div>
                </form>
            </div>

            <!-- Donation History Section -->
            <div class="bg-white p-8 rounded-xl shadow-lg">
                <h2 class="text-2xl font-bold text-gray-700 mb-6">Your Donation History</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-200 text-gray-600">
                            <tr>
                                <th class="py-3 px-6 text-left">Hospital</th>
                                <th class="py-3 px-6 text-left">Date</th>
                                <th class="py-3 px-6 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700">
                            <?php if (empty($donation_history)): ?>
                                <tr>
                                    <td colspan="3" class="py-4 px-6 text-center text-gray-500">You have no donation history yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($donation_history as $donation): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="py-4 px-6"><?php echo htmlspecialchars($donation['hospital_name']); ?></td>
                                    <td class="py-4 px-6"><?php echo date("d M Y, h:i A", strtotime($donation['donation_date'])); ?></td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="px-3 py-1 text-sm font-semibold rounded-full
                                        <?php 
                                            switch($donation['status']){
                                                case 'Completed': echo 'bg-green-100 text-green-800'; break;
                                                case 'Scheduled': echo 'bg-blue-100 text-blue-800'; break;
                                                case 'Cancelled': echo 'bg-red-100 text-red-800'; break;
                                            }
                                        ?>">
                                            <?php echo htmlspecialchars($donation['status']); ?>
                                        </span>
                                    </td>
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

