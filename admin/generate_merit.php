<?php

require_once '../config/config.php';
require_admin();

$success = '';
$error = '';

// Handle merit list generation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate'])) {
    $cutoff_score = floatval($_POST['cutoff_score']);
    
    // Clear existing merit list
    mysqli_query($conn, "DELETE FROM merit_list");
    
    // Get all applications sorted by merit score
    $query = "SELECT * FROM application ORDER BY merit_score DESC";
    $result = mysqli_query($conn, $query);
    
    $rank = 1;
    $total_generated = 0;
    
    while ($app = mysqli_fetch_assoc($result)) {
        $status = $app['merit_score'] >= $cutoff_score ? 'selected' : 'not_selected';
        
        $insert = "INSERT INTO merit_list (application_id, student_id, merit_score, rank, status) 
                   VALUES ({$app['id']}, {$app['student_id']}, {$app['merit_score']}, $rank, '$status')";
        
        if (mysqli_query($conn, $insert)) {
            // Update application status
            $update_status = $status == 'selected' ? 'selected' : 'not_selected';
            mysqli_query($conn, "UPDATE application SET status = '$update_status' WHERE id = {$app['id']}");
            $total_generated++;
        }
        
        $rank++;
    }
    
    if ($total_generated > 0) {
        $success = "Merit list generated successfully! $total_generated entries created.";
    } else {
        $error = "Failed to generate merit list.";
    }
}

// Get applications for preview
$apps_query = "SELECT a.*, s.name, s.email 
               FROM application a 
               JOIN student s ON a.student_id = s.id 
               ORDER BY a.merit_score DESC";
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
        
        <div class="form-card">
            <h2>Generate Merit List</h2>
            
           
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Cutoff Merit Score *</label>
                    <input type="number" name="cutoff_score" step="0.01" required 
                           placeholder="e.g., 75.00" value="75">
                    <small style="display: block; margin-top: 5px; color: #666;">
                        Students scoring at or above this value will be selected
                    </small>
                </div>
                
                <button type="submit" name="generate" class="btn btn-success" 
                        onclick="return confirm('This will replace any existing merit list. Are you sure you want to continue?')">
                    Generate & Publish Merit List
                </button>
            </form>
        </div>
        
        <div class="preview-card">
            <h2>Applications Preview (Ranked by Merit Score)</h2>
            
            <?php if ($total_apps > 0): ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th>Program</th>
                                <th>Matric Marks</th>
                                <th>Inter Marks</th>
                                <th>Merit Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $rank = 1;
                            foreach ($apps_data as $app): 
                            ?>
                                <tr>
                                    <td><strong><?php echo $rank++; ?></strong></td>
                                    <td><?php echo htmlspecialchars($app['name']); ?></td>
                                    <td><?php echo htmlspecialchars($app['email']); ?></td>
                                    <td><?php echo htmlspecialchars($app['program_choice']); ?></td>
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