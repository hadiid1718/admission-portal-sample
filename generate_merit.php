<?php

require_once 'config.php';
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
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .form-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .form-card h2 {
            color: #333;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        
        input {
            width: 100%;
            max-width: 300px;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
        }
        
        input:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        
 
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
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
        
        .preview-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .preview-card h2 {
            color: #333;
            margin-bottom: 20px;
        }
        
        .selected-row {
            background: #d4edda !important;
        }
        
        .not-selected-row {
            background: #f8d7da !important;
        }
    </style>
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