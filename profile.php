<?php
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$success_message = "";
$error_message = "";
$user_data = [];

// --- Handle Form Submission for Profile Update ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    // Basic validation
    if (empty($full_name) || empty($email) || empty($phone)) {
        $error_message = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        // Prepare an update statement
        $sql_update = "UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE user_id = ?";
        
        if ($stmt = $conn->prepare($sql_update)) {
            $stmt->bind_param("ssssi", $full_name, $email, $phone, $address, $user_id);
            
            if ($stmt->execute()) {
                $success_message = "Profile updated successfully!";
                // IMPORTANT: Update session variable to reflect name change immediately
                $_SESSION["full_name"] = $full_name;
            } else {
                $error_message = "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
}


// --- Fetch Current User Data ---
$sql_user = "SELECT full_name, email, phone, address, blood_group, user_type FROM users WHERE user_id = ?";
if ($stmt_user = $conn->prepare($sql_user)) {
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result = $stmt_user->get_result();
    $user_data = $result->fetch_assoc();
    $stmt_user->close();
}

if(empty($user_data)){
    // Handle case where user data isn't found, though unlikely if logged in
    header("location: logout.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - LifeLink Connect</title>
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
                LifeLink
            </a>
            <nav>
                 <a href="dashboard_redirect.php" class="flex items-center p-3 mt-4 text-lg hover:bg-gray-700 rounded-lg">
                    <i class="fas fa-tachometer-alt w-6"></i><span class="ml-4">Dashboard</span>
                </a>
                <a href="profile.php" class="flex items-center p-3 text-lg bg-red-500 rounded-lg">
                    <i class="fas fa-user-circle w-6"></i><span class="ml-4">Profile</span>
                </a>
                <?php if($_SESSION["user_type"] === 'donor'): ?>
                <a href="leaderboard.php" class="flex items-center p-3 mt-4 text-lg hover:bg-gray-700 rounded-lg">
                    <i class="fas fa-trophy w-6"></i><span class="ml-4">Leaderboard</span>
                </a>
                <?php endif; ?>
                <a href="logout.php" class="flex items-center p-3 mt-10 text-lg hover:bg-red-600 rounded-lg">
                    <i class="fas fa-sign-out-alt w-6"></i><span class="ml-4">Logout</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <main class="flex-1 p-10 overflow-y-auto">
            <header class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">My Profile</h1>
                <p class="text-gray-500">View and update your personal information.</p>
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

            <div class="bg-white p-8 rounded-xl shadow-lg max-w-2xl mx-auto">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Full Name -->
                        <div>
                            <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($user_data['full_name']); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                        </div>
                        <!-- Email Address -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                        </div>
                         <!-- Phone Number -->
                         <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <input type="tel" name="phone" id="phone" value="<?php echo htmlspecialchars($user_data['phone']); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                        </div>
                        <!-- Address -->
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                            <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($user_data['address']); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                        </div>
                        <!-- Blood Group (Read-only) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Blood Group</label>
                            <p class="mt-1 block w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm"><?php echo htmlspecialchars($user_data['blood_group']); ?></p>
                        </div>
                        <!-- User Type (Read-only) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Role</label>
                             <p class="mt-1 block w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md shadow-sm capitalize"><?php echo htmlspecialchars($user_data['user_type']); ?></p>
                        </div>
                    </div>
                    <div class="mt-8 text-right">
                        <button type="submit" class="px-8 py-2.5 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                           <i class="fas fa-save mr-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

</body>
</html>
