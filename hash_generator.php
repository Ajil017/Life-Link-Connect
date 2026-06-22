<?php
// --- PASSWORD HASH GENERATOR ---

// 1. Enter the new password you want to use inside the quotes below.
$new_password_to_hash = "admin123";

// 2. Save this file.

// 3. Open your browser and go to http://localhost/lifeflow-connect/hash_generator.php

// 4. Copy the long string of text that appears on the screen. That is your new hashed password.

// --- Do not change anything below this line ---

if (!empty($new_password_to_hash) && $new_password_to_hash !== "yourNewSecurePassword") {
    echo "<h1>Your New Hashed Password:</h1>";
    echo "<p>Copy this entire line and paste it into the 'password' field in phpMyAdmin:</p>";
    echo "<hr>";
    echo "<strong>" . password_hash($new_password_to_hash, PASSWORD_DEFAULT) . "</strong>";
    echo "<hr>";
} else {
    echo "<h1>Instructions</h1>";
    echo "<p>Please open this file (hash_generator.php) in your code editor, change the value of the <strong>\$new_password_to_hash</strong> variable on line 6, and then refresh this page.</p>";
}

?>
