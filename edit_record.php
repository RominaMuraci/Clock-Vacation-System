<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$rootDir = __DIR__ . '../../../../';
$conn = include($rootDir . 'config/connection.php');

// Debug: Print incoming POST data
var_dump($_POST);

// Ensure POST data is being received correctly
$clock_id = isset($_POST['clock_id']) ? intval(trim($_POST['clock_id'])) : 0;

if ($clock_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Clock ID.']);
    exit;
}

// Check if the record exists
$checkSql = "SELECT COUNT(*) FROM timeclock WHERE clock_id = :clock_id";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bindValue(':clock_id', $clock_id, PDO::PARAM_INT);
$checkStmt->execute();
$recordExists = $checkStmt->fetchColumn();

if (!$recordExists) {
    echo json_encode(['status' => 'error', 'message' => 'No record found for the provided Clock ID.']);
    exit;
}

// Fetch the form data
$day = isset($_POST['day']) ? trim($_POST['day']) : null;
// $employee = isset($_POST['employee']) ? trim($_POST['employee']) : null;  // Added employee variable
$clock_in = isset($_POST['clock_in']) ? trim($_POST['clock_in']) : null;
$clock_out = isset($_POST['clock_out']) ? trim($_POST['clock_out']) : null;
$tot_hours = isset($_POST['tot_hours']) ? trim($_POST['tot_hours']) : null;
$break_duration_sum = isset($_POST['break_duration_sum']) ? trim($_POST['break_duration_sum']) : null;
$daily_total = isset($_POST['daily_total']) ? trim($_POST['daily_total']) : null;
$regular_hours = isset($_POST['regular_hours']) ? trim($_POST['regular_hours']) : null;
$overtime = isset($_POST['overtime']) ? trim($_POST['overtime']) : null;

// Prepare the update SQL statement
$sql = "UPDATE timeclock 
        SET 
            day = :day, 
            clock_in = :clock_in, 
            clock_out = :clock_out, 
            tot_hours = :tot_hours, 
            break_duration_sum = :break_duration_sum, 
            daily_total = :daily_total, 
            regular_hours = :regular_hours, 
            overtime = :overtime 
        WHERE 
            clock_id = :clock_id";

$stmt = $conn->prepare($sql);

// Bind the parameters to the statement
$stmt->bindValue(':clock_id', $clock_id, PDO::PARAM_INT);
$stmt->bindValue(':day', $day);
// $stmt->bindValue(':employee', $employee);  // Ensure 'employee' is bound correctly
$stmt->bindValue(':clock_in', $clock_in);
$stmt->bindValue(':clock_out', $clock_out);
$stmt->bindValue(':tot_hours', $tot_hours);
$stmt->bindValue(':break_duration_sum', $break_duration_sum);
$stmt->bindValue(':daily_total', $daily_total);
$stmt->bindValue(':regular_hours', $regular_hours);
$stmt->bindValue(':overtime', $overtime);

// Execute the statement and handle the result
if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Timeclock record updated successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update timeclock record.']);
}

// Close the statement and connection
$checkStmt->closeCursor();
$stmt->closeCursor();
$conn = null; // PDO connection is closed by setting it to null
?>
