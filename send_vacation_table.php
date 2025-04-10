<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$rootDir = __DIR__ . '../../../../';
include '../../../Classes/EmailService.php';
$conn = include($rootDir . 'config/connection.php');

if (!$conn) {
    die("Database connection failed.");
}

try {
    // Fetch employee vacation balances
    $sqlBalances = "SELECT * FROM employee_balances";
    $stmtBalances = $conn->prepare($sqlBalances);
    $stmtBalances->execute();
    $balancesData = $stmtBalances->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch approved sick days with medical reports
    $sqlSickDays = "
        SELECT
            employee,
            SUM(no_days) AS total_sick_days
        FROM leave_requests
        WHERE status = 'approved'
          AND leave_type = 'Sick leaves'
          AND medical_report = 'yes'
        GROUP BY employee;
    ";
    $stmtSickDays = $conn->prepare($sqlSickDays);
    $stmtSickDays->execute();
    $sickDaysData = $stmtSickDays->fetchAll(PDO::FETCH_ASSOC);
    
    // Convert sick days data into an associative array for quick lookup
    $sickDaysMap = [];
    foreach ($sickDaysData as $sickRow) {
        $sickDaysMap[$sickRow['employee']] = $sickRow['total_sick_days'];
    }
    
    // Prepare email details
    $type = 'sendAlert';
    $CCs = ["rominamuraci@protech.com.al"]; // Add CC list
    $subject_email = "VMS-Employees Vacation Table";
    
    // Start the HTML email body
    $emailBody = "
    <html>
    <head>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
 .container {
    width: 100%;
    max-width: 800px;
    margin-left: 0; /* Aligns the container to the left */
    background-color: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

        .header {
            text-align: center;
            padding: 25px;
            background-color: #007bff;
            color: white;
            border-radius: 12px 12px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 2.2em;
        }
        .content {
            margin-top: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        .table th, .table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .table th {
            background-color: #f0f0f0;
            color: #333;
            font-weight: 600;
        }
        .table tr:nth-child(even) {
            background-color: #fbfbfb;
        }
        .table tr:hover {
            background-color: #e9ecef;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 14px;
            color: #777;
        }
    </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Employees Vacation Table</h1>
            </div>
            <div class='content'>
                <p>Dear Sir/Madam,</p>
                <p>This email is to notify you about the current status of the employee vacation table. Below are the details:</p>
                <table class='table'>
                    <tr>
                        <th>Employee</th>
                        <th>Hire Date</th>
                        <th>Year</th>
                        <th>Quota</th>
                        <th>Overdue Days from Last years</th>
                        <th>Used</th>
                        <th>Remaining</th>
                        <th>Sick Days</th>
                    </tr>";
    
    // Generate table rows
    foreach ($balancesData as $row) {
        $hireDate = new DateTime($row['hire_date']);
        $currentDate = new DateTime();
        $quota = floatval($row['quota']);
        $broughtForward = floatval($row['brought_forward']);
        $used = floatval($row['used']);
        
        // Calculate remaining vacation days
        $daysWorked = $hireDate->diff($currentDate)->days;
        if ($daysWorked < 365) {
            $proportionalLeave = ($quota * ($daysWorked / 365));
            $remaining = $proportionalLeave + $broughtForward - $used;
        } else {
            $remaining = $quota + $broughtForward - $used;
        }
        $remaining = max(0, floor($remaining));
        
        // Fetch sick days for this employee
        $sickDays = isset($sickDaysMap[$row['employee']]) ? $sickDaysMap[$row['employee']] : 0;
        
        // Add row to the table
        $emailBody .= "
        <tr>
            <td>" . htmlspecialchars($row['employee']) . "</td>
            <td>" . htmlspecialchars($row['hire_date']) . "</td>
            <td>" . htmlspecialchars($row['year']) . "</td>
            <td>" . htmlspecialchars($row['quota']) . "</td>
            <td>" . htmlspecialchars($row['brought_forward']) . "</td>
            <td>" . htmlspecialchars($row['used']) . "</td>
            <td>" . $remaining . "</td>
            <td>" . $sickDays . "</td>
        </tr>";
    }
    
    // Close table and footer
    $emailBody .= "
                </table>
            </div>
            <div class='footer'>
                <p>Please review the details above and take the necessary actions.</p>
                <p>If you have any questions, feel free to reach out to us.</p>
            </div>
        </div>
    </body>
    </html>";
    
    // Send email
    $emailSent = true;
    $sendTo = ["rominamuraci@protech.com.al"];
    foreach ($sendTo as $recipient) {
        $emailSent = $emailSent && EmailService::sendEmail($type, $recipient, $CCs, $subject_email, $emailBody);
    }
    
    if ($emailSent) {
        exit("Email sent successfully.");
    } else {
        exit("Failed to send email.");
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
?>
