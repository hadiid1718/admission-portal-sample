<?php

require_once '../config/config.php';
require_login();

$student_id = $_SESSION['student_id'];

// Get existing application
$sql = "SELECT * FROM application WHERE student_id = $student_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: apply_admission.php");
    exit();
}

$application = mysqli_fetch_assoc($result);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $matric_marks = floatval($_POST['matric_marks']);
    $inter_marks = floatval($_POST['inter_marks']);
    $program_choice = sanitize_input($_POST['program_choice']);
    $father_name = sanitize_input($_POST['father_name']);
    $dob = sanitize_input($_POST['dob']);
    
    // Validation
    if ($matric_marks <= 0 || $matric_marks > 1100) {
        $errors[] = "Matric marks must be between 0 and 1100";
    }
    
    if ($inter_marks <= 0 || $inter_marks > 1100) {
        $errors[] = "Intermediate marks must be between 0 and 1100";
    }
    
    if (empty($program_choice)) {
        $errors[] = "Please select a program";
    }
    
    // Calculate merit score
    $merit_score = (0.5 * $matric_marks / 11) + (0.5 * $inter_marks / 11);
    
    if (empty($errors)) {
        $update_sql = "UPDATE application SET 
                       matric_marks = $matric_marks,
                       inter_marks = $inter_marks,
                       program_choice = '$program_choice',
                       father_name = '$father_name',
                       date_of_birth = '$dob',
                       merit_score = $merit_score
                       WHERE student_id = $student_id";
        
        if (mysqli_query($conn, $update_sql)) {
            header("Location: student_dashboard.php?success=application_updated");
            exit();
        } else {
            $errors[] = "Update failed: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Application - Admission Portal</title>
    <link rel="stylesheet" href="../assets/css/studentcss/update-application.css">

</head>
<body>
    <div class="navbar">
        <h1>🎓 Admission Portal</h1>
        <a href="student_dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>
    
    <div class="container">
        <div class="form-card">
            <h2>Update Your Application</h2>
            <p class="subtitle">Modify your application details</p>
            
            <div class="warning-box">
                <strong>Note:</strong> Any changes you make will recalculate your merit score. Make sure all information is accurate before updating.
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="error">
                    <?php foreach ($errors as $error): ?>
                        <p>• <?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="updateForm">
                <div class="form-group">
                    <label>Father's Name <span class="required">*</span></label>
                    <input type="text" name="father_name" required 
                           value="<?php echo htmlspecialchars($application['father_name']); ?>">
                </div>
                
                <div class="form-group">
                    <label>Date of Birth <span class="required">*</span></label>
                    <input type="date" name="dob" required 
                           value="<?php echo htmlspecialchars($application['date_of_birth']); ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Matric Marks (out of 1100) <span class="required">*</span></label>
                        <input type="number" name="matric_marks" required min="0" max="1100" step="0.01"
                               value="<?php echo htmlspecialchars($application['matric_marks']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Intermediate Marks (out of 1100) <span class="required">*</span></label>
                        <input type="number" name="inter_marks" required min="0" max="1100" step="0.01"
                               value="<?php echo htmlspecialchars($application['inter_marks']); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Program Choice <span class="required">*</span></label>
                    <select name="program_choice" required>
                        <option value="">Select a program</option>
                        <option value="Computer Science" <?php echo $application['program_choice'] == 'Computer Science' ? 'selected' : ''; ?>>Computer Science</option>
                        <option value="Software Engineering" <?php echo $application['program_choice'] == 'Software Engineering' ? 'selected' : ''; ?>>Software Engineering</option>
                        <option value="Electrical Engineering" <?php echo $application['program_choice'] == 'Electrical Engineering' ? 'selected' : ''; ?>>Electrical Engineering</option>
                        <option value="Mechanical Engineering" <?php echo $application['program_choice'] == 'Mechanical Engineering' ? 'selected' : ''; ?>>Mechanical Engineering</option>
                        <option value="Business Administration" <?php echo $application['program_choice'] == 'Business Administration' ? 'selected' : ''; ?>>Business Administration</option>
                        <option value="Mathematics" <?php echo $application['program_choice'] == 'Mathematics' ? 'selected' : ''; ?>>Mathematics</option>
                    </select>
                </div>
                
                <div>
                    <button type="submit" class="btn">Update Application</button>
                    <a href="student_dashboard.php" class="btn btn-cancel" style="text-decoration: none; display: inline-block;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>