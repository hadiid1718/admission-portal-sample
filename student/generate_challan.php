<?php
require_once '../config/config.php';
require_login();

// Get student ID from URL
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : $_SESSION['student_id'];

// Get student details including time_category
$student_query = "SELECT s.*, a.program_choice, a.time_category, m.rank, m.merit_score 
                  FROM student s
                  LEFT JOIN application a ON s.id = a.student_id
                  LEFT JOIN merit_list m ON s.id = m.student_id
                  WHERE s.id = $student_id AND m.status = 'selected'";
$result = mysqli_query($conn, $student_query);

if (mysqli_num_rows($result) == 0) {
    die("Student not found or not selected");
}

$student = mysqli_fetch_assoc($result);

// Generate unique Bill ID and KuickPay ID
$bill_id = 'QAU' . date('Y') . str_pad($student_id, 6, '0', STR_PAD_LEFT);
$kuickpay_id = 'KP' . strtoupper(substr(md5($student_id . time()), 0, 10));

// Date calculations
$issue_date = date('Y-m-d');
$due_date = date('Y-m-d', strtotime($issue_date . ' + 5 days'));
$formatted_issue_date = date('d-M-Y', strtotime($issue_date));
$formatted_due_date = date('d-M-Y', strtotime($due_date));

// Fee details based on time_category
$service_charges = 21096;
$admission_fee = 30000;

// Determine tuition fee based on time category
if ($student['time_category'] === 'Evening/Self Support' || $student['time_category'] === 'Evening') {
    $tuition_fee = 88984;
    $total_fee = 140080;
    $fee_type = 'Evening/Self Support';
} else {
    // Default to Morning/Regular
    $tuition_fee = 31260;
    $total_fee = 82356;
    $fee_type = 'Morning/Regular';
}

// Copies to generate
$copies = array(
    'Student Copy' => '#3498db',
    'Department Copy' => '#27ae60',
    'Hostel Copy' => '#f39c12',
    'Admin Copy' => '#e74c3c'
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Challan - <?php echo htmlspecialchars($student['name']); ?></title>
    <link rel="stylesheet" href="../assets//css//studentcss/challan-generation.css">
</head>
<body>
    <div class="print-button-container no-print">
        <a href="view_merit.php" class="back-btn">← Back to Merit List</a>
        <button onclick="window.print()" class="print-btn">🖨️ Print Challan</button>
    </div>
    
    <div class="challan-container">
    <?php foreach ($copies as $copy_name => $color): ?>
        <div class="challan-page <?php echo $copy_name != 'Admin Copy' ? 'page-break' : ''; ?>">
            <div class="watermark">QAU</div>
            
            <div class="header" style="border-color: <?php echo $color; ?>">
                <div class="university-name"> QUAID-I-AZAM UNIVERSITY</div>
                <div class="header-ids">
                    <div><strong>1Bill ID:</strong> <?php echo $bill_id; ?></div>
                    <div><strong>KuickPay ID:</strong> <?php echo $kuickpay_id; ?></div>
                </div>
            </div>
                  <div class="payment-ids">
                <div class="payment-note">
                    ⚠️ Use these IDs for online payment through Bank, 1-Link, ATMs, or mobile banking apps<br>
                    <strong style="color: #d9534f;"> Valid until: <?php echo $formatted_due_date; ?> (5 days from issue)</strong>
                </div>
            </div>
            
            <div class="copy-type" style="background-color: <?php echo $color; ?>">
                <?php echo strtoupper($copy_name); ?>
            </div>
            
            <div class="challan-title">FEE PAYMENT CHALLAN</div>
            
            <table class="info-table">
                <tr>
                    <td class="info-label">Student Name:</td>
                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                    <td class="info-label">Roll Number:</td>
                    <td><?php echo str_pad($student_id, 6, '0', STR_PAD_LEFT); ?></td>
                </tr>
                <tr>
                    <td class="info-label">CNIC:</td>
                    <td><?php echo htmlspecialchars($student['cnic']); ?></td>
                    <td class="info-label">Merit Rank:</td>
                    <td><?php echo $student['rank']; ?></td>
                </tr>
                <tr>
                    <td class="info-label">Program:</td>
                    <td colspan="3"><?php echo htmlspecialchars($student['program_choice']); ?></td>
                </tr>
                <tr>
                    <td class="info-label">Fee Type:</td>
                    <td><?php echo $fee_type; ?></td>
                    <td class="info-label">Session:</td>
                    <td><?php echo date('Y'); ?></td>
                </tr>
                <tr>
                    <td class="info-label">Issue Date:</td>
                    <td><?php echo $formatted_issue_date; ?></td>
                    <td class="info-label">Due Date:</td>
                    <td><?php echo $formatted_due_date; ?></td>
                </tr>
            </table>
            
    
            
            <table class="fee-table">
                <thead>
                    <tr>
                        <th style="width: 60%; background-color: <?php echo $color; ?>">Fee Head</th>
                        <th style="width: 40%; text-align: right; background-color: <?php echo $color; ?>">Amount (PKR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Tuition Fee <?php echo $fee_type === 'Evening/Self Support' ? '(Self Support)' : '(Regular)'; ?></td>
                        <td style="text-align: right;"><?php echo number_format($tuition_fee); ?></td>
                    </tr>
                    <tr>
                        <td>Service Charges</td>
                        <td style="text-align: right;"><?php echo number_format($service_charges); ?></td>
                    </tr>
                    <tr>
                        <td>Admission Fee</td>
                        <td style="text-align: right;"><?php echo number_format($admission_fee); ?></td>
                    </tr>
                    <tr class="total-row">
                        <td>TOTAL PAYABLE AMOUNT</td>
                        <td style="text-align: right;">Rs. <?php echo number_format($total_fee); ?>/-</td>
                    </tr>
                </tbody>
            </table>
            
            <div class="signatures">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">Student Signature</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">Cashier Signature & Stamp</div>
                </div>
            </div>
            
      
        </div>
    <?php endforeach; ?>
    </div>
    
    <script src="../assets/script/student-script/generate-challan.js"></script>
</body>
</html>