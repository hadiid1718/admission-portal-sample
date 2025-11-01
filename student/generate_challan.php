<?php
require_once '../config/config.php';
require_login();

// Get student ID from URL
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : $_SESSION['student_id'];

// Get student details
$student_query = "SELECT s.*, a.program_choice, m.rank, m.merit_score 
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

// Fee details
$tuition_fee = 31086;
$service_charges = 21266;
$admission_fee = 30000;
$total_fee = 82352;

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
        <a href="view_merit.php" class="back-btn">← Back to Merit List</a>
    
    
    <?php foreach ($copies as $copy_name => $color): ?>
        <div class="challan-page <?php echo $copy_name != 'Admin Copy' ? 'page-break' : ''; ?>">
            <div class="watermark">QAU</div>
            
            <div class="header" style="border-color: <?php echo $color; ?>">
                <div class="university-name">🎓 QUAID-I-AZAM UNIVERSITY</div>
                <div class="university-details">
                    University Road, Islamabad, Pakistan<br>
                    Ph: +92-51-90642100 | Email: admissions@qau.edu.pk<br>
                    www.qau.edu.pk
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
                    <td class="info-label">Session:</td>
                    <td><?php echo date('Y'); ?></td>
                    <td class="info-label">Issue Date:</td>
                    <td><?php echo date('d-M-Y'); ?></td>
                </tr>
            </table>
            
            <div class="payment-ids">
                <div class="payment-id">📋 <strong>Bill ID:</strong> <?php echo $bill_id; ?></div>
                <div class="payment-id">💳 <strong>KuickPay ID:</strong> <?php echo $kuickpay_id; ?></div>
                <div class="payment-note">
                    ⚠️ Use these IDs for online payment through Bank, 1-Link, ATMs, or mobile banking apps
                </div>
            </div>
            
            <table class="fee-table">
                <thead>
                    <tr>
                        <th style="width: 60%; background-color: <?php echo $color; ?>">Fee Head</th>
                        <th style="width: 40%; text-align: right; background-color: <?php echo $color; ?>">Amount (PKR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Tuition Fee</td>
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
            
            <div class="instructions">
                <div class="instructions-title">📌 PAYMENT INSTRUCTIONS:</div>
                <ul>
                    <li>This challan is valid for <strong>15 days</strong> from the date of issue.</li>
                    <li>Pay the <strong>exact amount</strong> mentioned above.</li>
                    <li>Keep the bank receipt for your records.</li>
                    <li><strong>Online Payment:</strong> Use Bill ID or KuickPay ID through:
                        <ul>
                            <li>Internet Banking • Mobile Banking Apps • ATM • 1-Link Kiosks</li>
                        </ul>
                    </li>
                    <li><strong>Cash Payment:</strong> Visit any branch of designated banks with this challan.</li>
                    <li>After payment, submit the paid challan to the <strong>Admission Office</strong>.</li>
                    <li>Admission will be confirmed only after <strong>fee verification</strong>.</li>
                </ul>
            </div>
            
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
            
            <div class="footer">
                <strong>Generated on:</strong> <?php echo date('d-M-Y h:i A'); ?><br>
                This is a computer-generated document. No signature is required.<br>
                <strong>For queries, contact:</strong> admissions@qau.edu.pk | Ph: +92-51-90642100
            </div>
        </div>
    <?php endforeach; ?>
    
    <script>
        // Auto-focus on print button when page loads
        window.onload = function() {
            document.querySelector('.print-btn').focus();
        }
        
        // Optional: Show print dialog automatically
        // Uncomment the line below if you want auto-print
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>