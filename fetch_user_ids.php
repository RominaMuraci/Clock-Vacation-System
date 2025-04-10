<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$rootDir = __DIR__ . '../../../../';
$conn = include($rootDir . 'config/connection.php'); // Ensure this returns a PDO instance

if (!$conn) {
    die("Database connection failed.");
}

// Example logged-in user data; replace with actual data retrieval logic
$loggedInUser = [
    'userid' => $_SESSION['userid'], // Replace with actual logged-in user ID
    'fullname' => $_SESSION['login_session'], // Replace with actual logged-in user name
];

// Fetch all users
$users = [];
try {
    $sql = "SELECT userid, firstname, lastname FROM users WHERE accesslevel IN ('admin', 'noc', 'other')";
    $stmt = $conn->query($sql);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $users[] = [
            'userid' => htmlspecialchars($row['userid']),
            'fullname' => htmlspecialchars($row['firstname'] . " " . $row['lastname']),
        ];
    }
} catch (PDOException $e) {
    error_log("Query failed: " . $e->getMessage());
    echo json_encode(['error' => 'Error fetching users.']);
    exit();
}

// Fetch selected user IDs for leave permissions
$selectedUserIds = [];
try {
    $sql = "SELECT admin_id FROM user_permissions_leave";
    $stmt = $conn->query($sql);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $selectedUserIds[] = htmlspecialchars($row['admin_id']);
    }
} catch (PDOException $e) {
    error_log("Query failed: " . $e->getMessage());
    echo json_encode(['error' => 'Error fetching selected user IDs.']);
    exit();
}

// Fetch selected user IDs for approve permissions
$selectedUserApproveIds = [];
try {
    $sql = "SELECT admin_approve_id FROM admin_permissions_approve";
    $stmt = $conn->query($sql);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $selectedUserApproveIds[] = htmlspecialchars($row['admin_approve_id']);
    }
} catch (PDOException $e) {
    error_log("Query failed: " . $e->getMessage());
    echo json_encode(['error' => 'Error fetching selected user approve IDs.']);
    exit();
}

// Return data as JSON
header('Content-Type: application/json');
echo json_encode([
    'allUsers' => $users,
    'selectedUserIds' => $selectedUserIds,
    'selectedUserApproveIds' => $selectedUserApproveIds,
    'loggedInUser' => $loggedInUser
]);
?>
