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

// // Debugging: Print POST data
// echo '<pre>';
// print_r($_POST);
// echo '</pre>';

// Retrieve the user's full name and ID from the session
$fullname = isset($_SESSION['login_session']) ? $_SESSION['login_session'] : '';
$userIdDb = isset($_SESSION['userid']) ? $_SESSION['userid'] : '';

// Sanitize and validate input
$leaveType = isset($_POST['leaveType']) ? trim($_POST['leaveType']) : '';
$startDate = isset($_POST['startDate']) ? trim($_POST['startDate']) : '';
$endDate = isset($_POST['endDate']) ? trim($_POST['endDate']) : '';
$startTime = isset($_POST['startTime']) ? trim($_POST['startTime']) : '';
$endTime = isset($_POST['endTime']) ? trim($_POST['endTime']) : '';
$preselectedOptions = isset($_POST['preselectedOptions']) ? trim($_POST['preselectedOptions']) : '';
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

// Corrected assignment for $employee
$employee = isset($_POST['employee_fullname']) && !empty($_POST['employee_fullname'])
    ? trim($_POST['employee_fullname'])
    : (isset($_SESSION['login_session']) ? trim($_SESSION['login_session']) : '');
$employee_id = isset($_POST['selected_employee']) && !empty($_POST['selected_employee'])
    ? trim($_POST['selected_employee'])
    : (isset($_SESSION['userid']) ? trim($_SESSION['userid']) : '');

$medicalReportAvailable = isset($_POST['medicalReportAvailable']) ? $_POST['medicalReportAvailable'] : 'No';
$sendTo = isset($_POST['sendTo']) ? $_POST['sendTo'] : []; // Handle array input
$requestedBy= isset($_POST['employee']) ? trim($_POST['employee']) : '';


// Initialize variable for employee email
$employeeEmail = '';
try {
    $balanceCheckQuery = "SELECT COUNT(*) FROM employee_balances WHERE employee_id = :employee_id";
    $stmtBalanceCheck = $conn->prepare($balanceCheckQuery);
    $stmtBalanceCheck->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
    $stmtBalanceCheck->execute();
    $balanceExists = $stmtBalanceCheck->fetchColumn();
    
    if ($balanceExists == 0) {
        header("Location: request_leave.php?error=" . urlencode("You are not authorized to make a leave request. Please contact HR for assistance."));
        exit();
        
    }
} catch (PDOException $e) {
    error_log("Database error while checking balance: " . $e->getMessage());
    echo json_encode(['error' => 'Database error while checking leave eligibility.']);
    exit();
}
// Fetch the email of the employee using employee_id
$sqlEmail = "SELECT email FROM users WHERE userid = :employee_id"; // Adjust the table name as necessary
$stmtEmail = $conn->prepare($sqlEmail);
$stmtEmail->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
$stmtEmail->execute();

if ($stmtEmail->rowCount() > 0) {
    $resultEmail = $stmtEmail->fetch(PDO::FETCH_ASSOC);
    $employeeEmail = htmlspecialchars($resultEmail['email']); // Sanitize the email
} else {
    echo "No email found for employee ID: $employee_id.<br>";
}

// Debugging output to see fetched email
echo "Fetched employee email: " . $employeeEmail . "<br>";





$uploadedFilePath = '';

// Check if a medical report is available and a file is uploaded
if ($medicalReportAvailable === 'Yes' && isset($_FILES['medicalReportFile'])) {
    // Check for upload errors
    if ($_FILES['medicalReportFile']['error'] !== UPLOAD_ERR_OK) {
        echo "File upload error: " . $_FILES['medicalReportFile']['error'] . "<br>";
    } else {
        $uploadDir = '/var/www/html/billing-system/api/v1/Timeclock/uploads/';
        echo "Upload directory path: " . $uploadDir . "<br>";
        
        // Ensure the upload directory exists and is writable
        if (!is_dir($uploadDir)) {
            echo "Upload directory does not exist.<br>";
        } elseif (!is_writable($uploadDir)) {
            echo "Upload directory is not writable.<br>";
        } else {
            // Sanitize the file name
            $fileName = basename($_FILES['medicalReportFile']['name']);
            $fileName = preg_replace("/[^a-zA-Z0-9\.\-\_]/", "_", $fileName);
            $uploadFile = $uploadDir . $fileName;
            echo "Full file path: " . $uploadFile . "<br>";
            
            // Attempt to move the uploaded file
            if (move_uploaded_file($_FILES['medicalReportFile']['tmp_name'], $uploadFile)) {
                $baseUrl = 'https://billing.protech.com.al/billing-system/api/v1/Timeclock/';
                $uploadedFilePath = $baseUrl . 'uploads/' . $fileName;
                echo "File uploaded successfully. URL: " . $uploadedFilePath . "<br>";
            } else {
                echo "File upload failed. Unable to move the file.<br>";
            }
        }
    }
}
//  else {
//     echo "No file uploaded or medicalReportAvailable is not 'Yes'.<br>";
// }



// Determine which employee_id to use
if (empty($employee)) {
    echo "Employee name is empty or invalid.";
}
// else {
//     echo "Employee name is valid: {$employee}";
// }
if (empty($employee_id)) {
    echo "Employee name is empty or invalid.";
}
// else {
//     echo "Employee name is valid: {$employee_id}";
// }

// Validate data
if (empty($leaveType) || empty($startDate) || empty($endDate) || empty($sendTo) || empty($reason) || empty($employee) || empty($employee_id)) {
    exit();
}

// Validate dates
try {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    if ($start > $end) {
        header("Location: request_leave.php?error=" . urlencode("Start date must be before or equal to end date."));
        exit();
    }
} catch (Exception $e) {
    header("Location: request_leave.php?error=" . urlencode("Invalid date format: " . $e->getMessage()));
    exit();
}

// Fetch holidays
$sql = "SELECT date FROM holidays";
$stmt = $conn->prepare($sql);
$stmt->execute();

$holidays = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $holidays[] = $row['date'];
}

// Calculate the number of weekdays excluding weekends and official holidays
function countValidDays($startDate, $endDate, $holidays) {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $valid_days = 0;
    
    while ($start <= $end) {
        // Check if it's not Saturday (6) or Sunday (7) and not a holiday
        if ($start->format('N') < 6 && !in_array($start->format('Y-m-d'), $holidays)) {
            $valid_days++;
        }
        $start->modify('+1 day');
    }
    
    return $valid_days;
}

$no_days = countValidDays($startDate, $endDate, $holidays);
$sendToStr = implode(',', $sendTo);

// echo '<pre>';
// print_r($_POST);
// echo '</pre>';

// Prepare and execute the SQL statement
// Prepare and execute the SQL statement
$sql = "INSERT INTO leave_requests (leave_type, start_date, end_date, start_time, end_time, send_to, reason, employee, employee_id, no_days, medical_report, medical_report_url, requested_by, employee_email, created_at)
        VALUES (:leaveType, :startDate, :endDate, :startTime, :endTime, :sendToStr, :reason, :employee, :employee_id, :no_days, :medicalReportAvailable, :uploadedFilePath, :requestedBy, :employeeEmail, NOW())";
$stmt = $conn->prepare($sql);

$stmt->bindParam(':leaveType', $leaveType);
$stmt->bindParam(':startDate', $startDate);
$stmt->bindParam(':endDate', $endDate);
$stmt->bindParam(':startTime', $startTime);
$stmt->bindParam(':endTime', $endTime);
$stmt->bindParam(':sendToStr', $sendToStr);
$stmt->bindParam(':reason', $reason);
$stmt->bindParam(':employee', $employee);
$stmt->bindParam(':employee_id', $employee_id);
$stmt->bindParam(':no_days', $no_days, PDO::PARAM_INT);
$stmt->bindParam(':medicalReportAvailable', $medicalReportAvailable);
$stmt->bindParam(':uploadedFilePath', $uploadedFilePath);
$stmt->bindParam(':requestedBy', $requestedBy); // New attribute
$stmt->bindParam(':employeeEmail', $employeeEmail); // Add the employee email

if ($stmt->execute()) {
    // Prepare email details
    $type = 'sendToHR';
    // Add the employee (who is requesting the leave) to the CC list
    $CCs = [$employeeEmail]; // Add the employee's email to the CC list
    $subject_email = "VMS -Leave Request Notification: $employee - $leaveType";
    // Build the URL for the review link
    $reviewLink = 'https://billing.protech.com.al/billing-system/api/v1/Timeclock/display_offdays.php?employee=' . urlencode($employee);
    $sendToFormatted = implode(", ", $sendTo);
    
    // Enhanced email body with inline CSS for button styling
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
                <h1>VMS -Leave Request Notification</h1>
            </div>
            <div class='content'>
                <p>Dear Sir/Madam,</p>
                <p>This email is to notify you that employee <strong>" . htmlspecialchars($employee) . "</strong> is requesting a leave.</p>
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
    if ($leaveType === 'School') {
        $emailBody .= "
                    <tr>
                        <th>Start Time</th>
                        <td>" . (!empty($startTime) ? htmlspecialchars($startTime) : '-') . "</td>
                    </tr>
                    <tr>
                        <th>End Time</th>
                        <td>" . (!empty($endTime) ? htmlspecialchars($endTime) : '-') . "</td>
                    </tr>";
    }
    
    $emailBody .= "
                    
                    
                    
                    <tr>
                        <th>Number of Days</th>
                        <td>" . htmlspecialchars($no_days) . "</td>
                    </tr>
                    <tr>
                        <th>Reason</th>
                        <td>" . htmlspecialchars($reason) . "</td>
                    </tr>
                      <tr>
                        <th>Assigned to </th>
                        <td>" . htmlspecialchars($sendToFormatted) . "</td>
                    </tr>
                    <tr>
                        <th>Medical Report</th>
                        <td>" . htmlspecialchars($medicalReportAvailable) . "</td>
                    </tr>
                    " . ($uploadedFilePath ? "<tr><th>Report Link</th><td><a href='" . htmlspecialchars($uploadedFilePath) . "'>Download Report</a></td></tr>" : "") . "
                </table>
            </div>
           <div class='footer'>
                <p>Please review the details above and take the necessary actions.</p>
                <p>
                    <a href='" . htmlspecialchars($reviewLink) . "'
                       style='display: inline-block; padding: 10px 20px; margin-top: 20px; font-size: 16px; text-align: center; color: #fff; background-color: #007bff; border: none; border-radius: 5px; text-decoration: none; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); transition: background-color 0.3s ease;'>
                       Review and Approve/Decline Leave Request for " . htmlspecialchars($employee) . "
                    </a>
                </p>
            </div>
        </div>
    </body>
    </html>";
    
    // Send the email to each selected recipient
    $emailSent = true; // Initialize as true
    foreach ($sendTo as $recipient) {
        $emailSent = $emailSent && EmailService::sendEmail($type, $recipient, $CCs, $subject_email, $emailBody);
    }
    
    if ($emailSent) {
        header("Location: request_leave.php?success=" . urlencode("Leave request submitted and emails sent successfully."));
        exit(); // Add exit to prevent further script execution
    } else {
        header("Location: request_leave.php?error=" . urlencode("Failed to send email to all recipients."));
        exit();
    }
} else {
    // Redirect to request_leave.php on failure with error message
    header("Location: request_leave.php?error=" . urlencode("Failed to submit leave request."));
    exit();
}

// Close the statement and connection
$stmt->closeCursor();
$conn = null;
?>