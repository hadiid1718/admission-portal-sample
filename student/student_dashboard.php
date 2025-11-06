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
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../assets/css/studentcss/student-dashboard.css">
</head>
<body>
  

    <!-- Welcome Section -->
    <div class="container">
        <div class="welcome-card" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2>Welcome, <?php echo htmlspecialchars($student_name); ?>!</h2>
                <p>Manage your admission journey</p>
            </div>
            <a href="/adv-web-project/university_admission/auth/logout.php" class="logout-btn">
                Logout
            </a>
        </div>
    </div>

    <div class="container">
        <!-- Merit Status -->
        <?php if ($merit_status): ?>
            <div class="merit-result <?php echo $merit_status['status'] == 'selected' ? '' : 'not-selected'; ?>">
                <?php if ($merit_status['status'] == 'selected'): ?>
                    <h2>Congratulations!</h2>
                    <p>You have been selected for admission</p>
                <?php else: ?>
                    <h2>Application Result</h2>
                    <p>Unfortunately, you were not selected in this merit list</p>
                <?php endif; ?>
                <div class="merit-details">
                    <p>Merit Score: <strong><?php echo number_format($merit_status['merit_score'], 2); ?></strong></p>
                    <p>Rank: <strong>#<?php echo $merit_status['rank']; ?></strong></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Application Status Card -->
            <div class="card">
                <h3>Application Status</h3>
                <?php if ($has_application): ?>
                    <p><strong>Program:</strong> <?php echo htmlspecialchars($application['program_choice']); ?></p>
                    <p>
                        <strong>Status:</strong>
                        <span class="status-badge status-<?php echo $application['status']; ?>">
                            <?php echo strtoupper($application['status']); ?>
                        </span>
                    </p>
                    <?php if ($application['merit_score']): ?>
                        <p><strong>Merit Score:</strong> <?php echo number_format($application['merit_score'], 2); ?></p>
                    <?php endif; ?>
                    <p><strong>Submitted:</strong> <?php echo date('M d, Y', strtotime($application['submitted_at'])); ?></p>
                    <a href="update_application.php" class="btn">Update Application</a>
                <?php else: ?>
                    <p>You haven’t submitted an application yet.</p>
                    <a href="apply_admission.php" class="btn">Apply Now</a>
                <?php endif; ?>
            </div>

            <!-- Merit List Card -->
            <div class="card">
                <h3>Merit List</h3>
                <?php if ($merit_status): ?>
                    <p>The merit list has been published!</p>
                    <a href="view_merit.php" class="btn">View Full Merit List</a>
                <?php else: ?>
                    <p>Merit list not yet published.</p>
                    <p class="status-pending">Check back later</p>
                <?php endif; ?>
            </div>

            <!-- Profile Card -->
            <div class="card">
                <h3>My Profile</h3>
                <p>View and manage your personal information.</p>
                <a href="profile.php" class="btn btn-secondary">View Profile</a>
            </div>
        </div>
    </div>

</body>
</html>
