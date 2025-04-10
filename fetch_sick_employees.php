<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$rootDir = __DIR__ . '../../../../';
$conn = include($rootDir . 'config/connection.php');

try {
    // Prepare the SQL query to fetch approved sick leave requests with medical reports
    $sql = "
        SELECT
            employee,
            SUM(no_days) AS total_sick_days
        FROM leave_requests
        WHERE status = 'approved'
          AND leave_type = 'Sick leaves'
          AND medical_report = 'yes'
        GROUP BY employee;
    ";
    
    // Execute the query
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    // Fetch results as an associative array
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Send the results as JSON
    header('Content-Type: application/json');
    echo json_encode($requests);
} catch (PDOException $e) {
    // Handle any errors
    http_response_code(500);
    echo json_encode([
        'error' => 'An error occurred while fetching leave requests.',
        'details' => $e->getMessage()
    ]);
}
?>
