<?php
// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$rootDir = __DIR__ . '../../../../';
// Include your email service class
include '../../../Classes/EmailService.php';

// Include the PDO connection file
$conn = include($rootDir . 'config/connection.php');

if (!$conn) {
    error_log("Database connection failed.");
    exit;
}

// Set timezone
date_default_timezone_set('Europe/Tirane');

$today = date('Y-m-d');

// Fetch all users from the database
$users = [];
try {
    $sql = "SELECT userid, email, firstname, lastname FROM users WHERE accesslevel IN ('admin', 'noc', 'other')";
    $stmt = $conn->query($sql);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $users[] = [
            'userid' => htmlspecialchars($row['userid']),
            'fullname' => htmlspecialchars($row['firstname'] . " " . $row['lastname']),
            'email' => htmlspecialchars($row['email']),
        ];
    }
} catch (PDOException $e) {
    error_log("Query failed: " . $e->getMessage());
    exit();
}

try {
    $conn->beginTransaction();
    
    // Prepare the query to check clock-ins
    $check_clockin_query = "SELECT employee_id FROM timeclock WHERE day = :date AND employee_id = :employee_id";
    $stmt_check_clockin = $conn->prepare($check_clockin_query);
    
    // Prepare the query to check leave requests
    $check_leave_query = "SELECT employee_id FROM leave_requests WHERE :date BETWEEN start_date AND end_date AND employee_id = :employee_id";
    $stmt_check_leave = $conn->prepare($check_leave_query);
    
    foreach ($users as $user) {
        $employee_id = $user['userid'];
        $email = $user['email'];
        $fullname = $user['fullname'];
        
        // Check if the employee clocked in today
        $stmt_check_clockin->execute([':date' => $today, ':employee_id' => $employee_id]);
        $clockin = $stmt_check_clockin->fetch(PDO::FETCH_ASSOC);
        
        // Check if the employee requested leave for today
        $stmt_check_leave->execute([':date' => $today, ':employee_id' => $employee_id]);
        $leave = $stmt_check_leave->fetch(PDO::FETCH_ASSOC);
        
        // If neither clocked in nor requested leave, send an email
        if (!$clockin && !$leave) {
            // Prepare email details
            $type = 'sendAlert';
            $CCs = []; // Add CCs if needed
            $subject_email = "Test RM Reminder: No Clock-in or Leave Request for " . $today;
            $emailBody = "
            <html>
            <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; }
                .container { width: 80%; margin: auto; padding: 20px; }
                .header { background-color: #f8f9fa; padding: 10px; border-bottom: 2px solid #007bff; }
                .header h1 { margin: 0; font-size: 24px; }
                .content { padding: 20px; }
                .content p { margin: 10px 0; }
                .footer { background-color: #f8f9fa; padding: 10px; text-align: center; }
            </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Reminder: No Clock-in or Leave Request</h1>
                    </div>
                    <div class='content'>
                        <p>Dear $fullname,</p>
                        <p>We noticed that you have not clocked in today, nor have you requested a leave. Please ensure that you either clock in or submit a leave request.</p>
                    </div>
                    <div class='footer'>
                        <p>This is an automated message, please do not reply.</p>
                    </div>
                </div>
            </body>
            </html>";
            
            // Send the email to the user's email address
            $emailSent = EmailService::sendEmail($type, $email, $CCs, $subject_email, $emailBody);
            
            if (!$emailSent) {
                error_log("Failed to send email to $email");
            }
        }
    }
    
    $conn->commit();
} catch (PDOException $e) {
    $conn->rollBack();
    error_log("Database error: " . $e->getMessage());
}

$conn = null;
?>
