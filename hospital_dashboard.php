<?php
require_once 'db_connect.php';

// Check if user is logged in and is a hospital
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'hospital') {
    header("location: login.php");
    exit;
}

$hospital_id = $_SESSION["user_id"];
$full_name = $_SESSION["full_name"];
$blood_units = [];
$scheduled_donations = [];
$update_message = "";

// --- Handle Form Submission for Updating Blood Units ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_units'])) {
    $blood_group = $_POST['blood_group'];
    $units = $_POST['units'];

    $sql = "UPDATE blood_units SET units = ? WHERE hospital_id = ? AND blood_group = ?";
    if($stmt = $conn->prepare($sql)){
        $stmt->bind_param("iis", $units, $hospital_id, $blood_group);
        if($stmt->execute()){
            $update_message = "Inventory for {$blood_group} updated successfully!";
        } else {
            $update_message = "Error updating inventory.";
        }
        $stmt->close();
    }
}

// --- Handle Donation Completion or Cancellation ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['donation_id'])) {
    $donation_id = $_POST['donation_id'];
    $donor_id = $_POST['donor_id'];
    $blood_group = $_POST['blood_group'];
    $action = $_POST['action'];

    if ($action === 'complete') {
        // Use a transaction for completing a donation
        $conn->begin_transaction();
        try {
            // 1. Update donation status
            $stmt1 = $conn->prepare("UPDATE donations SET status = 'Completed' WHERE donation_id = ?");
            $stmt1->bind_param("i", $donation_id);
            $stmt1->execute();

            // 2. Increment blood units for the hospital
            $stmt2 = $conn->prepare("UPDATE blood_units SET units = units + 1 WHERE hospital_id = ? AND blood_group = ?");
            $stmt2->bind_param("is", $hospital_id, $blood_group);
            $stmt2->execute();
            
            // 3. Update donor's points and last donation date
            $stmt3 = $conn->prepare("UPDATE users SET points = points + 10, last_donation_date = NOW() WHERE user_id = ?");
            $stmt3->bind_param("i", $donor_id);
            $stmt3->execute();

            $conn->commit();
            $update_message = "Donation marked as complete. Inventory and donor points updated.";

        } catch (mysqli_sql_exception $exception) {
            $conn->rollback();
            $update_message = "Transaction failed: " . $exception->getMessage();
        }
    } elseif ($action === 'cancel') {
        $stmt = $conn->prepare("UPDATE donations SET status = 'Cancelled' WHERE donation_id = ?");
        $stmt->bind_param("i", $donation_id);
        if($stmt->execute()){
            $update_message = "Donation has been cancelled.";
        }
    }
}


// --- Fetch Current Blood Inventory ---
$sql_inventory = "SELECT blood_group, units FROM blood_units WHERE hospital_id = ?";
if($stmt_inv = $conn->prepare($sql_inventory)){
    $stmt_inv->bind_param("i", $hospital_id);
    $stmt_inv->execute();
    $result = $stmt_inv->get_result();
    while($row = $result->fetch_assoc()){
        $blood_units[$row['blood_group']] = $row['units'];
    }
    $stmt_inv->close();
}

// --- Fetch Scheduled Donations ---
$sql_donations = "SELECT d.donation_id, u.user_id as donor_id, u.full_name, u.blood_group, d.donation_date 
                  FROM donations d 
                  JOIN users u ON d.donor_id = u.user_id 
                  WHERE d.hospital_id = ? AND d.status = 'Scheduled'
                  ORDER BY d.donation_date ASC";
if($stmt_don = $conn->prepare($sql_donations)){
    $stmt_don->bind_param("i", $hospital_id);
    $stmt_don->execute();
    $result_don = $stmt_don->get_result();
    while($row = $result_don->fetch_assoc()){
        $scheduled_donations[] = $row;
    }
    $stmt_don->close();
}

$blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Dashboard - LifeLink Connect</title>
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
                <i class="fas fa-hospital-symbol text-3xl mr-2 text-red-500"></i>
                LifeLink
            </a>
            <nav>
                <a href="hospital_dashboard.php" class="flex items-center p-3 text-lg bg-red-500 rounded-lg">
                    <i class="fas fa-tachometer-alt w-6"></i><span class="ml-4">Dashboard</span>
                </a>
                <a href="hospital_profile.php" class="flex items-center p-3 mt-4 text-lg hover:bg-gray-700 rounded-lg">
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
                <h1 class="text-3xl font-bold text-gray-800">Hospital Dashboard</h1>
                <p class="font-semibold text-lg">Welcome, <?php echo htmlspecialchars($full_name); ?>!</p>
            </header>

            <?php if ($update_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo $update_message; ?></span>
            </div>
            <?php endif; ?>

            <!-- Blood Inventory -->
            <div class="bg-white p-8 rounded-xl shadow-lg mb-8">
                <h2 class="text-2xl font-bold text-gray-700 mb-6">Blood Inventory Management</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
                    <?php foreach($blood_groups as $group): ?>
                    <div class="bg-gray-50 p-4 rounded-lg text-center">
                        <p class="font-bold text-2xl text-red-600"><?php echo $group; ?></p>
                        <p class="text-gray-600"><?php echo $blood_units[$group] ?? 0; ?> Units</p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <!-- Update Form -->
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="mt-6 flex items-end gap-4">
                    <div>
                        <label for="blood_group" class="block text-sm font-medium text-gray-700">Blood Group</label>
                        <select name="blood_group" id="blood_group" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                            <?php foreach($blood_groups as $group): ?>
                            <option value="<?php echo $group; ?>"><?php echo $group; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                         <label for="units" class="block text-sm font-medium text-gray-700">Number of Units</label>
                        <input type="number" name="units" id="units" min="0" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                    </div>
                    <button type="submit" name="update_units" class="px-5 py-2 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none">
                        Update
                    </button>
                </form>
            </div>

            <!-- Scheduled Donations -->
            <div class="bg-white p-8 rounded-xl shadow-lg">
                <h2 class="text-2xl font-bold text-gray-700 mb-6">Scheduled Donations</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                         <thead class="bg-gray-200 text-gray-600">
                            <tr>
                                <th class="py-3 px-6 text-left">Donor Name</th>
                                <th class="py-3 px-6 text-left">Blood Group</th>
                                <th class="py-3 px-6 text-left">Date & Time</th>
                                <th class="py-3 px-6 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700">
                           <?php if (empty($scheduled_donations)): ?>
                                <tr>
                                    <td colspan="4" class="py-4 px-6 text-center text-gray-500">No donations currently scheduled.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($scheduled_donations as $donation): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="py-4 px-6"><?php echo htmlspecialchars($donation['full_name']); ?></td>
                                    <td class="py-4 px-6 font-semibold text-red-600"><?php echo htmlspecialchars($donation['blood_group']); ?></td>
                                    <td class="py-4 px-6"><?php echo date("d M Y, h:i A", strtotime($donation['donation_date'])); ?></td>
                                    <td class="py-4 px-6 text-center">
                                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="inline-flex gap-2">
                                            <input type="hidden" name="donation_id" value="<?php echo $donation['donation_id']; ?>">
                                            <input type="hidden" name="donor_id" value="<?php echo $donation['donor_id']; ?>">
                                            <input type="hidden" name="blood_group" value="<?php echo $donation['blood_group']; ?>">
                                            <button type="submit" name="action" value="complete" class="px-3 py-1 text-sm font-medium text-white bg-green-500 hover:bg-green-600 rounded-md">Complete</button>
                                            <button type="submit" name="action" value="cancel" class="px-3 py-1 text-sm font-medium text-white bg-red-500 hover:bg-red-600 rounded-md">Cancel</button>
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

