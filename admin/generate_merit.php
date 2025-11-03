<?php

require_once '../config/config.php';
require_admin();

$success = '';
$error = '';

// Define seats per department per time category
$SEATS_PER_DEPT = 60;

// Handle merit list generation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate'])) {
    $cutoff_score = floatval($_POST['cutoff_score']);
    $merit_list_number = intval($_POST['merit_list_number']);
    
    // Check if merit list number already exists
    $check_query = "SELECT COUNT(*) as count FROM merit_list WHERE merit_list_number = $merit_list_number";
    $check_result = mysqli_query($conn, $check_query);
    $check_data = mysqli_fetch_assoc($check_result);
    
    if ($check_data['count'] > 0 && $_POST['action'] != 'regenerate') {
        $error = "Merit List #$merit_list_number already exists. Use a different number or regenerate.";
    } else {
        // If regenerating, delete existing merit list with this number
        if ($_POST['action'] == 'regenerate') {
            mysqli_query($conn, "DELETE FROM merit_list WHERE merit_list_number = $merit_list_number");
        }
        
        // Get seat availability for each program and time category
        $seat_status = [];
        
        // Get all unique program and time category combinations from applications
        $combinations_query = "SELECT DISTINCT program_choice, time_category FROM application";
        $combinations_result = mysqli_query($conn, $combinations_query);
        
        while ($combo = mysqli_fetch_assoc($combinations_result)) {
            $program = $combo['program_choice'];
            $time_category = $combo['time_category'];
            
            // Initialize if not exists
            if (!isset($seat_status[$program])) {
                $seat_status[$program] = [];
            }
            
            // Count confirmed (fee paid) students for this combination
            $confirmed_query = "
                SELECT COUNT(*) as count FROM merit_list m
                JOIN application a ON m.application_id = a.id
                WHERE a.program_choice = '" . mysqli_real_escape_string($conn, $program) . "'
                AND a.time_category = '" . mysqli_real_escape_string($conn, $time_category) . "'
                AND m.status = 'confirmed'
            ";
            $confirmed_result = mysqli_query($conn, $confirmed_query);
            $confirmed_count = mysqli_fetch_assoc($confirmed_result)['count'];
            
            $seat_status[$program][$time_category] = [
                'filled' => $confirmed_count,
                'available' => $SEATS_PER_DEPT - $confirmed_count
            ];
        }
        
        // Mark unpaid students from previous merit lists as 'not_selected'
        mysqli_query($conn, "
            UPDATE merit_list 
            SET status = 'not_selected' 
            WHERE status = 'selected' 
            AND merit_list_number < $merit_list_number
            AND student_id NOT IN (
                SELECT student_id FROM fee_payment WHERE payment_status = 'paid'
            )
        ");
        
        // Get all applications sorted by merit score, grouped by program and time category
        $query = "SELECT a.*, s.name, s.email 
                  FROM application a 
                  JOIN student s ON a.student_id = s.id
                  WHERE a.id NOT IN (
                      SELECT application_id FROM merit_list WHERE status = 'confirmed'
                  )
                  ORDER BY a.program_choice, a.time_category, a.merit_score DESC";
        $result = mysqli_query($conn, $query);
        
        $rank_by_program_time = [];
        $total_generated = 0;
        
        while ($app = mysqli_fetch_assoc($result)) {
            $program = $app['program_choice'];
            $time_category = $app['time_category'];
            
            // Initialize rank counter for this program-time combination
            $key = $program . '_' . $time_category;
            if (!isset($rank_by_program_time[$key])) {
                $rank_by_program_time[$key] = 1;
            }
            
            $rank = $rank_by_program_time[$key];
            
            // Initialize seat status if not exists (safety check)
            if (!isset($seat_status[$program])) {
                $seat_status[$program] = [];
            }
            if (!isset($seat_status[$program][$time_category])) {
                $seat_status[$program][$time_category] = [
                    'filled' => 0,
                    'available' => $SEATS_PER_DEPT
                ];
            }
            
            // Check if seats are available for this program and time category
            $seats_available = $seat_status[$program][$time_category]['available'];
            
            // Determine status based on cutoff score and seat availability
            if ($app['merit_score'] >= $cutoff_score && $seats_available > 0) {
                $status = 'selected';
                $seat_status[$program][$time_category]['available']--;
                $seat_status[$program][$time_category]['filled']++;
            } else {
                $status = 'not_selected';
            }
            
            $insert = "INSERT INTO merit_list (
                application_id, 
                student_id, 
                merit_score, 
                rank, 
                status, 
                merit_list_number
            ) VALUES (
                {$app['id']}, 
                {$app['student_id']}, 
                {$app['merit_score']}, 
                $rank, 
                '$status',
                $merit_list_number
            )";
            
            if (mysqli_query($conn, $insert)) {
                // Update application status
                mysqli_query($conn, "UPDATE application SET status = '$status' WHERE id = {$app['id']}");
                $total_generated++;
            }
            
            $rank_by_program_time[$key]++;
        }
        
        if ($total_generated > 0) {
            $success = "Merit List #$merit_list_number generated successfully! $total_generated entries created.";
        } else {
            $error = "Failed to generate merit list.";
        }
    }
}

// Get current merit list statistics
$current_merit_query = "SELECT MAX(merit_list_number) as latest FROM merit_list";
$current_merit_result = mysqli_query($conn, $current_merit_query);
$current_merit = mysqli_fetch_assoc($current_merit_result);
$latest_merit_number = $current_merit['latest'] ?? 0;
$next_merit_number = $latest_merit_number + 1;

// Get seat availability statistics
$seat_stats_query = "
    SELECT 
        a.program_choice,
        a.time_category,
        COUNT(CASE WHEN m.status = 'confirmed' THEN 1 END) as confirmed_seats,
        COUNT(CASE WHEN m.status = 'selected' THEN 1 END) as selected_seats
    FROM application a
    LEFT JOIN merit_list m ON a.id = m.application_id
    GROUP BY a.program_choice, a.time_category
    ORDER BY a.program_choice, a.time_category
";
$seat_stats_result = mysqli_query($conn, $seat_stats_query);

// Get applications for preview
$apps_query = "SELECT a.*, s.name, s.email 
               FROM application a 
               JOIN student s ON a.student_id = s.id 
               ORDER BY a.program_choice, a.time_category, a.merit_score DESC";
$apps_result = mysqli_query($conn, $apps_query);

// Calculate statistics
$total_apps = mysqli_num_rows($apps_result);
$apps_data = mysqli_fetch_all($apps_result, MYSQLI_ASSOC);
$highest_score = $total_apps > 0 ? $apps_data[0]['merit_score'] : 0;
$lowest_score = $total_apps > 0 ? $apps_data[$total_apps - 1]['merit_score'] : 0;
$avg_score = $total_apps > 0 ? array_sum(array_column($apps_data, 'merit_score')) / $total_apps : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Merit List - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admincss/generate-merit.css">
  
</head>
<body>
    <div class="navbar">
        <h1> Generate Merit List</h1>
        <a href="admin_dashboard.php" class="back-btn">Back to Dashboard</a>
    </div>
    
    <div class="container">
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="merit-number-badge">
            📋 Latest Merit List: #<?php echo $latest_merit_number; ?> | Next: #<?php echo $next_merit_number; ?>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Applications</h3>
                <div class="number"><?php echo $total_apps; ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Highest Score</h3>
                <div class="number"><?php echo number_format($highest_score, 2); ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Lowest Score</h3>
                <div class="number"><?php echo number_format($lowest_score, 2); ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Average Score</h3>
                <div class="number"><?php echo number_format($avg_score, 2); ?></div>
            </div>
        </div>
        
        <div class="seat-stats-card">
            <h2>📊 Seat Availability Status (<?php echo $SEATS_PER_DEPT; ?> seats per program/category)</h2>
            <table class="seat-stats-table">
                <thead>
                    <tr>
                        <th>Program</th>
                        <th>Time Category</th>
                        <th>Confirmed Seats</th>
                        <th>Selected (Pending Fee)</th>
                        <th>Available Seats</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($stat = mysqli_fetch_assoc($seat_stats_result)): 
                        $available = $SEATS_PER_DEPT - $stat['confirmed_seats'];
                        $is_full = $available <= 0;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($stat['program_choice']); ?></td>
                            <td><?php echo htmlspecialchars($stat['time_category']); ?></td>
                            <td><strong><?php echo $stat['confirmed_seats']; ?></strong></td>
                            <td><?php echo $stat['selected_seats']; ?></td>
                            <td><strong><?php echo max(0, $available); ?></strong></td>
                            <td>
                                <span class="seat-badge <?php echo $is_full ? 'seat-full' : 'seat-available'; ?>">
                                    <?php echo $is_full ? '🔴 FULL' : '🟢 AVAILABLE'; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <div class="form-card">
            <h2>Generate Merit List</h2>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Merit List Number *</label>
                    <input type="number" name="merit_list_number" min="1" required 
                           placeholder="e.g., 1, 2, 3..." value="<?php echo $next_merit_number; ?>">
                    <small style="display: block; margin-top: 5px; color: #666;">
                        Use sequential numbers (1, 2, 3...) for each merit list
                    </small>
                </div>
                
                <div class="form-group">
                    <label>Cutoff Merit Score *</label>
                    <input type="number" name="cutoff_score" step="0.01" required 
                           placeholder="e.g., 75.00" value="75">
                    <small style="display: block; margin-top: 5px; color: #666;">
                        Students scoring at or above this value will be selected (if seats available)
                    </small>
                </div>
                
                <div class="form-group">
                    <label>Action</label>
                    <select name="action" required>
                        <option value="generate">Generate New Merit List</option>
                        <option value="regenerate">Regenerate Existing Merit List</option>
                    </select>
                </div>
                
                <button type="submit" name="generate" class="btn btn-success" 
                        onclick="return confirm('This will generate merit list based on available seats. Students who didn\'t pay fees in previous merit lists will be marked as not selected. Continue?')">
                    🎯 Generate & Publish Merit List
                </button>
            </form>
        </div>
        
        <div class="preview-card">
            <h2>Applications Preview (Ranked by Program, Time Category & Merit Score)</h2>
            
            <?php if ($total_apps > 0): ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th>Program</th>
                                <th>Time Category</th>
                                <th>Matric</th>
                                <th>Inter</th>
                                <th>Merit Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $current_program = '';
                            $current_time = '';
                            $rank = 1;
                            foreach ($apps_data as $app): 
                                if ($current_program != $app['program_choice'] || $current_time != $app['time_category']) {
                                    $current_program = $app['program_choice'];
                                    $current_time = $app['time_category'];
                                    $rank = 1;
                                }
                            ?>
                                <tr>
                                    <td><strong><?php echo $rank++; ?></strong></td>
                                    <td><?php echo htmlspecialchars($app['name']); ?></td>
                                    <td><?php echo htmlspecialchars($app['email']); ?></td>
                                    <td><?php echo htmlspecialchars($app['program_choice']); ?></td>
                                    <td><?php echo htmlspecialchars($app['time_category']); ?></td>
                                    <td><?php echo number_format($app['matric_marks'], 2); ?></td>
                                    <td><?php echo number_format($app['inter_marks'], 2); ?></td>
                                    <td><strong><?php echo number_format($app['merit_score'], 2); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="text-align: center; padding: 40px; color: #999;">No applications found</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>