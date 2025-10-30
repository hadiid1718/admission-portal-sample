<?php

require_once 'config.php';
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
        
        .navbar .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .welcome-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .welcome-card h2 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .welcome-card p {
            color: #666;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card h3 {
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card p {
            color: #666;
            margin-bottom: 15px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: transform 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-selected {
            background: #d4edda;
            color: #155724;
        }
        
        .status-not-selected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .merit-result {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .merit-result.not-selected {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        }
        
        .merit-result h2 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .icon {
            width: 24px;
            height: 24px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1> Admission Portal</h1>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($student_name); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
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