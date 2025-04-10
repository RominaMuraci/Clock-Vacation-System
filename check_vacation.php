<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include your database connection (PDO connection)
$rootDir = __DIR__ . '../../../../';
$conn = include($rootDir . 'config/connection.php');

// Check if a date was passed through GET
if (isset($_GET['date'])) {
    // Sanitize input (basic sanitization)
    $dateToCheck = $_GET['date'];

    // Prepare the SQL query
    // Use placeholders correctly for bound parameters
    $sql = "SELECT * FROM leave_requests WHERE :dateToCheck BETWEEN start_date AND end_date";
    
    // Prepare the statement
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':dateToCheck', $dateToCheck, PDO::PARAM_STR);
    
    // Execute the statement
    if ($stmt->execute()) {
        $employeesOnVacation = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // If there are results, return them as JSON
        if (count($employeesOnVacation) > 0) {
            echo json_encode(['status' => 'success', 'employees' => $employeesOnVacation]);
        } else {
            // Update the response to indicate success with an empty array
            echo json_encode(['status' => 'success', 'employees' => []]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Query execution failed.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No date provided!']);
}
