<?php

require_once 'config.php';
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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar h1 {
            font-size: 24px;
        }
        
        .back-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
        }
        
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .form-card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h2 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .error {
            background: #fee;
            color: #c33;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }
        
        .btn {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .required {
            color: red;
        }
        

        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
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