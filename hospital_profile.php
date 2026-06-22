<?php
require_once 'db_connect.php';

// Check if user is logged in and is a hospital
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["user_type"] !== 'hospital') {
    header("location: login.php");
    exit;
}

$hospital_id = $_SESSION["user_id"];
$full_name = $_SESSION["full_name"];
$success_message = "";
$error_message = "";
$hospital_info = [];

// --- Handle Form Submission for Profile Update ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve POST data
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if (empty($name) || empty($email) || empty($phone) || empty($address)) {
        $error_message = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $error_message = "The new passwords do not match.";
    } else {
        // Start transaction
        $conn->begin_transaction();

        try {
            // Update hospitals table
            $sql_hospital = "UPDATE hospitals SET name = ?, address = ?, phone = ? WHERE hospital_id = ?";
            $stmt_hospital = $conn->prepare($sql_hospital);
            $stmt_hospital->bind_param("sssi", $name, $address, $phone, $hospital_id);
            $stmt_hospital->execute();

            // Update users table (email)
            $sql_user = "UPDATE users SET email = ?, full_name = ? WHERE user_id = ?";
            $stmt_user = $conn->prepare($sql_user);
            $stmt_user->bind_param("ssi", $email, $name, $hospital_id);
            $stmt_user->execute();
            
            // Update password if provided
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql_pass = "UPDATE users SET password = ? WHERE user_id = ?";
                $stmt_pass = $conn->prepare($sql_pass);
                $stmt_pass->bind_param("si", $hashed_password, $hospital_id);
                $stmt_pass->execute();
            }

            // Commit transaction
            $conn->commit();

            // Update session variable
            $_SESSION['full_name'] = $name;
            $success_message = "Profile updated successfully!";

        } catch (mysqli_sql_exception $exception) {
            $conn->rollback();
            $error_message = "An error occurred. Please try again. " . $exception->getMessage();
        }
    }
}


// --- Fetch Hospital's Information ---
$sql_details = "SELECT h.name, h.address, h.phone, u.email FROM hospitals h JOIN users u ON h.hospital_id = u.user_id WHERE h.hospital_id = ?";
if($stmt_details = $conn->prepare($sql_details)){
    $stmt_details->bind_param("i", $hospital_id);
    $stmt_details->execute();
    $result = $stmt_details->get_result();
    $hospital_info = $result->fetch_assoc();
    $stmt_details->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Profile - LifeFlow Connect</title>
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
                LifeFlow
            </a>
            <nav>
                <a href="hospital_dashboard.php" class="flex items-center p-3 text-lg hover:bg-gray-700 rounded-lg">
                    <i class="fas fa-tachometer-alt w-6"></i><span class="ml-4">Dashboard</span>
                </a>
                <a href="hospital_profile.php" class="flex items-center p-3 mt-4 text-lg bg-red-500 rounded-lg">
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
                <h1 class="text-3xl font-bold text-gray-800">Manage Profile</h1>
                <p class="font-semibold text-lg"><?php echo htmlspecialchars($full_name); ?></p>
            </header>
            
            <?php if ($success_message): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p class="font-bold">Success</p>
                <p><?php echo $success_message; ?></p>
            </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p class="font-bold">Error</p>
                <p><?php echo $error_message; ?></p>
            </div>
            <?php endif; ?>

            <div class="bg-white p-8 rounded-xl shadow-lg max-w-4xl mx-auto">
                <h2 class="text-2xl font-bold text-gray-700 mb-6">Hospital Information</h2>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Full Name -->
                        <div>
                            <label for="full_name" class="block text-sm font-medium text-gray-700">Hospital Name</label>
                            <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($hospital_info['name'] ?? ''); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500" required>
                        </div>
                        <!-- Email Address -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Contact Email</label>
                            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($hospital_info['email'] ?? ''); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500" required>
                        </div>
                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($hospital_info['phone'] ?? ''); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500" required>
                        </div>
                        <!-- Address -->
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                            <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($hospital_info['address'] ?? ''); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500" required>
                        </div>
                        <!-- New Password -->
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700">New Password (optional)</label>
                            <input type="password" name="new_password" id="new_password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                        </div>
                        <!-- Confirm New Password -->
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</slabel>
                            <input type="password" name="confirm_password" id="confirm_password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                        </div>
                    </div>
                    <div class="mt-8 text-right">
                        <button type="submit" class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                           <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

</body>
</html>
