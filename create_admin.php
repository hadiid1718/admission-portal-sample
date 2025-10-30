<?php

require_once 'config.php';

// Delete existing admin
mysqli_query($conn, "DELETE FROM admin WHERE username = 'admin'");

// Create new admin
$username = 'admin';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$email = 'admin@portal.edu';

$sql = "INSERT INTO admin (username, password, email) VALUES ('$username', '$password', '$email')";

if (mysqli_query($conn, $sql)) {
    echo "✅ Admin created successfully!<br><br>";
    echo "Username: admin<br>";
    echo "Password: admin123<br><br>";
    echo "<a href='admin_login.php'>Go to Admin Login</a>";
} else {
    echo "❌ Error: " . mysqli_error($conn);
}
?>
