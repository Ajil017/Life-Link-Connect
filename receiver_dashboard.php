<?php
require_once 'db_connect.php';

// Check if user is logged in and is a receiver
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'receiver') {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$full_name = $_SESSION["full_name"];
$request_history = [];
$success_message = "";
$error_message = "";

// --- Fetch Receiver's Information ---
$sql_user = "SELECT blood_group FROM users WHERE user_id = ?";
$user_info = [];
if($stmt_user = $conn->prepare($sql_user)){
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    $user_info = $result_user->fetch_assoc();
    $stmt_user->close();
}


// --- Handle Form Submission for a New Blood Request ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_request'])) {
    $blood_group_needed = $_POST['blood_group'];
    $units_requested = $_POST['units_requested'];
    
    // Basic validation
    if (empty($blood_group_needed) || empty($units_requested) || !is_numeric($units_requested) || $units_requested <= 0) {
        $error_message = "Please select a valid blood group and enter a positive number for units.";
    } else {
        // CORRECTED: The column name is 'units_requested'
        $sql_request = "INSERT INTO blood_requests (requester_id, blood_group, units_requested, status) VALUES (?, ?, ?, 'Pending')";
        if($stmt_request = $conn->prepare($sql_request)){
            $stmt_request->bind_param("isi", $user_id, $blood_group_needed, $units_requested);
            if($stmt_request->execute()){
                $success_message = "Your blood request has been submitted successfully. You will be notified of any updates.";
            } else {
                $error_message = "Something went wrong. Please try again.";
            }
            $stmt_request->close();
        }
    }
}


// --- Fetch Receiver's Request History ---
// CORRECTED: The column name is 'created_at', not 'request_date'
$sql_history = "SELECT blood_group, units_requested, created_at, status FROM blood_requests WHERE requester_id = ? ORDER BY created_at DESC";
if($stmt_history = $conn->prepare($sql_history)){
    $stmt_history->bind_param("i", $user_id);
    $stmt_history->execute();
    $result_history = $stmt_history->get_result();
    while($row = $result_history->fetch_assoc()){
        $request_history[] = $row;
    }
    $stmt_history->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receiver Dashboard - LifeFlow Connect</title>
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
                <a href="receiver_dashboard.php" class="flex items-center p-3 text-lg bg-red-500 rounded-lg">
                    <i class="fas fa-tachometer-alt w-6"></i><span class="ml-4">Dashboard</span>
                </a>
                 <a href="find_blood.php" class="flex items-center p-3 mt-4 text-lg hover:bg-gray-700 rounded-lg">
                    <i class="fas fa-search-location w-6"></i><span class="ml-4">Find Blood</span>
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
                <h1 class="text-3xl font-bold text-gray-800">Receiver Dashboard</h1>
                <div class="text-right">
                    <p class="font-semibold text-lg"><?php echo htmlspecialchars($full_name); ?></p>
                    <p class="text-sm text-gray-500">Your Blood Group: <span class="font-bold text-red-600"><?php echo htmlspecialchars($user_info['blood_group'] ?? 'N/A'); ?></span></p>
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

            <!-- New Blood Request Section -->
            <div class="bg-white p-8 rounded-xl shadow-lg mb-8">
                <h2 class="text-2xl font-bold text-gray-700 mb-6">Request Emergency Blood</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                    <div>
                        <label for="blood_group" class="block text-sm font-medium text-gray-700">Blood Group Needed</label>
                        <select name="blood_group" id="blood_group" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                             <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                    <div>
                        <label for="units_requested" class="block text-sm font-medium text-gray-700">Units Needed</label>
                        <input type="number" name="units_requested" id="units_requested" min="1" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500" placeholder="e.g., 2">
                    </div>
                    <div>
                        <button type="submit" name="submit_request" class="w-full px-6 py-2.5 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                           <i class="fas fa-paper-plane mr-2"></i>Submit Request
                        </button>
                    </div>
                </form>
            </div>

            <!-- Request History Section -->
            <div class="bg-white p-8 rounded-xl shadow-lg">
                <h2 class="text-2xl font-bold text-gray-700 mb-6">Your Request History</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-200 text-gray-600">
                            <tr>
                                <th class="py-3 px-6 text-left">Blood Group</th>
                                <th class="py-3 px-6 text-center">Units</th>
                                <th class="py-3 px-6 text-left">Date</th>
                                <th class="py-3 px-6 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700">
                            <?php if (empty($request_history)): ?>
                                <tr>
                                    <td colspan="4" class="py-4 px-6 text-center text-gray-500">You have no request history yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($request_history as $request): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="py-4 px-6 font-bold"><?php echo htmlspecialchars($request['blood_group']); ?></td>
                                    <td class="py-4 px-6 text-center"><?php echo htmlspecialchars($request['units_requested']); ?></td>
                                    <td class="py-4 px-6"><?php echo date("d M Y, h:i A", strtotime($request['created_at'])); ?></td>
                                    <td class="py-4 px-6 text-center">
                                        <span class="px-3 py-1 text-sm font-semibold rounded-full
                                        <?php 
                                            switch($request['status']){
                                                case 'Fulfilled': echo 'bg-green-100 text-green-800'; break;
                                                case 'Pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                                case 'Rejected': echo 'bg-red-100 text-red-800'; break;
                                            }
                                        ?>">
                                            <?php echo htmlspecialchars($request['status']); ?>
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

