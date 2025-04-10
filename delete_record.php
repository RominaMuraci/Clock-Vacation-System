<?php
session_start();

// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

$rootDir = __DIR__ . '../../../../';

// Include database connection
$conn = include($rootDir . 'config/connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clock_id'])) {
    $clock_id = intval($_POST['clock_id']);  // Sanitize the clock_id

    try {
        // Prepare the delete query
        $stmt = $conn->prepare("DELETE FROM timeclock WHERE clock_id = :clock_id");
        // Bind the clock_id parameter
        $stmt->bindParam(':clock_id', $clock_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Return a JSON success message
            echo json_encode(["status" => "success", "message" => "Record deleted successfully"]);
        } else {
            // Return a JSON error message
            echo json_encode(["status" => "error", "message" => "Failed to delete record"]);
        }
    } catch (PDOException $e) {
        // Return a JSON error message in case of an exception
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }

    $stmt = null;
    $conn = null;
}
?>
