<?php
session_start();
include 'login.php'; // Include your database connection details

// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details

$rootDir = __DIR__ . '../../../../';
$conn = include($rootDir . 'config/connection.php');

// // Debugging output
// echo "<pre>";
// print_r($_POST);
// echo "</pre>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve selected user IDs from POST data
    $selectedUserApproveIds = isset($_POST['select_admin_approve']) ? $_POST['select_admin_approve'] : array();

    // Prepare the SQL statements
    $deleteSql = "DELETE FROM admin_permissions_approve WHERE admin_approve_id = ?";
    $insertSql = "INSERT INTO admin_permissions_approve (admin_approve_id) VALUES (?)";

    // Prepare statements
    $deleteStmt = $conn->prepare($deleteSql);
    $insertStmt = $conn->prepare($insertSql);

    if ($deleteStmt === false || $insertStmt === false) {
        error_log("Prepare failed: " . print_r($conn->errorInfo(), true));
        die("Prepare failed: " . print_r($conn->errorInfo(), true));
    }

    // Fetch current user IDs from the database
    $currentUserIds = [];
    $currentQuery = "SELECT admin_approve_id FROM admin_permissions_approve";
    $currentStmt = $conn->query($currentQuery);
    if ($currentStmt) {
        while ($row = $currentStmt->fetch(PDO::FETCH_ASSOC)) {
            $currentUserIds[] = $row['admin_approve_id'];
        }
    } else {
        error_log("Query failed: " . print_r($conn->errorInfo(), true));
        die("Query failed: " . print_r($conn->errorInfo(), true));
    }

    // Determine which user IDs need to be deleted
    $toDelete = array_diff($currentUserIds, $selectedUserApproveIds);
    foreach ($toDelete as $userId) {
        if ($deleteStmt->execute([$userId])) {
            echo 'Deleted User ID: ' . htmlspecialchars($userId) . '<br>';
        } else {
            echo 'Failed to delete User ID: ' . htmlspecialchars($userId) . '<br>';
            echo 'PDO Error: ' . htmlspecialchars(implode(', ', $deleteStmt->errorInfo())) . '<br>';
        }
    }

    // Determine which user IDs need to be inserted
    $toInsert = array_diff($selectedUserApproveIds, $currentUserIds);
    foreach ($toInsert as $userId) {
        if ($insertStmt->execute([$userId])) {
            echo 'Inserted User ID: ' . htmlspecialchars($userId) . '<br>';
        } else {
            echo 'Failed to insert User ID: ' . htmlspecialchars($userId) . '<br>';
            echo 'PDO Error: ' . htmlspecialchars(implode(', ', $insertStmt->errorInfo())) . '<br>';
        }
    }

    // Close the statements
    $deleteStmt->closeCursor();
    $insertStmt->closeCursor();
}

// Close the connection
$conn = null; // PDO connection is closed by setting it to null
// Redirect or show a success message
header("Location: settings.php?success=" . urlencode("Updated successfully"));
exit();
?>
