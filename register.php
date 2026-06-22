<?php
require_once 'db_connect.php';

$full_name = $email = $password = $phone = $address = $blood_group = $user_type = "";
$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve POST data
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $blood_group = $_POST['blood_group'] ?? 'N/A'; // Default for hospitals
    $user_type = $_POST['user_type'];
    
    // Basic Validation
    if (empty($full_name) || empty($email) || empty($password) || empty($user_type)) {
        $error_message = "Please fill in all required fields.";
    } elseif ($user_type === 'hospital' && (empty($address) || empty($phone))) {
        $error_message = "Hospitals must provide a phone number and address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        // Check if email already exists
        $sql_check = "SELECT user_id FROM users WHERE email = ?";
        if ($stmt_check = $conn->prepare($sql_check)) {
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $stmt_check->store_result();
            
            if ($stmt_check->num_rows > 0) {
                $error_message = "This email is already registered.";
            } else {
                // Hashing the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // --- Handle different registration types ---
                if ($user_type === 'hospital') {
                    // --- Hospital Registration Logic ---
                    $conn->begin_transaction();
                    try {
                        // 1. Insert into users table FIRST to get a valid user_id
                        $hospital_blood_group = 'N/A';
                        $sql_user = "INSERT INTO users (full_name, email, password, phone, address, blood_group, user_type) VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $stmt_user = $conn->prepare($sql_user);
                        $stmt_user->bind_param("sssssss", $full_name, $email, $hashed_password, $phone, $address, $hospital_blood_group, $user_type);
                        $stmt_user->execute();

                        // Get the new user_id that was auto-generated
                        $new_hospital_id = $conn->insert_id;

                        // 2. Now insert into hospitals table, using the new user_id as the hospital_id
                        $sql_hospital = "INSERT INTO hospitals (hospital_id, name, email, address, phone) VALUES (?, ?, ?, ?, ?)";
                        $stmt_hospital = $conn->prepare($sql_hospital);
                        // Bind email along with other details
                        $stmt_hospital->bind_param("issss", $new_hospital_id, $full_name, $email, $address, $phone);
                        $stmt_hospital->execute();
                        
                        // 3. Initialize blood units for the new hospital
                        $blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                        // CORRECTED: The column name is 'quantity', not 'units'.
                        $sql_units = "INSERT INTO blood_units (hospital_id, blood_group, quantity) VALUES (?, ?, 0)";
                        $stmt_units = $conn->prepare($sql_units);
                        foreach($blood_groups as $group){
                            $stmt_units->bind_param("is", $new_hospital_id, $group);
                            $stmt_units->execute();
                        }
                        
                        $conn->commit();
                        $success_message = "Hospital registered successfully! You can now log in.";

                    } catch (mysqli_sql_exception $exception) {
                        $conn->rollback();
                        // Provide a more detailed error for debugging
                        $error_message = "Something went wrong during hospital registration. Please try again. Error: " . $exception->getMessage();
                    }

                } else {
                    // --- Donor/Receiver Registration Logic ---
                    $sql_insert = "INSERT INTO users (full_name, email, password, phone, address, blood_group, user_type) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    if ($stmt_insert = $conn->prepare($sql_insert)) {
                        $stmt_insert->bind_param("sssssss", $full_name, $email, $hashed_password, $phone, $address, $blood_group, $user_type);
                        if ($stmt_insert->execute()) {
                            $success_message = "Registration successful! You can now log in.";
                        } else {
                            $error_message = "Something went wrong. Please try again.";
                        }
                        $stmt_insert->close();
                    }
                }
            }
            $stmt_check->close();
        }
    }
    if ($conn->connect_errno === 0) {
       $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - LifeFlow Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
    <script>
        function toggleBloodGroup() {
            const userType = document.getElementById('user_type').value;
            const bloodGroupDiv = document.getElementById('blood_group_div');
            const phoneInput = document.getElementById('phone');
            const addressInput = document.getElementById('address');

            if (userType === 'hospital') {
                bloodGroupDiv.style.display = 'none';
                phoneInput.required = true;
                addressInput.required = true;
            } else {
                bloodGroupDiv.style.display = 'block';
                phoneInput.required = false;
                addressInput.required = false;
            }
        }
    </script>
</head>
<body class="bg-gray-100">

    <div class="flex items-center justify-center min-h-screen">
        <div class="w-full max-w-lg p-8 space-y-6 bg-white rounded-xl shadow-lg">
            
            <div class="text-center">
                <a href="index.html" class="inline-flex items-center justify-center text-2xl font-bold text-gray-800">
                    <i class="fas fa-heartbeat text-3xl mr-2 text-red-500"></i>
                    LifeFlow Connect
                </a>
                <p class="mt-2 text-sm text-gray-600">Create your account to save lives</p>
            </div>

            <?php if(!empty($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo $error_message; ?></span>
                </div>
            <?php endif; ?>
            
            <?php if(!empty($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <p><?php echo $success_message; ?></p>
                    <div class="text-center mt-4">
                         <a href="login.php" class="font-bold text-white bg-red-600 hover:bg-red-700 py-2 px-4 rounded-md">Proceed to Login</a>
                    </div>
                </div>
            <?php else: ?>

            <form class="space-y-6" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="user_type" class="block text-sm font-medium text-gray-700">Register as</label>
                        <select id="user_type" name="user_type" onchange="toggleBloodGroup()" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                            <option value="donor">Donor</option>
                            <option value="receiver">Receiver</option>
                            <option value="hospital">Hospital</option>
                        </select>
                    </div>
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name / Hospital Name</label>
                        <input id="full_name" name="full_name" type="text" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                     <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input id="phone" name="phone" type="tel" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                    </div>
                    <div id="blood_group_div">
                        <label for="blood_group" class="block text-sm font-medium text-gray-700">Blood Group</label>
                         <select id="blood_group" name="blood_group" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
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
                </div>
                 <div>
                    <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                    <input id="address" name="address" type="text" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
                </div>


                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Create Account
                    </button>
                </div>
            </form>
            <?php endif; ?>

            <p class="mt-4 text-center text-sm text-gray-600">
                Already a member?
                <a href="login.php" class="font-medium text-red-600 hover:text-red-500">
                    Sign in
                </a>
            </p>
        </div>
    </div>
    <script>
        // Initial check on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleBloodGroup();
        });
    </script>
</body>
</html>

