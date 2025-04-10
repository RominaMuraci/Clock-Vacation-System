<?php
session_start();

// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details
$dbHost     = "localhost";
$dbUsername = "";
$dbPassword = "";
$dbName     = "";

// Redirect to login page if the user is not authenticated
if (!isset($_SESSION['login_session']) || !isset($_SESSION['userid'])) {
    header('Location: ../../../index.php');
    exit();
}

// Fetch user data from session
$userIdDb = $_SESSION['userid'];
$fullname = $_SESSION['login_session'];

// Create database connection
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]));
}

// Check if the user has admin permissions
$adminCheckSql = "SELECT COUNT(*) as count FROM admin_permissions_approve WHERE admin_approve_id = ?";
$stmt = $conn->prepare($adminCheckSql);
if (!$stmt) {
    die(json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]));
}

$stmt->bind_param("i", $userIdDb);
$stmt->execute();
$stmt->bind_result($adminCount);
$stmt->fetch();
$stmt->close();

$isAdmin = $adminCount > 0;

// Prepare the appropriate SQL query based on user permissions
if ($isAdmin) {
    // Admin user: Show all records
    $sql = "SELECT employee_id, employee, hire_date, year, quota, used, brought_forward FROM employee_balances";
} else {
    // Regular user: Show only their own records
    $sql = "SELECT employee_id, employee, hire_date, year, quota, used, brought_forward FROM employee_balances WHERE employee_id = ?";
}

// Prepare the SQL statement
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]));
}

// Bind parameters if the user is not an admin
if (!$isAdmin) {
    $stmt->bind_param("i", $userIdDb);
}

// Execute the query
$stmt->execute();

// Bind the result columns to variables
$stmt->bind_result($employee_id, $employee, $hireDate, $year, $quota, $used, $brought_forward);

// Fetch the results into an array
$data = [];
$currentDate = new DateTime(); // Current date is today
// Hardcode the current date as December 15, 2024
// $currentDate = new DateTime('2025-09-15');

$oneYearAgo = (new DateTime())->modify('-1 year'); // One year ago from today

while ($stmt->fetch()) {
    // Convert hire date to DateTime object for calculation
    $hireDateObj = new DateTime($hireDate);
    
    // Calculate the difference in days between current date and hire date
    $daysWorked = $currentDate->diff($hireDateObj)->days;
    
    // Proportional leave based on days worked if less than a year
    if ($daysWorked < 365) {
        $proportionalLeave = $quota * ($daysWorked / 365);
        $remaining = $proportionalLeave + $brought_forward - $used;
    } else {
        $remaining = $quota + $brought_forward - $used;
    }
    
    // Ensure the result is rounded down
    $remaining = (floor($remaining)); // Use floor to match JavaScript behavior
    
    
    // Add each employee's data to the array with the dynamically calculated remaining days
    $data[] = [
        'employee_id' => $employee_id,
        'employee' => $employee,
        'hire_date' => $hireDate,
        'year' => $year,
        'quota' => $quota,
        'used' => $used,
        'brought_forward' => $brought_forward,
        'remaining' => $remaining  // Dynamically calculated without decimals
    ];
}

// Close the statement and connection
$stmt->close();
$conn->close();

// Include the isAdmin status in the JSON response
echo json_encode(['status' => 'success', 'data' => $data, 'isAdmin' => $isAdmin]);
?>
