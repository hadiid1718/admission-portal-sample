<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      
define('DB_PASS', '');
define('DB_NAME', 'admission_portal');
define('DB_PORT', 3307);  // Added port configuration

// Create connection with port 3307
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8mb4");  // Changed to utf8mb4 for better Unicode support

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Helper function for sanitizing input
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

// Helper function to validate email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Helper function to validate CNIC
function validate_cnic($cnic) {
    return preg_match('/^[0-9]{5}-[0-9]{7}-[0-9]$/', $cnic);
}

// Helper function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['student_id']);
}

// Helper function to check if admin is logged in
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']);
}

// Redirect to login if not authenticated
function require_login() {
    if (!is_logged_in()) {
        header("Location: student_login.php");
        exit();
    }
}

// Redirect to admin login if not authenticated
function require_admin() {
    if (!is_admin_logged_in()) {
        header("Location: admin_login.php");
        exit();
    }
}
?>