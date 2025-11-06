<?php
require_once '../config/config.php';
require_admin();

$success = '';
$error = '';

$SEATS_PER_DEPT = 60;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate'])) {
    $cutoff_score = floatval($_POST['cutoff_score']);
    $merit_list_number = intval($_POST['merit_list_number']);
    
    $check_query = "SELECT COUNT(*) as count FROM merit_list WHERE merit_list_number = $merit_list_number";
    $check_result = mysqli_query($conn, $check_query);
    $check_data = mysqli_fetch_assoc($check_result);
    
    if ($check_data['count'] > 0 && $_POST['action'] != 'regenerate') {
        $error = "Merit List #$merit_list_number already exists. Use a different number or regenerate.";
    } else {
        if ($_POST['action'] == 'regenerate') {
            mysqli_query($conn, "DELETE FROM merit_list WHERE merit_list_number = $merit_list_number");
        }
        
        $seat_status = [];
        $combinations_query = "SELECT DISTINCT program_choice, time_category FROM application";
        $combinations_result = mysqli_query($conn, $combinations_query);
        
        while ($combo = mysqli_fetch_assoc($combinations_result)) {
            $program = $combo['program_choice'];
            $time_category = $combo['time_category'];
            if (!isset($seat_status[$program])) $seat_status[$program] = [];
            
            $confirmed_query = "
                SELECT COUNT(*) as count FROM merit_list m
                JOIN application a ON m.application_id = a.id
                WHERE a.program_choice = '" . mysqli_real_escape_string($conn, $program) . "'
                AND a.time_category = '" . mysqli_real_escape_string($conn, $time_category) . "'
                AND m.status = 'confirmed'";
            $confirmed_result = mysqli_query($conn, $confirmed_query);
            $confirmed_count = mysqli_fetch_assoc($confirmed_result)['count'];
            
            $seat_status[$program][$time_category] = [
                'filled' => $confirmed_count,
                'available' => $SEATS_PER_DEPT - $confirmed_count
            ];
        }

        mysqli_query($conn, "
            UPDATE merit_list 
            SET status = 'not_selected' 
            WHERE status = 'selected' 
            AND merit_list_number < $merit_list_number
            AND student_id NOT IN (
                SELECT student_id FROM fee_payment WHERE payment_status = 'paid'
            )
        ");

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
            $key = $program . '_' . $time_category;
            if (!isset($rank_by_program_time[$key])) $rank_by_program_time[$key] = 1;
            
            if (!isset($seat_status[$program][$time_category])) {
                $seat_status[$program][$time_category] = ['filled' => 0, 'available' => $SEATS_PER_DEPT];
            }
            
            $seats_available = $seat_status[$program][$time_category]['available'];
            $status = ($app['merit_score'] >= $cutoff_score && $seats_available > 0) ? 'selected' : 'not_selected';
            if ($status === 'selected') {
                $seat_status[$program][$time_category]['available']--;
                $seat_status[$program][$time_category]['filled']++;
            }
            
            $insert = "INSERT INTO merit_list (
                application_id, student_id, merit_score, rank, status, merit_list_number
            ) VALUES (
                {$app['id']}, {$app['student_id']}, {$app['merit_score']}, 
                {$rank_by_program_time[$key]}, '$status', $merit_list_number
            )";
            
            if (mysqli_query($conn, $insert)) {
                mysqli_query($conn, "UPDATE application SET status = '$status' WHERE id = {$app['id']}");
                $total_generated++;
            }
            $rank_by_program_time[$key]++;
        }
        
        $success = $total_generated > 0 
            ? "Merit List #$merit_list_number generated successfully! $total_generated entries created." 
            : "Failed to generate merit list.";
    }
}

$current_merit_query = "SELECT MAX(merit_list_number) as latest FROM merit_list";
$current_merit_result = mysqli_query($conn, $current_merit_query);
$current_merit = mysqli_fetch_assoc($current_merit_result);
$latest_merit_number = $current_merit['latest'] ?? 0;
$next_merit_number = $latest_merit_number + 1;

$seat_stats_query = "
    SELECT a.program_choice, a.time_category,
    COUNT(CASE WHEN m.status = 'confirmed' THEN 1 END) as confirmed_seats,
    COUNT(CASE WHEN m.status = 'selected' THEN 1 END) as selected_seats
    FROM application a
    LEFT JOIN merit_list m ON a.id = m.application_id
    GROUP BY a.program_choice, a.time_category
    ORDER BY a.program_choice, a.time_category";
$seat_stats_result = mysqli_query($conn, $seat_stats_query);

$apps_query = "SELECT a.*, s.name, s.email 
               FROM application a 
               JOIN student s ON a.student_id = s.id 
               ORDER BY a.program_choice, a.time_category, a.merit_score DESC";
$apps_result = mysqli_query($conn, $apps_query);

$total_apps = mysqli_num_rows($apps_result);
$apps_data = mysqli_fetch_all($apps_result, MYSQLI_ASSOC);
$highest_score = $total_apps ? $apps_data[0]['merit_score'] : 0;
$lowest_score = $total_apps ? $apps_data[$total_apps - 1]['merit_score'] : 0;
$avg_score = $total_apps ? array_sum(array_column($apps_data, 'merit_score')) / $total_apps : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generate Merit List - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admincss/generate-merit.css">
</head>
<body>
    <div class="navbar">
        <h1>Admin - Generate Merit List</h1>
        <a href="admin_dashboard.php" class="back-btn">⬅ Back to Dashboard</a>
    </div>

    <div class="container">
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php elseif ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

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
            <h2> Seat Availability Status (<?php echo $SEATS_PER_DEPT; ?> seats/program)</h2>
            <table class="seat-stats-table">
                <thead>
                    <tr>
                        <th>Program</th>
                        <th>Time Category</th>
                        <th>Confirmed</th>
                        <th>Selected</th>
                        <th>Available</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($stat = mysqli_fetch_assoc($seat_stats_result)): 
                        $available = $SEATS_PER_DEPT - $stat['confirmed_seats'];
                    ?>
                    <tr>
                        <td><?php echo $stat['program_choice']; ?></td>
                        <td><?php echo $stat['time_category']; ?></td>
                        <td><?php echo $stat['confirmed_seats']; ?></td>
                        <td><?php echo $stat['selected_seats']; ?></td>
                        <td><?php echo max(0, $available); ?></td>
                        <td>
                            <span class="seat-badge <?php echo $available > 0 ? 'seat-available' : 'seat-full'; ?>">
                                <?php echo $available > 0 ? 'Available' : 'Full'; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="form-card">
            <h2> Generate Merit List</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Merit List Number *</label>
                    <input type="number" name="merit_list_number" required value="<?php echo $next_merit_number; ?>">
                </div>
                <div class="form-group">
                    <label>Cutoff Score *</label>
                    <input type="number" name="cutoff_score" step="0.01" required value="75">
                </div>
                <div class="form-group">
                    <label>Action</label>
                    <select name="action">
                        <option value="generate">Generate New</option>
                        <option value="regenerate">Regenerate Existing</option>
                    </select>
                </div>
                <button type="submit" name="generate" class="btn btn-success">Generate Merit List</button>
            </form>
        </div>

        <div class="preview-card">
            <h2>Applications Preview</h2>
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Name</th>
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
                        <td><?php echo $rank++; ?></td>
                        <td><?php echo $app['name']; ?></td>
                        <td><?php echo $app['email']; ?></td>
                        <td><?php echo $app['program_choice']; ?></td>
                        <td><?php echo $app['time_category']; ?></td>
                        <td><?php echo $app['matric_marks']; ?></td>
                        <td><?php echo $app['inter_marks']; ?></td>
                        <td><strong><?php echo $app['merit_score']; ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
