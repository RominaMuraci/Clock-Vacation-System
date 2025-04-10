<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection details
$dbHost = "localhost";
$dbUsername = "";
$dbPassword = "";
$dbName = "";

// Create database connection
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => $conn->connect_error]));
}

// Check if holidays data is posted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['holidays'])) {
    $holidays = json_decode($_POST['holidays'], true);

    if (empty($holidays)) {
        echo json_encode(['success' => false, 'error' => 'No holidays found in the file.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO holidays (date, name) VALUES (?, ?)");

    foreach ($holidays as $holiday) {
        if (!empty($holiday['date']) && !empty($holiday['name'])) {
            $stmt->bind_param("ss", $holiday['date'], $holiday['name']);
            $stmt->execute();
        }
    }

    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true, 'message' => 'Holidays imported successfully']);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>
