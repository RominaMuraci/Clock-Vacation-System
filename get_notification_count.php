<?php
// Database connection details
$dbHost     = "localhost";
$dbUsername = "";
$dbPassword = "";
$dbName     = "";

// Create database connection
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}


// Query to get the count of unseen notifications
$sql = "SELECT COUNT(*) AS count FROM timeclock WHERE notes IS NOT NULL AND notes <> '' AND is_seen = 0 AND employee_id = '$userIdDb'";


$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    $count = $row['count'];
    echo json_encode(['success' => true, 'count' => $count]);
} else {
    echo json_encode(['success' => false, 'error' => 'Query execution failed']);
}

$conn->close();
?>
