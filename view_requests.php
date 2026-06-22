<?php
require_once 'db_connect.php';

// Check if user is logged in and is a donor
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'donor') {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$full_name = $_SESSION["full_name"];
$pending_requests = [];
$success_message = "";
$error_message = "";

// --- Handle Form Submission for Fulfilling a Request ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['fulfill_request'])) {
    $request_id = $_POST['request_id'];
    $units_requested = $_POST['units_requested'];

    // Start a database transaction
    $conn->begin_transaction();
    try {
        // 1. Update the blood_requests table
        $sql_update_request = "UPDATE blood_requests SET status = 'Fulfilled' WHERE request_id = ? AND status = 'Pending'";
        $stmt_update_request = $conn->prepare($sql_update_request);
        $stmt_update_request->bind_param("i", $request_id);
        $stmt_update_request->execute();

        // Check if the request was actually updated (to prevent double fulfillment)
        if ($stmt_update_request->affected_rows > 0) {
            // 2. Add a record to the donations table
            // NOTE: We are using hospital_id = 1 as a default processing center for this direct donation.
            $sql_insert_donation = "INSERT INTO donations (donor_id, hospital_id, units_donated, donation_type, status, donation_date) VALUES (?, 1, ?, 'Free', 'Completed', NOW())";
            $stmt_insert_donation = $conn->prepare($sql_insert_donation);
            $stmt_insert_donation->bind_param("ii", $user_id, $units_requested);
            $stmt_insert_donation->execute();

            // 3. Award points to the donor
            $sql_update_points = "UPDATE users SET points = points + 10, last_donation_date = NOW() WHERE user_id = ?";
            $stmt_update_points = $conn->prepare($sql_update_points);
            $stmt_update_points->bind_param("i", $user_id);
            $stmt_update_points->execute();

            $conn->commit();
            $success_message = "Thank you for your life-saving pledge! The request has been marked as fulfilled.";
        } else {
            // The request was likely fulfilled by another donor just moments ago
            $conn->rollback();
            $error_message = "This request is no longer available. Another donor may have already fulfilled it.";
        }

    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        $error_message = "A database error occurred. Please try again.";
    }
}


// --- Fetch all pending blood requests ---
$sql_requests = "SELECT br.request_id, br.blood_group, br.units_requested, br.created_at, u.full_name as requester_name, u.address 
                 FROM blood_requests br 
                 JOIN users u ON br.requester_id = u.user_id 
                 WHERE br.status = 'Pending' 
                 ORDER BY br.created_at DESC";

$result_requests = $conn->query($sql_requests);
if ($result_requests->num_rows > 0) {
    while ($row = $result_requests->fetch_assoc()) {
        $pending_requests[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Blood Requests - LifeLink Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">

    <div class="flex h-screen">
        <!-- Sidebar (Re-used from donor dashboard for consistency) -->
        <div class="bg-gray-800 text-white w-64 p-6 space-y-6">
            <a href="index.html" class="text-2xl font-bold text-white flex items-center mb-10">
                <i class="fas fa-heartbeat text-3xl mr-2 text-red-500"></i>
                LifeLink
            </a>
            <nav>
                <a href="donor_dashboard.php" class="flex items-center p-3 text-lg hover:bg-gray-700 rounded-lg">
                    <i class="fas fa-tachometer-alt w-6"></i><span class="ml-4">Dashboard</span>
                </a>
                <a href="view_requests.php" class="flex items-center p-3 text-lg bg-red-500 rounded-lg">
                    <i class="fas fa-hand-holding-heart w-6"></i><span class="ml-4">View Requests</span>
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
            <header class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Active Blood Requests</h1>
                <p class="text-gray-600 mt-1">Here are the current requests from patients in need. Your donation can save a life.</p>
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

            <div class="bg-white p-8 rounded-xl shadow-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-200 text-gray-600">
                            <tr>
                                <th class="py-3 px-6 text-left">Requester</th>
                                <th class="py-3 px-6 text-left">Location</th>
                                <th class="py-3 px-6 text-center">Blood Group</th>
                                <th class="py-3 px-6 text-center">Units Needed</th>
                                <th class="py-3 px-6 text-left">Date Requested</th>
                                <th class="py-3 px-6 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700">
                             <?php if (empty($pending_requests)): ?>
                                <tr>
                                    <td colspan="6" class="py-6 px-6 text-center text-gray-500">There are currently no pending blood requests. Thank you for checking!</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pending_requests as $request): ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="py-4 px-6 font-medium"><?php echo htmlspecialchars($request['requester_name']); ?></td>
                                        <td class="py-4 px-6"><?php echo htmlspecialchars($request['address']); ?></td>
                                        <td class="py-4 px-6 text-center">
                                            <span class="font-bold text-red-600 text-lg"><?php echo htmlspecialchars($request['blood_group']); ?></span>
                                        </td>
                                        <td class="py-4 px-6 text-center font-semibold"><?php echo htmlspecialchars($request['units_requested']); ?></td>
                                        <td class="py-4 px-6"><?php echo date("d M Y", strtotime($request['created_at'])); ?></td>
                                        <td class="py-4 px-6 text-center">
                                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                                <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                                <input type="hidden" name="units_requested" value="<?php echo $request['units_requested']; ?>">
                                                <button type="submit" name="fulfill_request" class="px-4 py-2 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-75">
                                                    <i class="fas fa-check-circle mr-2"></i>Pledge to Donate
                                                </button>
                                            </form>
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
