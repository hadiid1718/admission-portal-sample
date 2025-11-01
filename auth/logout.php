<?php
session_start();

// Check if admin or student
$is_admin = isset($_SESSION['admin_id']);

// Destroy all session data
session_unset();
session_destroy();

// Redirect to appropriate login page
if ($is_admin) {
    header("Location: ../admin/admin_login.php?message=logged_out");
} else {
    header("Location: ../student/student_login.php?message=logged_out");
}
exit();
?>
