<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$rootDir = __DIR__ . '../../../../';

include($rootDir . 'Classes/EmailService.php');
$conn = include($rootDir . 'config/connection.php');

include($rootDir . 'config/app_config.php');
include($rootDir . 'Classes/CMS.php');

if (!$conn) {
    die("Database connection failed.");
}
###################################### CRON EXECUTION CHECK ####################################
if (CMS::Execute($conn)) {
    CMS::logCronStartExecution($conn);
    $TO = CMS::TO($conn);
    $CC = CMS::CC($conn);
################################################################################################
    
    try {
        // Fetch the current toggle state from the database
        $sql = "SELECT year_flag FROM holiday_toggle LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $yearFlag = $stmt->fetchColumn();
        
        if ($yearFlag === false) {
            echo json_encode(['success' => false, 'message' => 'Holiday toggle record not found.']);
            exit;
        }
        
        // If the year_flag is 0, send the email
        if ($yearFlag == 0) {
            // Email details
            $type = 'sendToHR';
            
            $subject_email = "VMS- Reminder to set holidays for this year";
            $currentYear = date("Y"); // Dynamically fetch the current year
            $emailBody = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                p { margin: 10px 0; }
                a.button {
                    display: inline-block;
                    background-color: #007bff;
                    color: #fff;
                    text-decoration: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    font-size: 16px;
                }
                a.button:hover {
                    background-color: #0056b3;
                }
            </style>
        </head>
        <body>
            <p>Dear Sir/Madam,</p>
            <p>We would like to kindly remind you that the holidays for the current year, <strong>$currentYear</strong>, have not yet been set in the system.</p>
            <p>We request you to review and update the holiday schedule at the earliest convenience to ensure a smooth workflow and proper planning.</p>
            <p>You can update the holidays by clicking the button below:</p>
            <p><a href='https://billing.protech.com.al/billing-system/api/v1/Timeclock/add_holidays.php' target='_blank' class='button'>Update Holiday Schedule</a></p>
            <p style='font-weight: bold; color: #d9534f; background-color: #f9e7e7; padding: 10px; border-radius: 5px;'>
                Enable this flag after adding holidays for the current year. This stops daily email reminders. The flag will reset at the start of each year.
            </p>
            <p>If you require any assistance or have questions, please feel free to reach out to us.</p>
            <p>Thank you for your attention to this matter.</p>
            <p>Best regards,</p>
            <p><em>Protech</em></p>
        </body>
        </html>
        ";
            
            if (!empty($TO)) {
                $emailSent = EmailService::sendEmail($type, $TO, $CC, $subject_email, $emailBody);
                if ($emailSent) {
                    echo json_encode(['success' => true, 'message' => 'Email sent successfully.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to send email.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'No recipients found.']);
            }
        } else {
            // Holidays are already set
            echo json_encode(['success' => true, 'message' => 'Holidays are already set for the current year. No email sent.']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database query failed: ' . $e->getMessage()]);
    }
    
    CMS::logCronEndExecution($conn);
} else {
    CMS::logCronExitExecution($conn);
    exit;
}
?>