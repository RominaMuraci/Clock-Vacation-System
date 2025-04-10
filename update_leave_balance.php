<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$rootDir = __DIR__ . '../../../../';
$conn = include($rootDir . 'config/connection.php');

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    error_log("Database connection failed.");
    exit;
}

// Before any content is sent, ensure only JSON response follows
header('Content-Type: application/json');

$id = isset($_POST['id']) ? intval($_POST['id']) : '';
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action !== 'approve') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
    error_log("Invalid request action: $action");
    $conn = null;
    exit;
}

try {
    // Fetch leave request details
    $query = "SELECT employee_id, employee, no_days, leave_type, medical_report_url FROM leave_requests WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $leaveRequest = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$leaveRequest) {
        echo json_encode(['status' => 'error', 'message' => 'No such leave request.']);
        error_log("No leave request found for ID: $id");
        $conn = null;
        exit;
    }
    
    // Get current year
    $current_year = date("Y");
    
    // Fetch quota for the current year
    $quota_query = "SELECT quota FROM quota_timeclock WHERE year = :year";
    $stmt_quota = $conn->prepare($quota_query);
    $stmt_quota->bindParam(':year', $current_year, PDO::PARAM_INT);
    $stmt_quota->execute();
    $quota_data = $stmt_quota->fetch(PDO::FETCH_ASSOC);
    $stmt_quota->closeCursor();
    
    if (!$quota_data) {
        echo json_encode(['status' => 'error', 'message' => 'No quota defined for the current year.']);
        error_log("No quota found for the year: $current_year");
        $conn = null;
        exit;
    }
    
    $quota = $quota_data['quota'];
    
    
    // Fetch the employee's balance information for the current year
    $balance_query = "SELECT hire_date, used, brought_forward FROM employee_balances WHERE employee_id = :employee_id AND year = :year";
    $stmt_balance = $conn->prepare($balance_query);
    $stmt_balance->bindParam(':employee_id', $leaveRequest['employee_id'], PDO::PARAM_INT);
    $stmt_balance->bindParam(':year', $current_year, PDO::PARAM_INT);
    $stmt_balance->execute();
    $balance = $stmt_balance->fetch(PDO::FETCH_ASSOC);
    
    if (!$balance) {
        echo json_encode(['status' => 'error', 'message' => 'No balance record found for the current year.']);
        error_log("No balance found for employee ID: {$leaveRequest['employee_id']} in year: $current_year");
        $conn = null;
        exit;
    }
    
    // Get the current date
    $currentDate = new DateTime();
    
    // Convert hire date to DateTime object for calculation
    $hireDateObj = new DateTime($balance['hire_date']);
    
    // Calculate the difference in days between current date and hire date
    $daysWorked = $currentDate->diff($hireDateObj)->days;
    
    // Calculate proportional leave for employees who have worked less than a year
    if ($daysWorked < 365) {
        $proportionalLeave = $quota * ($daysWorked / 365); // Prorate the leave based on days worked
        $remaining = $proportionalLeave + $balance['brought_forward'] - $balance['used'];
    } else {
        $remaining = $quota + $balance['brought_forward'] - $balance['used']; // For old employees, no prorating
    }
    
    // Round down the remaining balance
    $remaining = floor($remaining);
    
    // Prepare response variables
    $status = 'success';
    $finalMessage = 'Leave request approved successfully.';
    
    // Check if leave days exceed remaining balance
    if ($leaveRequest['no_days'] > $remaining) {
        $status = 'warning';
        $finalMessage = 'Leave request exceeds available balance. Leave will still be approved with a negative balance.';
        error_log("Leave request exceeds balance for employee ID: {$leaveRequest['employee_id']}");
    }
    
    // Check if the leave type is sick_leaves with a medical report or school leave
    $isSickLeaveWithReport = ($leaveRequest['leave_type'] === 'Sick leaves' && !empty($leaveRequest['medical_report_url']));
    $isSchoolLeave = ($leaveRequest['leave_type'] === 'School');
    
    if ($isSickLeaveWithReport || $isSchoolLeave) {
        // If it's a sick leave with medical report or school leave, do NOT modify `used` or `brought_forward`
        $updated_brought_forward = $balance['brought_forward'];
        $updated_used = $balance['used'];
    } else {
        // For non-sick leaves or sick leave without medical report, adjust `used` and `brought_forward`
        $daysToDeduct = $leaveRequest['no_days'];
        $updated_brought_forward = $balance['brought_forward'];
        $updated_used = $balance['used'];
        
        if ($updated_brought_forward >= $daysToDeduct) {
            // Deduct entirely from `brought_forward`
            $updated_brought_forward -= $daysToDeduct;
        } else {
            // Partially deduct from `brought_forward` and the rest from `used`
            $daysToDeduct -= $updated_brought_forward;
            $updated_brought_forward = 0;
            $updated_used += $daysToDeduct;
        }
        
        // Ensure the updated brought_forward doesn't go negative
        $updated_brought_forward = max(0, $updated_brought_forward);
    }
    
    // Update the employee's balance in the database only if it's not a sick leave with a report
    if (!$isSickLeaveWithReport) {
        $update_balance_query = "UPDATE employee_balances SET used = :used, brought_forward = :brought_forward WHERE employee_id = :employee_id AND year = :year";
        $stmt_update = $conn->prepare($update_balance_query);
        $stmt_update->bindParam(':used', $updated_used, PDO::PARAM_INT);
        $stmt_update->bindParam(':brought_forward', $updated_brought_forward, PDO::PARAM_INT);
        $stmt_update->bindParam(':employee_id', $leaveRequest['employee_id'], PDO::PARAM_INT);
        $stmt_update->bindParam(':year', $current_year, PDO::PARAM_INT);
        
        if (!$stmt_update->execute()) {
            error_log("Failed to update leave balance: " . implode(" ", $stmt_update->errorInfo()));
            echo json_encode(['status' => 'error', 'message' => 'Failed to update leave balance.']);
            $conn = null;
            exit;
        }
    }
    
    // Respond with combined status
    echo json_encode(['status' => $status, 'message' => $finalMessage]);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}

$conn = null;
?>
