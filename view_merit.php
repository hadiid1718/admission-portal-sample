<?php

require_once 'config.php';
require_login();

$student_id = $_SESSION['student_id'];

// Get merit list
$query = "SELECT m.*, s.name, s.email, a.program_choice, a.matric_marks, a.inter_marks
          FROM merit_list m
          JOIN student s ON m.student_id = s.id
          JOIN application a ON m.application_id = a.id
          ORDER BY m.rank ASC";
$result = mysqli_query($conn, $query);

// Get current student's merit status
$my_merit_query = "SELECT * FROM merit_list WHERE student_id = $student_id";
$my_merit_result = mysqli_query($conn, $my_merit_query);
$my_merit = mysqli_num_rows($my_merit_result) > 0 ? mysqli_fetch_assoc($my_merit_result) : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merit List - Admission Portal</title>
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
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
        }
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .my-result-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .result-selected {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .result-not-selected {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            color: white;
        }
        
        .my-result-card h2 {
            font-size: 32px;
            margin-bottom: 15px;
        }
        
        .my-result-card .details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .my-result-card .detail-item {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 5px;
        }
        
        .my-result-card .detail-item strong {
            display: block;
            font-size: 12px;
            margin-bottom: 5px;
            opacity: 0.9;
        }
        
        .my-result-card .detail-item span {
            font-size: 24px;
            font-weight: bold;
        }
        
        .merit-list-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .merit-list-card h2 {
            color: #333;
            margin-bottom: 20px;
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
            position: sticky;
            top: 0;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .highlight-row {
            background: #fff3cd !important;
            font-weight: bold;
        }
        
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-selected {
            background: #d4edda;
            color: #155724;
        }
        
        .status-not-selected {
            background: #f8d7da;
            color: #721c24;
        }
        
        @media (max-width: 768px) {
            table {
                font-size: 12px;
            }
            
            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1> Merit List</h1>
        <a href="student_dashboard.php" class="back-btn"> Back to Dashboard</a>
    </div>
    
    <div class="container">
        <?php if ($my_merit): ?>
            <div class="my-result-card <?php echo $my_merit['status'] == 'selected' ? 'result-selected' : 'result-not-selected'; ?>">
                <h2>
                    <?php if ($my_merit['status'] == 'selected'): ?>
                         Congratulations! You've been SELECTED
                    <?php else: ?>
                        Your Application Status
                    <?php endif; ?>
                </h2>
                
                <div class="details">
                    <div class="detail-item">
                        <strong>Your Rank</strong>
                        <span><?php echo $my_merit['rank']; ?></span>
                    </div>
                    <div class="detail-item">
                        <strong>Merit Score</strong>
                        <span><?php echo number_format($my_merit['merit_score'], 2); ?></span>
                    </div>
                    <div class="detail-item">
                        <strong>Status</strong>
                        <span><?php echo strtoupper($my_merit['status']); ?></span>
                    </div>
                    <div class="detail-item">
                        <strong>Published On</strong>
                        <span style="font-size: 16px;"><?php echo date('M d, Y', strtotime($my_merit['published_at'])); ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="merit-list-card">
            <h2>Complete Merit List</h2>
            
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search by name, rank, or program...">
            </div>
            
            <div style="overflow-x: auto;">
                <table id="meritTable">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Student Name</th>
                            <th>Program</th>
                            <th>Matric Marks</th>
                            <th>Inter Marks</th>
                            <th>Merit Score</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr <?php echo $row['student_id'] == $student_id ? 'class="highlight-row"' : ''; ?>>
                                <td><strong><?php echo $row['rank']; ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($row['name']); ?>
                                    <?php if ($row['student_id'] == $student_id): ?>
                                        <span style="color: #f39c12; margin-left: 5px;">★ You</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['program_choice']); ?></td>
                                <td><?php echo number_format($row['matric_marks'], 2); ?></td>
                                <td><?php echo number_format($row['inter_marks'], 2); ?></td>
                                <td><strong><?php echo number_format($row['merit_score'], 2); ?></strong></td>
                                <td>
                                    <span class="status-badge status-<?php echo $row['status']; ?>">
                                        <?php echo strtoupper(str_replace('_', ' ', $row['status'])); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
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