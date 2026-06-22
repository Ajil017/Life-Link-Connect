<?php
// Include the database connection file
require_once 'db_connect.php';

// Initialize variables
$email = $password = "";
$error = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if email is empty
    if (empty(trim($_POST["email"]))) {
        $error = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Check if password is empty
    if (empty(trim($_POST["password"]))) {
        $error = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate credentials if no errors
    if (empty($error)) {
        $sql = "SELECT user_id, full_name, email, password, user_type FROM users WHERE email = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = $email;

            if ($stmt->execute()) {
                $stmt->store_result();

                // Check if email exists, if yes then verify password
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($user_id, $full_name, $db_email, $hashed_password, $user_type);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            session_start();

                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $user_id;
                            $_SESSION["full_name"] = $full_name;
                            $_SESSION["user_type"] = $user_type;

                            // Redirect user to their respective dashboard
                            header("location: dashboard_redirect.php");
                            exit();
                        } else {
                            // Password is not valid
                            $error = "The password you entered was not valid.";
                        }
                    }
                } else {
                    // Email doesn't exist
                    $error = "No account found with that email.";
                }
            } else {
                $error = "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LifeFlow Connect</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-lg">
        <div class="text-center mb-8">
            <a href="index.html" class="text-3xl font-bold text-red-600 flex items-center justify-center">
                <i class="fas fa-heartbeat text-4xl mr-2"></i>
                LifeFlow Connect
            </a>
            <h2 class="text-2xl font-bold text-gray-800 mt-4">Welcome Back!</h2>
            <p class="text-gray-500">Sign in to continue to your dashboard.</p>
        </div>

        <?php 
        if(!empty($error)){
            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">';
            echo '<span class="block sm:inline">' . $error . '</span>';
            echo '</div>';
        }        
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" name="email" id="email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500" value="<?php echo $email; ?>">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-red-500 focus:border-red-500">
            </div>

            <div>
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Sign In
                </button>
            </div>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600">
            Don't have an account? 
            <a href="register.php" class="font-medium text-red-600 hover:text-red-500">
                Register now
            </a>
        </p>
    </div>

</body>
</html>
