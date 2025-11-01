<?php
require_once '/../config/config.php';

$success = '';
$error = '';

if (isset($_POST['submit_contact'])) {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $subject = sanitize_input($_POST['subject']);
    $message = sanitize_input($_POST['message']);
    
    // Validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Please fill in all required fields";
    } elseif (!validate_email($email)) {
        $error = "Please enter a valid email address";
    } else {
        // Insert into database
        $sql = "INSERT INTO contact_messages (name, email, phone, subject, message, created_at) 
                VALUES ('$name', '$email', '$phone', '$subject', '$message', NOW())";
        
        if (mysqli_query($conn, $sql)) {
            $success = "Thank you for contacting us! We'll get back to you soon.";
            // Clear form
            $_POST = array();
        } else {
            $error = "Something went wrong. Please try again later.";
        }
    }
}

// Display messages
if ($success) {
    echo '<div class="success-message">' . $success . '</div>';
}
if ($error) {
    echo '<div class="error-message">' . $error . '</div>';
}
?>