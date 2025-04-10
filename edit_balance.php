<?php
session_start();

// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$rootDir = __DIR__ . '../../../../';
$conn = include($rootDir . 'config/connection.php');

// Check if the user is logged in by verifying the session variable
if (!isset($_SESSION['login_session']) || !isset($_SESSION['userid'])) {
    header('Location: ../../../../index.php');
    exit();
}

$fullname = isset($_SESSION['login_session']) ? $_SESSION['login_session'] : '';
$userIdDb = isset($_SESSION['userid']) ? $_SESSION['userid'] : '';

// Check if the logged-in user has permission
$checkPermissionSql = "SELECT COUNT(*) AS count FROM admin_permissions_approve WHERE admin_approve_id = ?";
$checkPermissionStmt = $conn->prepare($checkPermissionSql);
$checkPermissionStmt->execute([$userIdDb]);
$result = $checkPermissionStmt->fetch(PDO::FETCH_ASSOC);

if (!$result || $result['count'] <= 0) {
    exit(); // Stop further execution if user lacks permission
}

// Extract and sanitize form data
$employee_id = isset($_POST['employee_id']) ? trim($_POST['employee_id']) : '';
$hire_date = isset($_POST['hire_date']) ? trim($_POST['hire_date']) : '';
$year = isset($_POST['year']) ? trim($_POST['year']) : '';
$quota = isset($_POST['quota']) ? (int)trim($_POST['quota']) : 0;
$used = isset($_POST['used']) ? (int)trim($_POST['used']) : 0;
$brought_forward = isset($_POST['brought_forward']) ? (int)trim($_POST['brought_forward']) : 0;

// Check if required fields are present
if (empty($employee_id) || empty($year) || empty($hire_date)) {
    echo json_encode(['status' => 'error', 'message' => 'Employee ID, Year, and Hire Date are required.']);
    exit;
}

$currentDate = new DateTime();
$hireDate = new DateTime($hire_date);

// Calculate the difference in months between current date and hire date
$monthsWorked = $hireDate->diff($currentDate)->m + ($hireDate->diff($currentDate)->y * 12); // Months worked

// Determine the remaining leave based on the months worked and pro-rata calculation
if ($monthsWorked < 12) {
    // New employee calculation (pro-rata basis based on months worked)
    $vacationPerMonth = $quota / 12;  // Monthly vacation days
    $calculatedRemaining = min($quota, $vacationPerMonth * $monthsWorked);  // Ensure it doesn't exceed the total quota
    
    // Add brought forward and subtract used leave
    $remaining = $calculatedRemaining + $brought_forward - $used;
} else {
    // Employee has worked more than 1 year (annual allowance)
    $remaining = $brought_forward + $quota - $used;
}

// Ensure the remaining leave is not negative
$remaining = max(0, round($remaining));  // Round to remove decimals

// Check if the record exists
$checkSql = "SELECT COUNT(*) FROM employee_balances WHERE employee_id = :employee_id AND year = :year";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bindValue(':employee_id', $employee_id, PDO::PARAM_INT);
$checkStmt->bindValue(':year', $year, PDO::PARAM_INT);
$checkStmt->execute();
$recordExists = $checkStmt->fetchColumn();

if ($recordExists) {
    // Update existing record
    $sql = "UPDATE employee_balances
            SET quota = :quota, used = :used, brought_forward = :brought_forward, remaining = :remaining, hire_date = :hire_date
            WHERE employee_id = :employee_id AND year = :year";
} else {
    // Insert new record
    $sql = "INSERT INTO employee_balances (employee_id, year, quota, used, brought_forward, remaining, hire_date)
            VALUES (:employee_id, :year, :quota, :used, :brought_forward, :remaining, :hire_date)";
}

$stmt = $conn->prepare($sql);
$stmt->bindValue(':employee_id', $employee_id, PDO::PARAM_INT);
$stmt->bindValue(':hire_date', $hire_date, PDO::PARAM_STR);
$stmt->bindValue(':year', $year, PDO::PARAM_INT);
$stmt->bindValue(':quota', $quota, PDO::PARAM_INT);
$stmt->bindValue(':used', $used, PDO::PARAM_INT);
$stmt->bindValue(':brought_forward', $brought_forward, PDO::PARAM_INT);
$stmt->bindValue(':remaining', $remaining, PDO::PARAM_INT);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Leave balance updated successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update leave balance.']);
}
?>
