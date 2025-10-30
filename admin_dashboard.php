<?php
require_once 'config.php';
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
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
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
        
        .navbar .admin-badge {
            background: #e74c3c;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            margin-right: 15px;
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
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .actions-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .actions-card h2 {
            color: #333;
            margin-bottom: 20px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: white;
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }

        .program-stats-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .program-stats-card h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .program-stat-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            border-left: 4px solid #3498db;
            transition: all 0.3s;
        }

        .program-stat-item:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .program-stat-item h4 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .program-stat-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 10px;
            font-size: 14px;
            color: #666;
        }

        .program-stat-details div {
            background: white;
            padding: 8px;
            border-radius: 3px;
        }
        
        .applications-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .applications-card h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .filter-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-section label {
            font-weight: 600;
            color: #555;
            margin-right: 10px;
        }

        .filter-btn {
            padding: 8px 16px;
            border: 2px solid #3498db;
            background: white;
            color: #3498db;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
            font-weight: 500;
        }

        .filter-btn:hover {
            background: #e8f4f8;
        }

        .filter-btn.active {
            background: #3498db;
            color: white;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #555;
            border-bottom: 2px solid #dee2e6;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        
        tr:hover {
            background: #f8f9fa;
        }

        .program-group {
            margin-bottom: 30px;
        }

        .program-header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 10px;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .program-count {
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
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

        .status-under_review {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        @media (max-width: 768px) {
            table {
                font-size: 12px;
            }
            
            th, td {
                padding: 8px;
            }

            .filter-section {
                flex-direction: column;
                align-items: flex-start;
            }

            .program-header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1> Admin Panel</h1>
        <div style="display: flex; align-items: center;">
            <span class="admin-badge">ADMIN</span>
            <span style="margin-right: 15px;"><?php echo htmlspecialchars($admin_username); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
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