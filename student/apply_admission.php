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
    // Personal Information
    $father_name = sanitize_input($_POST['father_name']);
    $mother_name = sanitize_input($_POST['mother_name']);
    $dob = sanitize_input($_POST['dob']);
    $cnic = sanitize_input($_POST['cnic']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    $city = sanitize_input($_POST['city']);
    $province = sanitize_input($_POST['province']);
    $postal_code = sanitize_input($_POST['postal_code']);
    
    // Academic Information
    $matric_marks = floatval($_POST['matric_marks']);
    $inter_marks = floatval($_POST['inter_marks']);
    $matric_board = sanitize_input($_POST['matric_board']);
    $inter_board = sanitize_input($_POST['inter_board']);
    $matric_year = intval($_POST['matric_year']);
    $inter_year = intval($_POST['inter_year']);
    $program_choice = sanitize_input($_POST['program_choice']);
    
    // Validation
    if (empty($father_name)) {
        $errors[] = "Father's name is required";
    }
    
    if (empty($mother_name)) {
        $errors[] = "Mother's name is required";
    }
    
    if (empty($dob)) {
        $errors[] = "Date of birth is required";
    }
    
    if (empty($cnic) || !preg_match('/^\d{5}-\d{7}-\d{1}$/', $cnic)) {
        $errors[] = "Valid CNIC is required (format: 12345-1234567-1)";
    }
    
    if (empty($phone) || !preg_match('/^03\d{9}$/', $phone)) {
        $errors[] = "Valid phone number is required (format: 03xxxxxxxxx)";
    }
    
    if (empty($address)) {
        $errors[] = "Address is required";
    }
    
    if ($matric_marks <= 0 || $matric_marks > 1100) {
        $errors[] = "Matric marks must be between 0 and 1100";
    }
    
    if ($inter_marks <= 0 || $inter_marks > 1100) {
        $errors[] = "Intermediate marks must be between 0 and 1100";
    }
    
    if (empty($program_choice)) {
        $errors[] = "Please select a program";
    }
    
    // File Upload Handling
    $upload_dir = '../uploads/applications/' . $student_id . '/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    $files_to_upload = [
        'cnic_front' => 'CNIC Front',
        'cnic_back' => 'CNIC Back',
        'matric_result' => 'Matric Result Card',
        'inter_result' => 'Intermediate Result Card',
        'photo' => 'Passport Size Photo'
    ];
    
    $uploaded_files = [];
    
    foreach ($files_to_upload as $field => $label) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
            $file = $_FILES[$field];
            
            // Validate file type
            if (!in_array($file['type'], $allowed_types)) {
                $errors[] = "$label must be an image (JPG, PNG, or GIF)";
                continue;
            }
            
            // Validate file size
            if ($file['size'] > $max_size) {
                $errors[] = "$label must be less than 2MB";
                continue;
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $field . '_' . time() . '.' . $extension;
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $uploaded_files[$field] = $filename;
            } else {
                $errors[] = "Failed to upload $label";
            }
        } else {
            $errors[] = "$label is required";
        }
    }
    
    // Calculate merit score
    $merit_score = (0.5 * $matric_marks / 11) + (0.5 * $inter_marks / 11);
    
    if (empty($errors)) {
        $sql = "INSERT INTO application (
            student_id, father_name, mother_name, date_of_birth, cnic, phone, 
            address, city, province, postal_code,
            matric_marks, inter_marks, matric_board, inter_board, 
            matric_year, inter_year, program_choice, merit_score,
            cnic_front, cnic_back, matric_result, inter_result, photo
        ) VALUES (
            $student_id, 
            '$father_name', '$mother_name', '$dob', '$cnic', '$phone',
            '$address', '$city', '$province', '$postal_code',
            $matric_marks, $inter_marks, '$matric_board', '$inter_board',
            $matric_year, $inter_year, '$program_choice', $merit_score,
            '{$uploaded_files['cnic_front']}', '{$uploaded_files['cnic_back']}',
            '{$uploaded_files['matric_result']}', '{$uploaded_files['inter_result']}',
            '{$uploaded_files['photo']}'
        )";
        
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
    <link rel="stylesheet" href="../assets/css/studentcss/admission-apply.css">
    <style>
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding: 0 20px;
        }
        
        .step {
            flex: 1;
            text-align: center;
            padding: 10px;
            position: relative;
        }
        
        .step::before {
            content: attr(data-step);
            display: block;
            width: 40px;
            height: 40px;
            margin: 0 auto 10px;
            border-radius: 50%;
            background: #ddd;
            line-height: 40px;
            font-weight: bold;
            color: #666;
        }
        
        .step.active::before {
            background: #4CAF50;
            color: white;
        }
        
        .step.completed::before {
            background: #2196F3;
            color: white;
            content: '✓';
        }
        
        .step::after {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #ddd;
            z-index: -1;
        }
        
        .step:last-child::after {
            display: none;
        }
        
        .step.active::after,
        .step.completed::after {
            background: #4CAF50;
        }
        
        .form-step {
            display: none;
        }
        
        .form-step.active {
            display: block;
        }
        
        .form-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .file-upload-wrapper {
            position: relative;
            margin-bottom: 20px;
        }
        
        .file-upload-label {
            display: block;
            padding: 15px;
            background: #f5f5f5;
            border: 2px dashed #ddd;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-upload-label:hover {
            border-color: #4CAF50;
            background: #f9f9f9;
        }
        
        .file-upload-label.has-file {
            border-color: #4CAF50;
            background: #e8f5e9;
        }
        
        .file-preview {
            margin-top: 10px;
            text-align: center;
        }
        
        .file-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .btn-secondary {
            background: #757575;
        }
        
        .btn-secondary:hover {
            background: #616161;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .step span {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>Admission Portal</h1>
        <a href="student_dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>
    
    <div class="container">
        <div class="form-card">
            <h2>Apply for Admission</h2>
            <p class="subtitle">Complete all steps to submit your application</p>
            
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step active" data-step="1">
                    <span>Personal Info</span>
                </div>
                <div class="step" data-step="2">
                    <span>Academic Info</span>
                </div>
                <div class="step" data-step="3">
                    <span>Documents</span>
                </div>
                <div class="step" data-step="4">
                    <span>Review</span>
                </div>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="error">
                    <?php foreach ($errors as $error): ?>
                        <p>• <?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="applicationForm" enctype="multipart/form-data">
                <!-- Step 1: Personal Information -->
                <div class="form-step active" data-step="1">
                    <h3>Personal Information</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Father's Name <span class="required">*</span></label>
                            <input type="text" name="father_name" required 
                                   value="<?php echo isset($_POST['father_name']) ? htmlspecialchars($_POST['father_name']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Mother's Name <span class="required">*</span></label>
                            <input type="text" name="mother_name" required 
                                   value="<?php echo isset($_POST['mother_name']) ? htmlspecialchars($_POST['mother_name']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Date of Birth <span class="required">*</span></label>
                            <input type="date" name="dob" required max="<?php echo date('Y-m-d', strtotime('-15 years')); ?>"
                                   value="<?php echo isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>CNIC <span class="required">*</span></label>
                            <input type="text" name="cnic" required pattern="\d{5}-\d{7}-\d{1}" 
                                   placeholder="12345-1234567-1"
                                   value="<?php echo isset($_POST['cnic']) ? htmlspecialchars($_POST['cnic']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Phone Number <span class="required">*</span></label>
                        <input type="tel" name="phone" required pattern="03\d{9}" 
                               placeholder="03xxxxxxxxx"
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Address <span class="required">*</span></label>
                        <textarea name="address" required rows="3"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>City <span class="required">*</span></label>
                            <input type="text" name="city" required 
                                   value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Province <span class="required">*</span></label>
                            <select name="province" required>
                                <option value="">Select Province</option>
                                <option value="Punjab" <?php echo (isset($_POST['province']) && $_POST['province'] == 'Punjab') ? 'selected' : ''; ?>>Punjab</option>
                                <option value="Sindh" <?php echo (isset($_POST['province']) && $_POST['province'] == 'Sindh') ? 'selected' : ''; ?>>Sindh</option>
                                <option value="KPK" <?php echo (isset($_POST['province']) && $_POST['province'] == 'KPK') ? 'selected' : ''; ?>>KPK</option>
                                <option value="Balochistan" <?php echo (isset($_POST['province']) && $_POST['province'] == 'Balochistan') ? 'selected' : ''; ?>>Balochistan</option>
                                <option value="Islamabad" <?php echo (isset($_POST['province']) && $_POST['province'] == 'Islamabad') ? 'selected' : ''; ?>>Islamabad</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Postal Code</label>
                        <input type="text" name="postal_code" pattern="\d{5}" placeholder="12345"
                               value="<?php echo isset($_POST['postal_code']) ? htmlspecialchars($_POST['postal_code']) : ''; ?>">
                    </div>
                </div>
                
                <!-- Step 2: Academic Information -->
                <div class="form-step" data-step="2">
                    <h3>Academic Information</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Matric Marks (out of 1100) <span class="required">*</span></label>
                            <input type="number" name="matric_marks" required min="0" max="1100" step="1"
                                   value="<?php echo isset($_POST['matric_marks']) ? htmlspecialchars($_POST['matric_marks']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Matric Board <span class="required">*</span></label>
                            <input type="text" name="matric_board" required 
                                   placeholder="e.g., BISE Rawalpindi"
                                   value="<?php echo isset($_POST['matric_board']) ? htmlspecialchars($_POST['matric_board']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Matric Passing Year <span class="required">*</span></label>
                        <input type="number" name="matric_year" required min="2000" max="<?php echo date('Y'); ?>"
                               value="<?php echo isset($_POST['matric_year']) ? htmlspecialchars($_POST['matric_year']) : ''; ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Intermediate Marks (out of 1100) <span class="required">*</span></label>
                            <input type="number" name="inter_marks" required min="0" max="1100" step="1"
                                   value="<?php echo isset($_POST['inter_marks']) ? htmlspecialchars($_POST['inter_marks']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Intermediate Board <span class="required">*</span></label>
                            <input type="text" name="inter_board" required 
                                   placeholder="e.g., BISE Rawalpindi"
                                   value="<?php echo isset($_POST['inter_board']) ? htmlspecialchars($_POST['inter_board']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Intermediate Passing Year <span class="required">*</span></label>
                        <input type="number" name="inter_year" required min="2000" max="<?php echo date('Y'); ?>"
                               value="<?php echo isset($_POST['inter_year']) ? htmlspecialchars($_POST['inter_year']) : ''; ?>">
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
                </div>
                
                <!-- Step 3: Document Upload -->
                <div class="form-step" data-step="3">
                    <h3>Required Documents</h3>
                    <p style="color: #666; margin-bottom: 20px;">All documents must be clear images (JPG, PNG) under 2MB</p>
                    
                    <div class="form-group">
                        <label>Passport Size Photo <span class="required">*</span></label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="photo" id="photo" accept="image/*" required style="display: none;">
                            <label for="photo" class="file-upload-label" id="photo-label">
                                📷 Click to upload your photo
                            </label>
                            <div class="file-preview" id="photo-preview"></div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>CNIC Front Side <span class="required">*</span></label>
                            <div class="file-upload-wrapper">
                                <input type="file" name="cnic_front" id="cnic_front" accept="image/*" required style="display: none;">
                                <label for="cnic_front" class="file-upload-label" id="cnic_front-label">
                                    🪪 Upload CNIC Front
                                </label>
                                <div class="file-preview" id="cnic_front-preview"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>CNIC Back Side <span class="required">*</span></label>
                            <div class="file-upload-wrapper">
                                <input type="file" name="cnic_back" id="cnic_back" accept="image/*" required style="display: none;">
                                <label for="cnic_back" class="file-upload-label" id="cnic_back-label">
                                    🪪 Upload CNIC Back
                                </label>
                                <div class="file-preview" id="cnic_back-preview"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Matric Result Card <span class="required">*</span></label>
                            <div class="file-upload-wrapper">
                                <input type="file" name="matric_result" id="matric_result" accept="image/*" required style="display: none;">
                                <label for="matric_result" class="file-upload-label" id="matric_result-label">
                                    📄 Upload Matric Result
                                </label>
                                <div class="file-preview" id="matric_result-preview"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Intermediate Result Card <span class="required">*</span></label>
                            <div class="file-upload-wrapper">
                                <input type="file" name="inter_result" id="inter_result" accept="image/*" required style="display: none;">
                                <label for="inter_result" class="file-upload-label" id="inter_result-label">
                                    📄 Upload Inter Result
                                </label>
                                <div class="file-preview" id="inter_result-preview"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Step 4: Review -->
                <div class="form-step" data-step="4">
                    <h3>Review Your Application</h3>
                    <p style="color: #666; margin-bottom: 20px;">Please review all information before submitting</p>
                    
                    <div id="review-content"></div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" required> 
                            I declare that all the information provided is true and correct
                        </label>
                    </div>
                </div>
                
                <!-- Navigation Buttons -->
                <div class="form-navigation">
                    <button type="button" class="btn btn-secondary" id="prevBtn" style="display: none;">Previous</button>
                    <button type="button" class="btn" id="nextBtn">Next</button>
                    <button type="submit" class="btn" id="submitBtn" style="display: none;">Submit Application</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/script/student-script/apply-admission.js"></script>
</body>
</html>