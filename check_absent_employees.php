<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
$rootDir = __DIR__ . '/../../../'; // Adjusted for the directory structure
$conn = include($rootDir . 'config/connection.php');

// Retrieve session data for user authentication
$fullname = isset($_SESSION['login_session']) ? $_SESSION['login_session'] : '';
$userIdDb = isset($_SESSION['userid']) ? $_SESSION['userid'] : '';

// Redirect if the user is not logged in
if (empty($fullname) || empty($userIdDb)) {
    header('Location: ../../../index.php');
    exit();
}

// Check if the date range was passed through GET
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    // Sanitize and validate dates
    $startDate = filter_var($_GET['start_date'], FILTER_SANITIZE_STRING);
    $endDate = filter_var($_GET['end_date'], FILTER_SANITIZE_STRING);
    
    // Create an array of dates
    $dateRange = [];
    $currentDate = new DateTime($startDate);
    $endDateTime = new DateTime($endDate);
    
    while ($currentDate <= $endDateTime) {
        $dateRange[] = $currentDate->format('Y-m-d');
        $currentDate->modify('+1 day');
    }
    
    // Prepare the SQL query with dynamic date values
    $unionQueries = [];
    foreach ($dateRange as $date) {
        $unionQueries[] = "SELECT '$date' AS date"; // Use single quotes to insert the date as a string
    }
    
    // Combine all union queries
    $unionQuery = implode(' UNION ALL ', $unionQueries);
    
    $sql = "SELECT
                dr.date,
                COALESCE(COUNT(lr.employee_id), 0) AS num_of_absent_employees,
                GROUP_CONCAT(lr.employee SEPARATOR ', ') AS absent_employees
            FROM
                ($unionQuery) AS dr
            LEFT JOIN
                leave_requests lr
            ON dr.date BETWEEN lr.start_date AND lr.end_date
            AND lr.status = 'approved'
            GROUP BY
                dr.date
            ORDER BY
                dr.date ASC";
    
    // Prepare the statement
    $stmt = $conn->prepare($sql);
    
    // Execute and fetch results
    if ($stmt->execute()) {
        $employeesOnVacation = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Output as JSON
        $response = [
            'status' => 'success',
            'employees' => $employeesOnVacation
        ];
        echo json_encode($response);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Query execution failed.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Both start and end dates must be provided!']);
}
?>
