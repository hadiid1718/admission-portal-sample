<?php

require_once 'config.php';
require_admin();

$admin_username = $_SESSION['admin_username'];

// Get filter from URL
$filter_program = isset($_GET['program']) ? $_GET['program'] : 'all';
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Get all unique programs from merit list
$programs_query = "SELECT DISTINCT a.program_choice 
                   FROM merit_list m 
                   JOIN application a ON m.application_id = a.id 
                   ORDER BY a.program_choice";
$programs_result = mysqli_query($conn, $programs_query);
$programs = [];
while ($row = mysqli_fetch_assoc($programs_result)) {
    $programs[] = $row['program_choice'];
}

// Build query based on filters
$where_conditions = [];
if ($filter_program != 'all') {
    $filter_program_safe = mysqli_real_escape_string($conn, $filter_program);
    $where_conditions[] = "a.program_choice = '$filter_program_safe'";
}
if ($filter_status != 'all') {
    $filter_status_safe = mysqli_real_escape_string($conn, $filter_status);
    $where_conditions[] = "m.status = '$filter_status_safe'";
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get merit list with filters
$merit_query = "SELECT m.*, s.name, s.email, s.cnic, a.program_choice, a.matric_marks, a.inter_marks
                FROM merit_list m
                JOIN student s ON m.student_id = s.id
                JOIN application a ON m.application_id = a.id
                $where_clause
                ORDER BY m.rank ASC";
$merit_result = mysqli_query($conn, $merit_query);

// Get statistics
$total_entries = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM merit_list"))['count'];
$selected_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM merit_list WHERE status = 'selected'"))['count'];
$not_selected_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM merit_list WHERE status = 'not_selected'"))['count'];

// Get program-wise merit statistics
$program_merit_stats = "SELECT a.program_choice, 
                        COUNT(*) as total,
                        SUM(CASE WHEN m.status = 'selected' THEN 1 ELSE 0 END) as selected,
                        SUM(CASE WHEN m.status = 'not_selected' THEN 1 ELSE 0 END) as not_selected,
                        MAX(m.merit_score) as highest_score,
                        MIN(m.merit_score) as lowest_score
                        FROM merit_list m
                        JOIN application a ON m.application_id = a.id
                        GROUP BY a.program_choice
                        ORDER BY a.program_choice";
$program_merit_result = mysqli_query($conn, $program_merit_stats);

// Get published date
$published_query = "SELECT MIN(published_at) as published_date FROM merit_list";
$published_result = mysqli_fetch_assoc(mysqli_query($conn, $published_query));
$published_date = $published_result['published_date'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merit List - Admin Panel</title>
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
        
        .back-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .header-card {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }

        .header-card h2 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .header-card p {
            font-size: 16px;
            opacity: 0.9;
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
            text-align: center;
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

        .stat-card.selected .number {
            color: #27ae60;
        }

        .stat-card.not-selected .number {
            color: #e74c3c;
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

        .program-stat-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1fr;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 10px;
            align-items: center;
        }

        .program-stat-row:first-child {
            background: #2c3e50;
            color: white;
            font-weight: 600;
        }

        .program-stat-row:not(:first-child):hover {
            background: #e9ecef;
        }
        
        .merit-list-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .merit-list-card h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .filter-row {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .filter-row:last-child {
            margin-bottom: 0;
        }

        .filter-row label {
            font-weight: 600;
            color: #555;
            min-width: 100px;
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

        .filter-btn.status-selected {
            border-color: #27ae60;
            color: #27ae60;
        }

        .filter-btn.status-selected.active {
            background: #27ae60;
            color: white;
        }

        .filter-btn.status-not-selected {
            border-color: #e74c3c;
            color: #e74c3c;
        }

        .filter-btn.status-not-selected.active {
            background: #e74c3c;
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #2c3e50;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        
        tr:hover {
            background: #f8f9fa;
        }

        .rank-badge {
            background: #f39c12;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
        }

        .rank-badge.top-10 {
            background: #e74c3c;
        }

        .rank-badge.top-50 {
            background: #3498db;
        }
        
        .status-badge {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-selected {
            background: #d4edda;
            color: #155724;
        }
        
        .status-not-selected {
            background: #f8d7da;
            color: #721c24;
        }

        .search-box {
            margin-bottom: 20px;
        }

        .search-box input {
            width: 100%;
            max-width: 400px;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
        }

        .search-box input:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .export-section {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            table {
                font-size: 12px;
            }
            
            th, td {
                padding: 8px;
            }

            .program-stat-row {
                grid-template-columns: 1fr;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1> Merit List - Admin </h1>
        <a href="admin_dashboard.php" class="back-btn"> Back to Dashboard</a>
    </div>
    
    <div class="container">
        <?php if ($published_date): ?>
            <div class="header-card">
                <h2> Published Merit List</h2>
                <p>Published on: <?php echo date('F d, Y \a\t g:i A', strtotime($published_date)); ?></p>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Entries</h3>
                <div class="number"><?php echo $total_entries; ?></div>
            </div>
            
            <div class="stat-card selected">
                <h3>Selected Students</h3>
                <div class="number"><?php echo $selected_count; ?></div>
            </div>
            
            <div class="stat-card not-selected">
                <h3>Not Selected</h3>
                <div class="number"><?php echo $not_selected_count; ?></div>
            </div>

            <div class="stat-card">
                <h3>Selection Rate</h3>
                <div class="number" style="font-size: 28px;">
                    <?php echo $total_entries > 0 ? round(($selected_count / $total_entries) * 100, 1) : 0; ?>%
                </div>
            </div>
        </div>

   

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="admin_dashboard.php" class="btn btn-primary"> View Applications</a>
        </div>
        
        <div class="merit-list-card">
            <h2>Complete Merit List</h2>
            
            <!-- Filter Section -->
            <div class="filter-section">
                <div class="filter-row">
                    <label> Program:</label>
                    <a href="view_merit_admin.php?program=all&status=<?php echo $filter_status; ?>" 
                       class="filter-btn <?php echo $filter_program == 'all' ? 'active' : ''; ?>">
                        All Programs
                    </a>
                    <?php foreach ($programs as $program): ?>
                        <a href="view_merit_admin.php?program=<?php echo urlencode($program); ?>&status=<?php echo $filter_status; ?>" 
                           class="filter-btn <?php echo $filter_program == $program ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($program); ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <div class="filter-row">
                    <label> Status:</label>
                    <a href="view_merit_admin.php?program=<?php echo $filter_program; ?>&status=all" 
                       class="filter-btn <?php echo $filter_status == 'all' ? 'active' : ''; ?>">
                        All Status
                    </a>
                    <a href="view_merit_admin.php?program=<?php echo $filter_program; ?>&status=selected" 
                       class="filter-btn status-selected <?php echo $filter_status == 'selected' ? 'active' : ''; ?>">
                        Selected
                    </a>
                    <a href="view_merit_admin.php?program=<?php echo $filter_program; ?>&status=not_selected" 
                       class="filter-btn status-not-selected <?php echo $filter_status == 'not_selected' ? 'active' : ''; ?>">
                        Not Selected
                    </a>
                </div>
            </div>

            <!-- Search Box -->
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Search by name, rank, CNIC, or email...">
            </div>
            
            <?php if (mysqli_num_rows($merit_result) > 0): ?>
                <div style="overflow-x: auto;">
                    <table id="meritTable">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th>CNIC</th>
                                <th>Program</th>
                                <th>Matric</th>
                                <th>Inter</th>
                                <th>Merit Score</th>
                                <th>Status</th>
                                <th>Published</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($merit_result)): ?>
                                <tr>
                                    <td>
                                        <span class="rank-badge <?php echo $row['rank'] <= 10 ? 'top-10' : ($row['rank'] <= 50 ? 'top-50' : ''); ?>">
                                            #<?php echo $row['rank']; ?>
                                        </span>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['cnic']); ?></td>
                                    <td><?php echo htmlspecialchars($row['program_choice']); ?></td>
                                    <td><?php echo number_format($row['matric_marks'], 2); ?></td>
                                    <td><?php echo number_format($row['inter_marks'], 2); ?></td>
                                    <td><strong><?php echo number_format($row['merit_score'], 2); ?></strong></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $row['status']; ?>">
                                            <?php echo strtoupper(str_replace('_', ' ', $row['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($row['published_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <p>📭 No merit list entries found with the selected filters</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const table = document.getElementById('meritTable');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const text = row.textContent.toLowerCase();
                
                if (text.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>