<?php

require_once '../config/config.php';
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
    <link rel="stylesheet" href="../assets/css/admincss/view-merit-admi.css">

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