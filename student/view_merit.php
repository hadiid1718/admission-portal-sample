<?php
require_once '../config/config.php';
require_login();

$student_id = $_SESSION['student_id'];

// Get student details
$student_query = "SELECT * FROM student WHERE id = $student_id";
$student_result = mysqli_query($conn, $student_query);
$student = mysqli_fetch_assoc($student_result);

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

// Get application details
if ($my_merit) {
    $app_query = "SELECT * FROM application WHERE student_id = $student_id";
    $app_result = mysqli_query($conn, $app_query);
    $application = mysqli_fetch_assoc($app_result);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merit List - Admission Portal</title>
    <link rel="stylesheet" href="../assets/css/studentcss/view-merit.css">  

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

                <?php if ($my_merit['status'] == 'selected'): ?>
                    <div class="challan-section">
                        <h3 style="margin-bottom: 15px;"> Fee Payment</h3>
                        <p style="margin-bottom: 10px;">Download your fee challan to complete the admission process</p>
                        
                    

                        <a href="generate_challan.php?student_id=<?php echo $student_id; ?>" 
                           class="challan-btn" target="_blank">
                            📄 Generate Fee Challan 
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="merit-list-card">
            <h2>Complete Merit List</h2>
            
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Search by name, rank, or program...">
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
    
    <script src="../assets/script/student-script/view-merit.js"></script>
</body>
</html>