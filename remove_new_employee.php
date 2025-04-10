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

// Check if the employee_id is received via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['employee_id'])) {
    $employee_id = $_POST['employee_id'];
    
    try {
        // Prepare the DELETE statement using PDO
        $stmt = $conn->prepare("DELETE FROM employee_balances WHERE employee_id = :employee_id");
        
        // Bind the parameter
        $stmt->bindValue(':employee_id', $employee_id, PDO::PARAM_INT);
        
        // Execute the query
        $stmt->execute();
        
        // Check if any row was deleted
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Employee removed successfully.']);
            
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Employee not found or already removed.']);
        }
    } catch (PDOException $e) {
        // Log the error and output error message as JSON
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An error occurred. Please try again.']);
    }
} else {
    // Output error message as JSON if no employee_id was provided
    echo json_encode(['status' => 'error', 'message' => 'Invalid request. No employee ID provided.']);
}
$conn = null; // PDO connection is closed by setting it to null
?>