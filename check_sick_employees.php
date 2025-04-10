<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
$rootDir = __DIR__ . '/../../../'; // Adjusted for the directory structure
$conn = include($rootDir . 'config/connection.php');

$fullname = isset($_SESSION['login_session']) ? $_SESSION['login_session'] : '';
$userIdDb = isset($_SESSION['userid']) ? $_SESSION['userid'] : '';

if (!isset($_SESSION['login_session']) || !isset($_SESSION['userid'])) {
    header('Location: ../../../index.php');
    exit(); // Redirect and exit if the user is not logged in
}

// Check if the user has admin permissions


// Check if the date range was passed through GET
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    // Sanitize input to prevent SQL Injection
    $startDate = $_GET['start_date'];
    $endDate = $_GET['end_date'];
    
    try {
        // Modify the SQL query based on whether the user is an admin or not
        $sql = "SELECT
                DATE(start_date) AS date,
                employee AS sick_employee,
                SUM(no_days) AS total_sick_days
            FROM leave_requests
            WHERE status = 'approved'
            AND leave_type = 'Sick leaves'
            AND (start_date <= :end_date AND end_date >= :start_date)
            GROUP BY DATE(start_date), employee
            ORDER BY DATE(start_date), sick_employee ASC";
        
        
        // Prepare the statement
        $stmt = $conn->prepare($sql);
        
        // Bind parameters to prevent SQL injection
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        
        
        // Execute the statement
        if ($stmt->execute()) {
            // Fetch all results
            $sickEmployees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format the summarized data for JSON response
            $result = [];
            foreach ($sickEmployees as $employee) {
                $employeeName = $employee['sick_employee'];
                $date = $employee['date'];
                $totalSickDays = (int)$employee['total_sick_days'];
                
                // Initialize the employee entry if it doesn't exist
                if (!isset($result[$employeeName])) {
                    $result[$employeeName] = [
                        'dates' => [],
                        'total' => 0
                    ];
                }
                
                // Add the date to the employee's dates and accumulate the total sick days
                $result[$employeeName]['dates'][] = $date;
                $result[$employeeName]['total'] += $totalSickDays;
            }
            
            $finalOutput = [
                'sick_interval' => "Sick Interval {$startDate} - {$endDate}",
                'employees' => []
            ];
            
            foreach ($result as $employee => $data) {
                $total = $data['total'];
                
                $finalOutput['employees'][] = [
                    'sick_interval' => "{$startDate} - {$endDate}",  // Include sick_interval per employee
                    'total_sick_days' => $total,
                    'sick_employee' => $employee
                ];
            }

// Return the results as JSON
            echo json_encode(['status' => 'success', 'data' => $finalOutput]);
        } else {
            // Query execution failed
            echo json_encode(['status' => 'error', 'message' => 'Query execution failed.']);
        }
    } catch (Exception $e) {
        // Catch any exceptions and return a proper JSON error response
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    // Error if start_date or end_date are not provided
    echo json_encode(['status' => 'error', 'message' => 'Both start and end dates must be provided!']);
}
?>
