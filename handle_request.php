<?php
session_start(); // Ensure session is started

error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../../../Classes/EmailService.php';

$rootDir = __DIR__ . '../../../../';
$conn = include($rootDir . 'config/connection.php');

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

$id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : '';
$action = isset($_POST['action']) ? filter_var($_POST['action'], FILTER_SANITIZE_STRING) : '';
$userIdDb = isset($_SESSION['userid']) ? $_SESSION['userid'] : '';

if (!$id || !in_array($action, ['approve', 'decline']) || !$userIdDb) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request parameters.']);
    $conn = null;
    exit;
}

try {
    // Check if the current admin has the permission to approve or decline requests
    $permissionQuery = "SELECT 1 FROM admin_permissions_approve WHERE admin_approve_id = :userId";
    $permissionStmt = $conn->prepare($permissionQuery);
    $permissionStmt->bindParam(':userId', $userIdDb, PDO::PARAM_INT);
    $permissionStmt->execute();
    
    if ($permissionStmt->rowCount() === 0) {
        echo json_encode(['status' => 'error', 'message' => 'User does not have permission to perform this action.']);
        $conn = null;
        exit;
    }
} catch (PDOException $e) {
    error_log("Permission check failed: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Permission check failed.']);
    $conn = null;
    exit;
}

$status = ($action === 'approve') ? 'approved' : 'declined';

try {
    // Fetch the employee details, including email, from the leave_requests and users tables using a JOIN
    $userEmailQuery = "
        SELECT lr.employee, u.email, lr.leave_type, lr.start_date, lr.end_date,lr.start_time, lr.end_time,lr.send_to, lr.no_days, lr.reason, lr.medical_report
        FROM leave_requests lr
        JOIN users u ON lr.employee_id = u.userid
        WHERE lr.id = :id
    ";
    $userEmailStmt = $conn->prepare($userEmailQuery);
    $userEmailStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $userEmailStmt->execute();
    $userDetails = $userEmailStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userDetails) {
        echo json_encode(['status' => 'error', 'message' => 'User not found for the specified leave request.']);
        $conn = null;
        exit;
    }
    
    $userEmail = $userDetails['email'];
    $employeeName = $userDetails['employee'];
    $leaveType = $userDetails['leave_type'];
    $startDate = $userDetails['start_date'];
    $endDate = $userDetails['end_date'];
    $startTime = $userDetails['start_time'];
    $endTime = $userDetails['end_time'];
    $sendTo = $userDetails['send_to'];
    $noDays = $userDetails['no_days'];
    $reason = $userDetails['reason'];
    $medicalReport = $userDetails['medical_report'];
    
    // Update the status in the leave_requests table
    $query = "UPDATE leave_requests SET status = :status WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        $type = 'sendToHR';
        $CCs = [$userEmail]; // Add the employee's email to the CC list
        
        // Prepare the email to the employee notifying them of the decision
        $subject_email = "VMS-Your Leave Request Has Been " . ucfirst($status);
        
        // Build email body
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
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th, .table td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .footer { background-color: #f8f9fa; padding: 10px; text-align: center; }
        </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Leave Request Notification</h1>
                </div>
                <div class='content'>
                    <p>Dear " . htmlspecialchars($employeeName) . ",</p>
                    <p>Your leave request has been <strong>" . htmlspecialchars($status) . "</strong>.</p>
        <p><strong>Leave Details:</strong></p>
                <table class='table'>
                    <tr>
                        <th>Leave Type</th>
                        <td>" . htmlspecialchars($leaveType) . "</td>
                    </tr>
                    <tr>
                        <th>Start Date</th>
                        <td>" . htmlspecialchars($startDate) . "</td>
                    </tr>
                    <tr>
                        <th>End Date</th>
                        <td>" . htmlspecialchars($endDate) . "</td>
                        </tr>";
        
        // Only include start and end times if leave type is "school"
        if ($leaveType === 'School') {
            $emailBody .= "
                <tr>
                    <th>Start Time</th>
                    <td>" . htmlspecialchars($startTime) . "</td>
                </tr>
                <tr>
                    <th>End Time</th>
                    <td>" . htmlspecialchars($endTime) . "</td>
                </tr>";
        }

// Continue with the rest of the leave details
        $emailBody .= "
                    <tr>
                        <th>Number of Days</th>
                        <td>" . htmlspecialchars($noDays) . "</td>
                    </tr>
                         <tr>
                        <th>Assigned to </th>
                        <td>" . htmlspecialchars($sendTo) . "</td>
                    </tr>
                    <tr>
                        <th>Reason</th>
                        <td>" . htmlspecialchars($reason) . "</td>
                    </tr>
                    <tr>
                        <th>Medical Report</th>
                        <td>" . htmlspecialchars($medicalReport) . "</td>
                    </tr>
                </table>
            </div>
                <div class='footer'>
                    <p>If you have any questions, please contact HR.</p>
                </div>
            </div>
        </body>
        </html>";
        
        // Send the email to the employee
        $emailSent = EmailService::sendEmail('sendToHR', $userEmail, [], $subject_email, $emailBody);
        
        // Prepare additional response data
        $responseData = [
            'status' => 'success',
            'message' => 'Leave status updated and email sent to employee.',
            'employee_name' => $employeeName,
            'employee_email' => $userEmail,
            'leave_status' => $status,
            'leave_details' => [
                'leave_type' => $leaveType,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'send_to' => $sendTo,
                'no_days' => $noDays,
                'reason' => $reason,
                'medical_report' => $medicalReport == 'Yes' ? 'Yes' : 'No'
            ]
        ];
        
        if ($emailSent) {
            echo json_encode($responseData);
        } else {
            $responseData['message'] = 'Leave status updated but failed to send email.';
            echo json_encode($responseData);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update leave request.']);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}

// Close the statement and connection
$stmt->closeCursor();
$conn = null;
?>