<?php

require_once '../config/config.php';
require_login();

$student_id = $_SESSION['student_id'];

// Check if already applied
$check = "SELECT id FROM application WHERE student_id = $student_id";
$result = mysqli_query($conn, $check);
if (mysqli_num_rows($result) > 0) {
    header("Location: student_dashboard.php");
    exit();
}

$errors = [];
$success = '';

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
    
    if (empty($father_name)) {
        $errors[] = "Father's name is required";
    }
    
    if (empty($dob)) {
        $errors[] = "Date of birth is required";
    }
    
    // Calculate merit score
    $merit_score = (0.5 * $matric_marks / 11) + (0.5 * $inter_marks / 11);
    
    if (empty($errors)) {
        $sql = "INSERT INTO application (student_id, matric_marks, inter_marks, program_choice, father_name, date_of_birth, merit_score) 
                VALUES ($student_id, $matric_marks, $inter_marks, '$program_choice', '$father_name', '$dob', $merit_score)";
        
        if (mysqli_query($conn, $sql)) {
            header("Location: student_dashboard.php?success=application_submitted");
            exit();
        } else {
            $errors[] = "Application submission failed: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Admission - Admission Portal</title>
        <link rel="stylesheet" href="../assets//css//studentcss//admission-apply.css">

  
</head>
<body>
    <div class="navbar">
        <h1> Admission Portal</h1>
        <a href="student_dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>
    
    <div class="container">
        <div class="form-card">
            <h2>Apply for Admission</h2>
            <p class="subtitle">Fill in your academic details to apply</p>
            
          
            
            <?php if (!empty($errors)): ?>
                <div class="error">
                    <?php foreach ($errors as $error): ?>
                        <p>• <?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="applicationForm">
                <div class="form-group">
                    <label>Father's Name <span class="required">*</span></label>
                    <input type="text" name="father_name" required 
                           value="<?php echo isset($_POST['father_name']) ? htmlspecialchars($_POST['father_name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Date of Birth <span class="required">*</span></label>
                    <input type="date" name="dob" required max="<?php echo date('Y-m-d', strtotime('-15 years')); ?>"
                           value="<?php echo isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : ''; ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Matric Marks (out of 1100) <span class="required">*</span></label>
                        <input type="number" name="matric_marks" required min="0" max="1100" step="0.01"
                               value="<?php echo isset($_POST['matric_marks']) ? htmlspecialchars($_POST['matric_marks']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Intermediate Marks (out of 1100) <span class="required">*</span></label>
                        <input type="number" name="inter_marks" required min="0" max="1100" step="0.01"
                               value="<?php echo isset($_POST['inter_marks']) ? htmlspecialchars($_POST['inter_marks']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Program Choice <span class="required">*</span></label>
                    <select name="program_choice" required>
                        <option value="">Select a program</option>
                        <option value="Computer Science" <?php echo (isset($_POST['program_choice']) && $_POST['program_choice'] == 'Computer Science') ? 'selected' : ''; ?>>Computer Science</option>
                        <option value="Software Engineering" <?php echo (isset($_POST['program_choice']) && $_POST['program_choice'] == 'Software Engineering') ? 'selected' : ''; ?>>Software Engineering</option>
                        <option value="Electrical Engineering" <?php echo (isset($_POST['program_choice']) && $_POST['program_choice'] == 'Electrical Engineering') ? 'selected' : ''; ?>>Electrical Engineering</option>
                        <option value="Mechanical Engineering" <?php echo (isset($_POST['program_choice']) && $_POST['program_choice'] == 'Mechanical Engineering') ? 'selected' : ''; ?>>Mechanical Engineering</option>
                        <option value="Business Administration" <?php echo (isset($_POST['program_choice']) && $_POST['program_choice'] == 'Business Administration') ? 'selected' : ''; ?>>Business Administration</option>
                        <option value="Mathematics" <?php echo (isset($_POST['program_choice']) && $_POST['program_choice'] == 'Mathematics') ? 'selected' : ''; ?>>Mathematics</option>
                    </select>
                </div>
                
                <button type="submit" class="btn">Submit Application</button>
            </form>
        </div>
    </div>
    
    <script>
        document.getElementById('applicationForm').addEventListener('submit', function(e) {
            const matricMarks = parseFloat(document.querySelector('input[name="matric_marks"]').value);
            const interMarks = parseFloat(document.querySelector('input[name="inter_marks"]').value);
            
            if (matricMarks < 0 || matricMarks > 1100) {
                e.preventDefault();
                alert('Matric marks must be between 0 and 1100');
                return;
            }
            
            if (interMarks < 0 || interMarks > 1100) {
                e.preventDefault();
                alert('Intermediate marks must be between 0 and 1100');
                return;
            }
        });
    </script>
</body>
</html>