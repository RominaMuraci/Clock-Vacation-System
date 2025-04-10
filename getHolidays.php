<?php
session_start();

// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

$rootDir = __DIR__ . '../../../../';

// Include database connection (ensure the file returns a PDO instance)
$conn = include($rootDir . 'config/connection.php');

try {
    // Query to fetch holidays
    $query = "SELECT date, name FROM holidays";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    // Fetch all holidays
    $holidays = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return holidays as JSON
    header('Content-Type: application/json');
    echo json_encode(['holidays' => $holidays]);
    
} catch (PDOException $e) {
    // Handle query or connection errors
    echo "Error: " . $e->getMessage();
}
?>
