<?php
require_once '../config/config.php';

// Delete existing admin
mysqli_query($conn, "DELETE FROM admin WHERE username = 'admin'");

// Create new admin
$username = 'admin';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$email = 'admin@portal.edu';

$sql = "INSERT INTO admin (username, password, email) VALUES ('$username', '$password', '$email')";
$success = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin - Admission Portal</title>
    <link href="../src/output.css" rel="stylesheet">
</head>
<body class="bg-gray-900 min-h-screen">
    <?php include "../includes/Navbar.php" ?>

    <div class="container mx-auto px-4 pt-24">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-xl overflow-hidden">
            <div class="px-6 py-8">
                <?php if ($success): ?>
                    <div class="text-center">
                        <div class="flex items-center justify-center mb-4">
                            <svg class="h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">Admin Created Successfully!</h2>
                        <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                            <div class="mb-2">
                                <span class="font-semibold text-gray-700">Username:</span>
                                <span class="text-gray-900 ml-2">admin</span>
                            </div>
                            <div>
                                <span class="font-semibold text-gray-700">Password:</span>
                                <span class="text-gray-900 ml-2">admin123</span>
                            </div>
                        </div>
                        <a href="admin_login.php" 
                           class="inline-block bg-red-600 text-white px-6 py-2 rounded-md hover:bg-red-700 transition-colors">
                            Go to Admin Login
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <div class="flex items-center justify-center mb-4">
                            <svg class="h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">Error Creating Admin</h2>
                        <div class="text-red-600">
                            <?php echo mysqli_error($conn); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include "../includes/Footer.php" ?>
</body>
</html>