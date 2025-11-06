<?php
require_once '../config/config.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $cnic = sanitize_input($_POST['cnic']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    
    // Validation
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email) || !validate_email($email)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
        if (empty($cnic) || !validate_cnic($cnic)) {
        $errors[] = "Valid CNIC format required (e.g., 12345-1234567-1)";
    }
    
  
    
    // Check if email already exists
    if (empty($errors)) {
        $check_email = "SELECT id FROM student WHERE email = '$email'";
        $result = mysqli_query($conn, $check_email);
        if (mysqli_num_rows($result) > 0) {
            $errors[] = "Email already registered";
        }
    }
        // Check if CNIC already exists
    if (empty($errors)) {
        $check_cnic = "SELECT id FROM student WHERE cnic = '$cnic'";
        $result = mysqli_query($conn, $check_cnic);
        if (mysqli_num_rows($result) > 0) {
            $errors[] = "CNIC already registered";
        }
    }

    

    
    // Insert into database
// Insert into database
if (empty($errors)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO student (name, email, cnic, password) 
            VALUES ('$name', '$email','$cnic' , '$hashed_password')";

    if (mysqli_query($conn, $sql)) {
        $success = "Registration successful! You can now login.";
    } else {
        $errors[] = "Registration failed: " . mysqli_error($conn);
    }
}

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link rel="stylesheet" href="../assets/css/studentcss/student-register.css">
</head>
<body>
    
    <div class="container">
        <div class="register-box">
            <div class="header">
                <h2>Create Student Account</h2>
                <p>Register to apply for admission</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p>• <?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <p><?php echo $success; ?></p>
                </div>
            <?php endif; ?>
                
            <form method="POST" action="" id="registerForm">
                <div class="form-group">
                    <label>Full Name <span class="required">*</span></label>
                    <input type="text" name="name" required 
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Email Address <span class="required">*</span></label>
                    <input type="email" name="email" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>CNIC <span class="required">*</span></label>
                    <input type="text" name="cnic" placeholder="12345-1234567-1" required 
                           pattern="[0-9]{5}-[0-9]{7}-[0-9]"
                           value="<?php echo isset($_POST['cnic']) ? htmlspecialchars($_POST['cnic']) : ''; ?>">
                    <p class="format-hint">Format: 12345-1234567-1</p>
                </div>

                <div class="form-group">
                    <label>Password <span class="required">*</span></label>
                    <input type="password" name="password" required minlength="6">
                    <p class="format-hint">Minimum 6 characters</p>
                </div>
                
                <div class="form-group">
                    <label>Confirm Password <span class="required">*</span></label>
                    <input type="password" name="confirm_password" required minlength="6">
                </div>
                
                <button type="submit">Register</button>
            </form>
            
            <div class="login-link">
                <p>Already have an account? <a href="student_login.php">Login here</a></p>
            </div>
        </div>
    </div>
    
    <script src="../assets/script/student-script/student-register.js"></script>
</body>
</html>