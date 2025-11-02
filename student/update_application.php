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
    $program_level = sanitize_input($_POST['program_level']);
    $time_category = sanitize_input($_POST['time_category']);
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
    
    if ($matric_marks <= 0 || $matric_marks > 1100) {
        $errors[] = "Matric marks must be between 0 and 1100";
    }
    
    if ($inter_marks <= 0 || $inter_marks > 1100) {
        $errors[] = "Intermediate marks must be between 0 and 1100";
    }
    
    if (empty($program_level)) {
        $errors[] = "Please select a program level";
    }
    
    if (empty($time_category)) {
        $errors[] = "Please select a time category";
    }
    
    if (empty($program_choice)) {
        $errors[] = "Please select a program";
    }
    
    // File Upload Handling (optional for updates)
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
        // Check if new file is uploaded
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
            
            // Delete old file if exists
            if (!empty($application[$field])) {
                $old_file = $upload_dir . $application[$field];
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
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
            // Keep existing file
            $uploaded_files[$field] = $application[$field];
        }
    }
    
    // Calculate merit score
    $merit_score = (0.5 * $matric_marks / 11) + (0.5 * $inter_marks / 11);
    
    if (empty($errors)) {
        $update_sql = "UPDATE application SET 
                       father_name = '$father_name',
                       mother_name = '$mother_name',
                       date_of_birth = '$dob',
                       cnic = '$cnic',
                       phone = '$phone',
                       address = '$address',
                       city = '$city',
                       province = '$province',
                       postal_code = '$postal_code',
                       matric_marks = $matric_marks,
                       inter_marks = $inter_marks,
                       matric_board = '$matric_board',
                       inter_board = '$inter_board',
                       matric_year = $matric_year,
                       inter_year = $inter_year,
                       program_level = '$program_level',
                       time_category = '$time_category',
                       program_choice = '$program_choice',
                       merit_score = $merit_score,
                       cnic_front = '{$uploaded_files['cnic_front']}',
                       cnic_back = '{$uploaded_files['cnic_back']}',
                       matric_result = '{$uploaded_files['matric_result']}',
                       inter_result = '{$uploaded_files['inter_result']}',
                       photo = '{$uploaded_files['photo']}'
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
        <h1> Admission Portal</h1>
        <a href="student_dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>
    
    <div class="container">
        <div class="form-card">
            <h2>Update Your Application</h2>
            <p class="subtitle">Modify your application details</p>
            
            <div class="warning-box">
                <strong>⚠️ Important:</strong> Any changes you make will recalculate your merit score. Make sure all information is accurate before updating.
            </div>
            
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
            
            <form method="POST" action="" id="updateForm" enctype="multipart/form-data">
                <!-- Step 1: Personal Information -->
                <div class="form-step active" data-step="1">
                    <h3>Personal Information</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Father's Name <span class="required">*</span></label>
                            <input type="text" name="father_name" required 
                                   value="<?php echo htmlspecialchars($application['father_name']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Mother's Name <span class="required">*</span></label>
                            <input type="text" name="mother_name" required 
                                   value="<?php echo htmlspecialchars($application['mother_name'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Date of Birth <span class="required">*</span></label>
                            <input type="date" name="dob" required max="<?php echo date('Y-m-d', strtotime('-15 years')); ?>"
                                   value="<?php echo htmlspecialchars($application['date_of_birth']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>CNIC <span class="required">*</span></label>
                            <input type="text" name="cnic" required pattern="\d{5}-\d{7}-\d{1}" 
                                   placeholder="12345-1234567-1"
                                   value="<?php echo htmlspecialchars($application['cnic'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Phone Number <span class="required">*</span></label>
                        <input type="tel" name="phone" required pattern="03\d{9}" 
                               placeholder="03xxxxxxxxx"
                               value="<?php echo htmlspecialchars($application['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Address <span class="required">*</span></label>
                        <textarea name="address" required rows="3"><?php echo htmlspecialchars($application['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>City <span class="required">*</span></label>
                            <input type="text" name="city" required 
                                   value="<?php echo htmlspecialchars($application['city'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Province <span class="required">*</span></label>
                            <select name="province" required>
                                <option value="">Select Province</option>
                                <option value="Punjab" <?php echo ($application['province'] ?? '') == 'Punjab' ? 'selected' : ''; ?>>Punjab</option>
                                <option value="Sindh" <?php echo ($application['province'] ?? '') == 'Sindh' ? 'selected' : ''; ?>>Sindh</option>
                                <option value="KPK" <?php echo ($application['province'] ?? '') == 'KPK' ? 'selected' : ''; ?>>KPK</option>
                                <option value="Balochistan" <?php echo ($application['province'] ?? '') == 'Balochistan' ? 'selected' : ''; ?>>Balochistan</option>
                                <option value="Islamabad" <?php echo ($application['province'] ?? '') == 'Islamabad' ? 'selected' : ''; ?>>Islamabad</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Postal Code</label>
                        <input type="text" name="postal_code" pattern="\d{5}" placeholder="12345"
                               value="<?php echo htmlspecialchars($application['postal_code'] ?? ''); ?>">
                    </div>
                </div>
                
                <!-- Step 2: Academic Information -->
                <div class="form-step" data-step="2">
                    <h3>Academic Information</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Matric Marks (out of 1100) <span class="required">*</span></label>
                            <input type="number" name="matric_marks" required min="0" max="1100" step="1"
                                   value="<?php echo htmlspecialchars($application['matric_marks']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Matric Board <span class="required">*</span></label>
                            <input type="text" name="matric_board" required 
                                   placeholder="e.g., BISE Rawalpindi"
                                   value="<?php echo htmlspecialchars($application['matric_board'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Matric Passing Year <span class="required">*</span></label>
                        <input type="number" name="matric_year" required min="2000" max="<?php echo date('Y'); ?>"
                               value="<?php echo htmlspecialchars($application['matric_year'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Intermediate Marks (out of 1100) <span class="required">*</span></label>
                            <input type="number" name="inter_marks" required min="0" max="1100" step="1"
                                   value="<?php echo htmlspecialchars($application['inter_marks']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Intermediate Board <span class="required">*</span></label>
                            <input type="text" name="inter_board" required 
                                   placeholder="e.g., BISE Rawalpindi"
                                   value="<?php echo htmlspecialchars($application['inter_board'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Intermediate Passing Year <span class="required">*</span></label>
                        <input type="number" name="inter_year" required min="2000" max="<?php echo date('Y'); ?>"
                               value="<?php echo htmlspecialchars($application['inter_year'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Program Level <span class="required">*</span></label>
                        <select name="program_level" required>
                            <option value="">Select Program Level</option>
                            <option value="Undergraduate BS" <?php echo ($application['program_level'] ?? '') == 'Undergraduate BS' ? 'selected' : ''; ?>>Undergraduate BS</option>
                            <option value="MPhil" <?php echo ($application['program_level'] ?? '') == 'MPhil' ? 'selected' : ''; ?>>MPhil</option>
                            <option value="PhD" <?php echo ($application['program_level'] ?? '') == 'PhD' ? 'selected' : ''; ?>>PhD</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Time Category <span class="required">*</span></label>
                        <select name="time_category" required>
                            <option value="">Select Time Category</option>
                            <option value="Morning/Regular" <?php echo ($application['time_category'] ?? '') == 'Morning/Regular' ? 'selected' : ''; ?>>Morning/Regular</option>
                            <option value="Evening/Self Support" <?php echo ($application['time_category'] ?? '') == 'Evening/Self Support' ? 'selected' : ''; ?>>Evening/Self Support</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Program Choice <span class="required">*</span></label>
                        <select name="program_choice" required>
                            <option value="">Select a program</option>
                            <option value="Anthropology" <?php echo ($application['program_choice'] ?? '') == 'Anthropology' ? 'selected' : ''; ?>>Anthropology</option>
                            <option value="Archeology" <?php echo ($application['program_choice'] ?? '') == 'Archeology' ? 'selected' : ''; ?>>Archeology</option>
                            <option value="BBA" <?php echo ($application['program_choice'] ?? '') == 'BBA' ? 'selected' : ''; ?>>BBA</option>
                            <option value="Bio Informatics" <?php echo ($application['program_choice'] ?? '') == 'Bio Informatics' ? 'selected' : ''; ?>>Bio Informatics</option>
                            <option value="Botany" <?php echo ($application['program_choice'] ?? '') == 'Botany' ? 'selected' : ''; ?>>Botany</option>
                            <option value="Chemistry" <?php echo ($application['program_choice'] ?? '') == 'Chemistry' ? 'selected' : ''; ?>>Chemistry</option>
                            <option value="Computer Sciences" <?php echo ($application['program_choice'] ?? '') == 'Computer Sciences' ? 'selected' : ''; ?>>Computer Sciences</option>
                            <option value="Economics" <?php echo ($application['program_choice'] ?? '') == 'Economics' ? 'selected' : ''; ?>>Economics</option>
                            <option value="Electronics" <?php echo ($application['program_choice'] ?? '') == 'Electronics' ? 'selected' : ''; ?>>Electronics</option>
                            <option value="English" <?php echo ($application['program_choice'] ?? '') == 'English' ? 'selected' : ''; ?>>English</option>
                            <option value="Environmental Sciences" <?php echo ($application['program_choice'] ?? '') == 'Environmental Sciences' ? 'selected' : ''; ?>>Environmental Sciences</option>
                            <option value="Gender Studies" <?php echo ($application['program_choice'] ?? '') == 'Gender Studies' ? 'selected' : ''; ?>>Gender Studies</option>
                            <option value="History" <?php echo ($application['program_choice'] ?? '') == 'History' ? 'selected' : ''; ?>>History</option>
                            <option value="Information Technology" <?php echo ($application['program_choice'] ?? '') == 'Information Technology' ? 'selected' : ''; ?>>Information Technology</option>
                            <option value="International Relations" <?php echo ($application['program_choice'] ?? '') == 'International Relations' ? 'selected' : ''; ?>>International Relations</option>
                            <option value="Mathematics" <?php echo ($application['program_choice'] ?? '') == 'Mathematics' ? 'selected' : ''; ?>>Mathematics</option>
                            <option value="National Security" <?php echo ($application['program_choice'] ?? '') == 'National Security' ? 'selected' : ''; ?>>National Security</option>
                            <option value="NIPS" <?php echo ($application['program_choice'] ?? '') == 'NIPS' ? 'selected' : ''; ?>>NIPS</option>
                            <option value="Pharmacy" <?php echo ($application['program_choice'] ?? '') == 'Pharmacy' ? 'selected' : ''; ?>>Pharmacy</option>
                            <option value="Physics" <?php echo ($application['program_choice'] ?? '') == 'Physics' ? 'selected' : ''; ?>>Physics</option>
                            <option value="Plant Sciences" <?php echo ($application['program_choice'] ?? '') == 'Plant Sciences' ? 'selected' : ''; ?>>Plant Sciences</option>
                            <option value="Psychology" <?php echo ($application['program_choice'] ?? '') == 'Psychology' ? 'selected' : ''; ?>>Psychology</option>
                            <option value="Public Administration" <?php echo ($application['program_choice'] ?? '') == 'Public Administration' ? 'selected' : ''; ?>>Public Administration</option>
                            <option value="Statistics" <?php echo ($application['program_choice'] ?? '') == 'Statistics' ? 'selected' : ''; ?>>Statistics</option>
                            <option value="TIAC" <?php echo ($application['program_choice'] ?? '') == 'TIAC' ? 'selected' : ''; ?>>TIAC</option>
                        </select>
                    </div>
                </div>
                
                <!-- Step 3: Document Upload -->
                <div class="form-step" data-step="3">
                    <h3>Update Documents</h3>
                    
                    <div class="info-box">
                        <strong>ℹ️ Info:</strong> You can upload new documents to replace existing ones, or leave them unchanged. All documents must be clear images (JPG, PNG) under 2MB.
                    </div>
                    
                    <div class="form-group">
                        <label>Passport Size Photo</label>
                        <?php if (!empty($application['photo'])): ?>
                            <div class="existing-file">
                                ✓ Current: <?php echo htmlspecialchars($application['photo']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="file-upload-wrapper">
                            <input type="file" name="photo" id="photo" accept="image/*" style="display: none;">
                            <label for="photo" class="file-upload-label" id="photo-label">
                                📷 Click to upload new photo (optional)
                            </label>
                            <div class="file-preview" id="photo-preview"></div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>CNIC Front Side</label>
                            <?php if (!empty($application['cnic_front'])): ?>
                                <div class="existing-file">
                                    ✓ Current: <?php echo htmlspecialchars($application['cnic_front']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="file-upload-wrapper">
                                <input type="file" name="cnic_front" id="cnic_front" accept="image/*" style="display: none;">
                                <label for="cnic_front" class="file-upload-label" id="cnic_front-label">
                                    🪪 Upload new CNIC Front (optional)
                                </label>
                                <div class="file-preview" id="cnic_front-preview"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>CNIC Back Side</label>
                            <?php if (!empty($application['cnic_back'])): ?>
                                <div class="existing-file">
                                    ✓ Current: <?php echo htmlspecialchars($application['cnic_back']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="file-upload-wrapper">
                                <input type="file" name="cnic_back" id="cnic_back" accept="image/*" style="display: none;">
                                <label for="cnic_back" class="file-upload-label" id="cnic_back-label">
                                    🪪 Upload new CNIC Back (optional)
                                </label>
                                <div class="file-preview" id="cnic_back-preview"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Matric Result Card</label>
                            <?php if (!empty($application['matric_result'])): ?>
                                <div class="existing-file">
                                    ✓ Current: <?php echo htmlspecialchars($application['matric_result']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="file-upload-wrapper">
                                <input type="file" name="matric_result" id="matric_result" accept="image/*" style="display: none;">
                                <label for="matric_result" class="file-upload-label" id="matric_result-label">
                                    📄 Upload new Matric Result (optional)
                                </label>
                                <div class="file-preview" id="matric_result-preview"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Intermediate Result Card</label>
                            <?php if (!empty($application['inter_result'])): ?>
                                <div class="existing-file">
                                    ✓ Current: <?php echo htmlspecialchars($application['inter_result']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="file-upload-wrapper">
                                <input type="file" name="inter_result" id="inter_result" accept="image/*" style="display: none;">
                                <label for="inter_result" class="file-upload-label" id="inter_result-label">
                                    📄 Upload new Inter Result (optional)
                                </label>
                                <div class="file-preview" id="inter_result-preview"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Step 4: Review -->
                <div class="form-step" data-step="4">
                    <h3>Review Your Changes</h3>
                    <p style="color: #666; margin-bottom: 20px;">Please review all changes before updating</p>
                    
                    <div id="review-content"></div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" required> 
                            I confirm that all the updated information is true and correct
                        </label>
                    </div>
                </div>
                
                <!-- Navigation Buttons -->
                <div class="form-navigation">
                    <button type="button" class="btn btn-secondary" id="prevBtn" style="display: none;">Previous</button>
                    <button type="button" class="btn" id="nextBtn">Next</button>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn" id="submitBtn" style="display: none;">Update Application</button>
                        <a href="student_dashboard.php" class="btn btn-cancel" style="text-decoration: none; display: none;" id="cancelBtn">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/script/student-script/update-application.js"></script>
</body>
</html>