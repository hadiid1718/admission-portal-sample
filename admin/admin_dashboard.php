<?php
require_once '../config/config.php';
require_admin();

$admin_username = $_SESSION['admin_username'];

// Get statistics
$total_students = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM student"))['count'];
$total_applications = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM application"))['count'];
$pending_applications = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM application WHERE status = 'pending'"))['count'];
$merit_published = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM merit_list"))['count'] > 0;

// Get filter from URL
$filter_program = isset($_GET['program']) ? $_GET['program'] : 'all';

// Get all unique programs
$programs_query = "SELECT DISTINCT program_choice FROM application ORDER BY program_choice";
$programs_result = mysqli_query($conn, $programs_query);
$programs = [];
while ($row = mysqli_fetch_assoc($programs_result)) {
    $programs[] = $row['program_choice'];
}

// Get applications with optional filter
if ($filter_program == 'all') {
    $applications_query = "SELECT a.*, s.name, s.email, s.cnic 
                           FROM application a 
                           JOIN student s ON a.student_id = s.id 
                           ORDER BY a.program_choice, a.merit_score DESC";
} else {
    $filter_program_safe = mysqli_real_escape_string($conn, $filter_program);
    $applications_query = "SELECT a.*, s.name, s.email, s.cnic 
                           FROM application a 
                           JOIN student s ON a.student_id = s.id 
                           WHERE a.program_choice = '$filter_program_safe'
                           ORDER BY a.merit_score DESC";
}
$applications_result = mysqli_query($conn, $applications_query);

// Get program-wise statistics
$program_stats_query = "SELECT program_choice, COUNT(*) as count, 
                        AVG(merit_score) as avg_score, 
                        MAX(merit_score) as max_score,
                        MIN(merit_score) as min_score
                        FROM application 
                        GROUP BY program_choice 
                        ORDER BY program_choice";
$program_stats_result = mysqli_query($conn, $program_stats_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Admission Portal</title>
    <link rel="stylesheet" href="../assets/css/admincss/admin-dashboard.css">

</head>
<body>
    <div class="navbar">
        <h1> Admin Panel</h1>
        <div style="display: flex; align-items: center;">
            <span class="admin-badge">ADMIN</span>
            <span style="margin-right: 15px;"><?php echo htmlspecialchars($admin_username); ?></span>
            <a href="/adv-web-project/university_admission/auth/logout.php" rel="noopener" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Students</h3>
                <div class="number"><?php echo $total_students; ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Total Applications</h3>
                <div class="number"><?php echo $total_applications; ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Pending Applications</h3>
                <div class="number"><?php echo $pending_applications; ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Merit List Status</h3>
                <div class="number" style="font-size: 20px;">
                    <?php echo $merit_published ? ' Published' : ' Not Published'; ?>
                </div>
            </div>
        </div>
        
        <div class="actions-card">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="#applications" class="btn btn-primary">View All Applications</a>
                <a href="generate_merit.php" class="btn btn-success">Generate Merit List</a>
                <?php if ($merit_published): ?>
                    <a href="view_merit_admin.php" class="btn btn-primary">View Merit List</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Program-wise Statistics -->
        <div class="program-stats-card">
            <h2> Program-wise Statistics</h2>
            <?php if (mysqli_num_rows($program_stats_result) > 0): ?>
                <?php while ($stat = mysqli_fetch_assoc($program_stats_result)): ?>
                    <div class="program-stat-item">
                        <h4><?php echo htmlspecialchars($stat['program_choice']); ?></h4>
                        <div class="program-stat-details">
                            <div><strong>Applications:</strong> <?php echo $stat['count']; ?></div>
                            <div><strong>Avg Score:</strong> <?php echo number_format($stat['avg_score'], 2); ?></div>
                            <div><strong>Highest:</strong> <?php echo number_format($stat['max_score'], 2); ?></div>
                            <div><strong>Lowest:</strong> <?php echo number_format($stat['min_score'], 2); ?></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #999; text-align: center; padding: 20px;">No applications yet</p>
            <?php endif; ?>
        </div>
        
        <!-- Applications Section -->
        <div class="applications-card" id="applications">
            <h2>All Applications</h2>
            
            <!-- Filter Section -->
            <div class="filter-section">
                <label> Filter by Program:</label>
                <a href="admin_dashboard.php?program=all" class="filter-btn <?php echo $filter_program == 'all' ? 'active' : ''; ?>">
                    All Programs
                </a>
                <?php foreach ($programs as $program): ?>
                    <a href="admin_dashboard.php?program=<?php echo urlencode($program); ?>" 
                       class="filter-btn <?php echo $filter_program == $program ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($program); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <?php if (mysqli_num_rows($applications_result) > 0): ?>
                <div style="overflow-x: auto;">
                    <?php 
                    // Group applications by program if showing all
                    if ($filter_program == 'all') {
                        $grouped_apps = [];
                        mysqli_data_seek($applications_result, 0);
                        while ($app = mysqli_fetch_assoc($applications_result)) {
                            $grouped_apps[$app['program_choice']][] = $app;
                        }
                        
                        foreach ($grouped_apps as $program => $apps):
                    ?>
                            <div class="program-group">
                                <div class="program-header">
                                    <span><?php echo htmlspecialchars($program); ?></span>
                                    <span class="program-count"><?php echo count($apps); ?> Applications</span>
                                </div>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Student Name</th>
                                            <th>Email</th>
                                            <th>CNIC</th>
                                            <th>Matric</th>
                                            <th>Inter</th>
                                            <th>Merit Score</th>
                                            <th>Status</th>
                                            <th>Submitted</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($apps as $app): ?>
                                            <tr>
                                                <td><?php echo $app['id']; ?></td>
                                                <td><?php echo htmlspecialchars($app['name']); ?></td>
                                                <td><?php echo htmlspecialchars($app['email']); ?></td>
                                                <td><?php echo htmlspecialchars($app['cnic']); ?></td>
                                                <td><?php echo number_format($app['matric_marks'], 2); ?></td>
                                                <td><?php echo number_format($app['inter_marks'], 2); ?></td>
                                                <td><strong><?php echo number_format($app['merit_score'], 2); ?></strong></td>
                                                <td>
                                                    <span class="status-badge status-<?php echo $app['status']; ?>">
                                                        <?php echo strtoupper(str_replace('_', ' ', $app['status'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($app['submitted_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                    <?php 
                        endforeach;
                    } else {
                        // Show single program
                    ?>
                        <div class="program-header">
                            <span><?php echo htmlspecialchars($filter_program); ?></span>
                            <span class="program-count"><?php echo mysqli_num_rows($applications_result); ?> Applications</span>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Student Name</th>
                                    <th>Email</th>
                                    <th>CNIC</th>
                                    <th>Matric</th>
                                    <th>Inter</th>
                                    <th>Merit Score</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($app = mysqli_fetch_assoc($applications_result)): ?>
                                    <tr>
                                        <td><?php echo $app['id']; ?></td>
                                        <td><?php echo htmlspecialchars($app['name']); ?></td>
                                        <td><?php echo htmlspecialchars($app['email']); ?></td>
                                        <td><?php echo htmlspecialchars($app['cnic']); ?></td>
                                        <td><?php echo number_format($app['matric_marks'], 2); ?></td>
                                        <td><?php echo number_format($app['inter_marks'], 2); ?></td>
                                        <td><strong><?php echo number_format($app['merit_score'], 2); ?></strong></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $app['status']; ?>">
                                                <?php echo strtoupper(str_replace('_', ' ', $app['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($app['submitted_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php } ?>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <p> No applications found</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>