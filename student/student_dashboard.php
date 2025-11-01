<?php

require_once '../config/config.php';
require_login();

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

// Check if student has already applied
$check_application = "SELECT * FROM application WHERE student_id = $student_id";
$app_result = mysqli_query($conn, $check_application);
$has_application = mysqli_num_rows($app_result) > 0;
$application = $has_application ? mysqli_fetch_assoc($app_result) : null;

// Check merit list status
$merit_status = null;
if ($has_application) {   
    $merit_query = "SELECT * FROM merit_list WHERE student_id = $student_id";
    $merit_result = mysqli_query($conn, $merit_query);
    if (mysqli_num_rows($merit_result) > 0) {
        $merit_status = mysqli_fetch_assoc($merit_result);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Admission Portal</title>
    <link rel="stylesheet" href="../assets/css/studentcss/student-dashboard.css">
  
</head>
<body>
    <div class="navbar">
        <h1> Admission Portal</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($student_name); ?></span>
            <a href="/adv-web-project/university_admission/auth/logout.php" rel="noopener" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <?php if ($merit_status): ?>
            <div class="merit-result <?php echo $merit_status['status'] == 'not_selected' ? 'not-selected' : ''; ?>">
                <?php if ($merit_status['status'] == 'selected'): ?>
                    <h2> Congratulations!</h2>
                    <p style="font-size: 18px; margin: 10px 0;">You have been SELECTED for admission</p>
                    <p>Merit Score: <?php echo number_format($merit_status['merit_score'], 2); ?> | Rank: <?php echo $merit_status['rank']; ?></p>
                <?php else: ?>
                    <h2>Application Status</h2>
                    <p style="font-size: 18px; margin: 10px 0;">Unfortunately, you were not selected in this merit list</p>
                    <p>Merit Score: <?php echo number_format($merit_status['merit_score'], 2); ?> | Rank: <?php echo $merit_status['rank']; ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="welcome-card">
            <h2>Welcome to Your Dashboard</h2>
            <p>Manage your admission application and check your status</p>
        </div>
        
        <div class="dashboard-grid">
            <div class="card">
                <h3> Application Status</h3>
                <?php if ($has_application): ?>
                    <p><strong>Program:</strong> <?php echo htmlspecialchars($application['program_choice']); ?></p>
                    <p><strong>Status:</strong> <span class="status-badge status-<?php echo $application['status']; ?>">
                        <?php echo strtoupper($application['status']); ?>
                    </span></p>
                    <?php if ($application['merit_score']): ?>
                        <p><strong>Merit Score:</strong> <?php echo number_format($application['merit_score'], 2); ?></p>
                    <?php endif; ?>
                    <p><strong>Submitted:</strong> <?php echo date('M d, Y', strtotime($application['submitted_at'])); ?></p>
                    <a href="update_application.php" class="btn">Update Application</a>
                <?php else: ?>
                    <p>You haven't submitted an application yet.</p>
                    <a href="apply_admission.php" class="btn">Apply Now</a>
                <?php endif; ?>
            </div>
            
            <div class="card">
                <h3> Merit List</h3>
                <?php if ($merit_status): ?>
                    <p>The merit list has been published!</p>
                    <a href="view_merit.php" class="btn">View Full Merit List</a>
                <?php else: ?>
                    <p>Merit list not yet published. Check back later.</p>
                    <button class="btn btn-secondary" disabled>Not Available</button>
                <?php endif; ?>
            </div>
            
            <div class="card">
                <h3> My Profile</h3>
                <p>View and manage your personal information</p>
                <a href="profile.php" class="btn">View Profile</a>
            </div>
        </div>
    </div>
</body>
</html>