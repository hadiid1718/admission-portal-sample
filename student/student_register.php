<?php
require_once '../config/config.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $cnic = sanitize_input($_POST['cnic']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    
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
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO student (name, email, password, cnic, phone, address) 
                VALUES ('$name', '$email', '$hashed_password', '$cnic', '$phone', '$address')";
        
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
    <title>Student Registration - Admission Portal</title>
    <link rel="stylesheet" href="../assets/css/studentcss/student-register.css">
</head>
<body>
    <div class="container">
        <h2>Create Student Account</h2>
        <p class="subtitle">Register to apply for admission</p>
        
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p>• <?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success">
                <p><?php echo $success; ?></p>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="registerForm">
            <div class="form-group">
                <label>Full Name <span class="required">*</span></label>
                <input type="text" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label>Email Address <span class="required">*</span></label>
                <input type="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label>CNIC <span class="required">*</span></label>
                <input type="text" name="cnic" placeholder="12345-1234567-1" required 
                       pattern="[0-9]{5}-[0-9]{7}-[0-9]" 
                       value="<?php echo isset($_POST['cnic']) ? htmlspecialchars($_POST['cnic']) : ''; ?>">
                <small style="color: #666;">Format: 12345-1234567-1</small>
            </div>
            
            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" placeholder="0300-1234567" 
                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label>Address</label>
                <textarea name="address"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Password <span class="required">*</span></label>
                <input type="password" name="password" required minlength="6">
                <small style="color: #666;">Minimum 6 characters</small>
            </div>
            
            <div class="form-group">
                <label>Confirm Password <span class="required">*</span></label>
                <input type="password" name="confirm_password" required minlength="6">
            </div>
            
            <button type="submit" class="btn">Register</button>
        </form>
        
        <div class="links">
            Already have an account? <a href="student_login.php">Login here</a>
        </div>
    </div>
    
    <script>
        // Client-side validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.querySelector('input[name="password"]').value;
            const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html>