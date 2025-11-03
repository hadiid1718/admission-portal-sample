
<?php
require_once '../config/config.php';
require_login();

$student_id = $_SESSION['student_id'];

// Get student details
$student_query = "SELECT * FROM student WHERE id = $student_id";
$student_result = mysqli_query($conn, $student_query);
$student = mysqli_fetch_assoc($student_result);

// Get selected merit list number filter (default to latest)
$selected_merit_list = isset($_GET['merit_list']) ? intval($_GET['merit_list']) : null;

// If no merit list selected, get the latest one
if (!$selected_merit_list) {
    $latest_merit_query = "SELECT MAX(merit_list_number) as latest FROM merit_list";
    $latest_result = mysqli_query($conn, $latest_merit_query);
    $latest_row = mysqli_fetch_assoc($latest_result);
    $selected_merit_list = $latest_row['latest'] ? $latest_row['latest'] : 1;
}

// Get current student's merit status and application for selected merit list
$my_merit_query = "SELECT m.*, a.program_choice, a.time_category 
                   FROM merit_list m
                   JOIN application a ON m.application_id = a.id
                   WHERE m.student_id = $student_id 
                   AND m.merit_list_number = $selected_merit_list";
$my_merit_result = mysqli_query($conn, $my_merit_query);
$my_merit = mysqli_num_rows($my_merit_result) > 0 ? mysqli_fetch_assoc($my_merit_result) : null;

// Get application details
$app_query = "SELECT * FROM application WHERE student_id = $student_id";
$app_result = mysqli_query($conn, $app_query);
$application = mysqli_fetch_assoc($app_result);

// Set default program filter to student's own program if they have one, otherwise Computer Science
$default_program = $application ? $application['program_choice'] : 'Computer Science';
$selected_program = isset($_GET['program']) ? $_GET['program'] : $default_program;

// Get all unique programs for the dropdown
$programs_query = "SELECT DISTINCT a.program_choice 
                   FROM application a
                   JOIN merit_list m ON a.id = m.application_id
                   WHERE m.merit_list_number = $selected_merit_list
                   ORDER BY a.program_choice ASC";
$programs_result = mysqli_query($conn, $programs_query);

// Get all unique merit list numbers
$merit_lists_query = "SELECT DISTINCT merit_list_number FROM merit_list ORDER BY merit_list_number DESC";
$merit_lists_result = mysqli_query($conn, $merit_lists_query);

// Get merit list based on selected program and merit list number
$query = "SELECT m.*, s.name, s.email, a.program_choice, a.time_category, a.matric_marks, a.inter_marks
          FROM merit_list m
          JOIN student s ON m.student_id = s.id
          JOIN application a ON m.application_id = a.id
          WHERE a.program_choice = '" . mysqli_real_escape_string($conn, $selected_program) . "'
          AND m.merit_list_number = $selected_merit_list
          ORDER BY m.rank ASC";
$result = mysqli_query($conn, $query);
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
        <a href="student_dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>
    
    <div class="container">
        <?php if ($my_merit): ?>
            <div class="my-result-card <?php echo $my_merit['status'] == 'selected' ? 'result-selected' : 'result-not-selected'; ?>">

                <?php if ($my_merit['status'] == 'selected'): ?>
                    <div class="challan-section">
                        <h3 style="margin-bottom: 15px;"> Fee Payment</h3>
                        <p style="margin-bottom: 10px;">Download your fee challan to complete the admission process</p>
                        
                        <a href="generate_challan.php?student_id=<?php echo $student_id; ?>" 
                           class="challan-btn" target="_blank">
                             Generate Fee Challan 
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif ($application): ?>
            <div class="my-result-card result-not-selected">
                <h2> Your Application Status</h2>
                <p style="text-align: center; padding: 20px; color: #666;">
                    You are not in Merit List #<?php echo $selected_merit_list; ?> for <?php echo htmlspecialchars($application['program_choice']); ?>.
                    <br>Please check other merit lists or wait for the next merit list announcement.
                </p>
            </div>
        <?php endif; ?>
        
        <div class="merit-list-card">
            <h2>Complete Merit List</h2>
            
            <div class="filter-section">
                <div class="filter-group">
                    <label for="meritListFilter">
                        <strong> Merit List:</strong>
                    </label>
                    <select id="meritListFilter" onchange="filterByMeritList(this.value)">
                        <?php 
                        mysqli_data_seek($merit_lists_result, 0);
                        while ($ml = mysqli_fetch_assoc($merit_lists_result)): 
                        ?>
                            <option value="<?php echo $ml['merit_list_number']; ?>"
                                    <?php echo $ml['merit_list_number'] == $selected_merit_list ? 'selected' : ''; ?>>
                                Merit List #<?php echo $ml['merit_list_number']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="programFilter">
                        <strong> Select Program:</strong>
                    </label>
                    <select id="programFilter" onchange="filterByProgram(this.value)">
                        <?php 
                        mysqli_data_seek($programs_result, 0);
                        $has_programs = false;
                        while ($prog = mysqli_fetch_assoc($programs_result)): 
                            $has_programs = true;
                        ?>
                            <option value="<?php echo htmlspecialchars($prog['program_choice']); ?>"
                                    <?php echo $prog['program_choice'] == $selected_program ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($prog['program_choice']); ?>
                            </option>
                        <?php endwhile; ?>
                        <?php if (!$has_programs): ?>
                            <option value="">No programs available</option>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="🔍 Search by name, rank, or program...">
                </div>
            </div>
            
            <div style="overflow-x: auto;">
                <table id="meritTable">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Student Name</th>
                            <th>Program</th>
                            <th>Dicipline</th>
                            <th>Matric Marks</th>
                            <th>Inter Marks</th>
                            <th>Merit Score</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $has_data = false;
                        while ($row = mysqli_fetch_assoc($result)): 
                            $has_data = true;
                        ?>
                            <tr <?php echo $row['student_id'] == $student_id ? 'class="highlight-row"' : ''; ?>>
                                <td><strong><?php echo $row['rank']; ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($row['name']); ?>
                                    <?php if ($row['student_id'] == $student_id): ?>
                                        <span style="color: #f39c12; margin-left: 5px;">★ You</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['program_choice']); ?></td>
                                <td>
                                    <span class="time-category-badge <?php echo str_replace('/', '-', strtolower($row['time_category'])); ?>">
                                        <?php echo htmlspecialchars($row['time_category']); ?>
                                    </span>
                                </td>
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
                        <?php if (!$has_data): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px; color: #999;">
                                    No merit list entries found for this program and merit list number.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="../assets/script/student-script/view-merit.js"></script>
    <script>
        function filterByMeritList(meritListNumber) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('merit_list', meritListNumber);
            // Keep the program filter if it exists
            window.location.search = urlParams.toString();
        }
        
        function filterByProgram(program) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('program', program);
            window.location.search = urlParams.toString();
        }
    </script>
</body>
</html>