<?php
session_start();
include 'login.php'; // Include your database connection details

// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

$rootDir = __DIR__ . '../../../../';
// Include your email service class


// Include database connection
$conn = include($rootDir . 'config/connection.php');


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve selected user IDs from POST data
    $selectedUserIds = isset($_POST['selectusers']) ? $_POST['selectusers'] : [];

    // Prepare the SQL statements
    $deleteSql = "DELETE FROM user_permissions_leave WHERE admin_id = :admin_id";
    $insertSql = "INSERT INTO user_permissions_leave (admin_id) VALUES (:admin_id)";

    // Prepare statements
    try {
        $deleteStmt = $conn->prepare($deleteSql);
        $insertStmt = $conn->prepare($insertSql);
    } catch (PDOException $e) {
        error_log("Prepare failed: " . $e->getMessage());
        die("Prepare failed: " . $e->getMessage());
    }

    // Fetch current user IDs from the database
    $currentUserIds = [];
    try {
        $stmt = $conn->query("SELECT admin_id FROM user_permissions_leave");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $currentUserIds[] = $row['admin_id'];
        }
    } catch (PDOException $e) {
        error_log("Query failed: " . $e->getMessage());
        die("Query failed: " . $e->getMessage());
    }

    // Determine which user IDs need to be deleted
    $toDelete = array_diff($currentUserIds, $selectedUserIds);
    foreach ($toDelete as $userId) {
        $deleteStmt->bindParam(':admin_id', $userId, PDO::PARAM_INT);
        if ($deleteStmt->execute()) {
            echo 'Deleted User ID: ' . htmlspecialchars($userId) . '<br>';
        } else {
            echo 'Failed to delete User ID: ' . htmlspecialchars($userId) . '<br>';
            $errorInfo = $deleteStmt->errorInfo();
            echo 'PDO Error: ' . htmlspecialchars($errorInfo[2]) . '<br>';
        }
    }

    // Determine which user IDs need to be inserted
    $toInsert = array_diff($selectedUserIds, $currentUserIds);
    foreach ($toInsert as $userId) {
        $insertStmt->bindParam(':admin_id', $userId, PDO::PARAM_INT);
        if ($insertStmt->execute()) {
            echo 'Inserted User ID: ' . htmlspecialchars($userId) . '<br>';
        } else {
            echo 'Failed to insert User ID: ' . htmlspecialchars($userId) . '<br>';
            $errorInfo = $insertStmt->errorInfo();
            echo 'PDO Error: ' . htmlspecialchars($errorInfo[2]) . '<br>';
        }
    }

    // Close the statements
    $deleteStmt = null;
    $insertStmt = null;
}

// Close the connection
$conn = null;
// Redirect or show a success message
header("Location: settings.php?success=" . urlencode("Updated successfully"));
exit();
?>
