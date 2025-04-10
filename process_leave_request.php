<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Database connection details
$dbHost     = "localhost";
$dbUsername = "";
$dbPassword = "";
$dbName     = "";

// Create database connection
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Check if the user is logged in
$employee_id = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
if (!$employee_id) {
    die(json_encode(["error" => "User not logged in or session expired"]));
}

// Get the form data
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : null;
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : null;
$leave_type = isset($_POST['leave_type']) ? $_POST['leave_type'] : 'Paid off'; // Default to 'Paid off'

if (!$start_date || !$end_date) {
    die(json_encode(["error" => "Start date and end date are required"]));
}

// Validate dates
try {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    if ($start > $end) {
        die(json_encode(["error" => "Start date must be before or equal to end date"]));
    }
} catch (Exception $e) {
    die(json_encode(["error" => "Invalid date format: " . $e->getMessage()]));
}

// Insert leave request into the database
$query = "INSERT INTO leave_requests (employee, start_date, end_date, leave_type) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssss", $employee_id, $start_date, $end_date, $leave_type);

if ($stmt->execute()) {
    echo json_encode(["success" => "Leave request submitted successfully"]);
} else {
    die(json_encode(["error" => "Failed to submit leave request: " . $conn->error]));
}

$stmt->close();
$conn->close();
?>
