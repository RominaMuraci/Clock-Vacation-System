<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Database connection
$rootDir = __DIR__ . '../../../../';
$conn = include($rootDir . 'config/connection.php'); // Ensure this returns a valid PDO connection

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

try {
    // Fetch the current toggle state from the database
    $sql = "SELECT year_flag FROM holiday_toggle LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $yearFlag = $stmt->fetchColumn();
    
    if ($yearFlag === false) {
        echo json_encode(['success' => false, 'message' => 'Holiday toggle record not found.']);
    } else {
        // Return the year_flag in JSON format
        echo json_encode(['success' => true, 'year_flag' => (int)$yearFlag]);
    }
} catch (PDOException $e) {
    // Handle the exception and send an error response
    echo json_encode(['success' => false, 'message' => 'Database query failed: ' . $e->getMessage()]);
}

// Close the connection
$conn = null;
?>
