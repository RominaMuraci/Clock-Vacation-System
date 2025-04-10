<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
$rootDir = __DIR__ . '../../../../';
$conn = include($rootDir . 'config/connection.php');

// Check if the user is logged in
if (!isset($_SESSION['login_session']) || !isset($_SESSION['userid'])) {
    echo json_encode(['status' => 'error', 'message' => 'User is not logged in.']);
    exit(); // Exit to stop further execution
}


// Retrieve the logged-in user's details
$fullname = isset($_SESSION['login_session']) ? $_SESSION['login_session'] : '';
$userIdDb = isset($_SESSION['userid']) ? $_SESSION['userid'] : '';
$currentYear = date("Y"); // Current year

try {
    // Prepare query to fetch leave balances for the logged-in user
    $query = "
        SELECT
            brought_forward,
            quota,
            used,
            hire_date
        FROM
            employee_balances
        WHERE
            employee_id = :employee_id
            AND year = :year
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':employee_id', $userIdDb, PDO::PARAM_INT);
    $stmt->bindParam(':year', $currentYear, PDO::PARAM_INT);
    $stmt->execute();
    
    // Fetch the results
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        // Perform the calculations for remaining leave days
        $currentDate = new DateTime(); // Current date
        $hireDate = new DateTime($result['hire_date']);
        $daysWorked = $currentDate->diff($hireDate)->days;
        
        if ($daysWorked < 365) {
            // Proportional leave calculation if the employee has worked less than a year
            $proportionalLeave = $result['quota'] * ($daysWorked / 365);
            $remaining = floor($proportionalLeave + $result['brought_forward'] - $result['used']);
        } else {
            // Standard calculation for employees who have worked more than a year
            $remaining = floor($result['quota'] + $result['brought_forward'] - $result['used']);
        }
        
        // Prepare the final response
        $leaveBalance = [
            'brought_forward' => $result['brought_forward'],
            'quota' => $result['quota'],
            'used' => $result['used'],
            'remaining' => $remaining, // Dynamically calculated
            'hire_date' => $result['hire_date']
        ];
        
        echo json_encode(['status' => 'success', 'leaveBalance' => $leaveBalance]);
    } else {
        // No leave balance found for the user
        echo json_encode(['status' => 'error', 'message' => 'Leave balance not found']);
    }
} catch (PDOException $e) {
    // Log and return database errors
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}

// Close the database connection
$conn = null;
?>
