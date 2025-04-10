<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection details
$dbHost = "localhost";
$dbUsername = "erald";
$dbPassword = "erald1232!";
$dbName = "asterisk";

// Create database connection
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => $conn->connect_error]));
}

// Handle POST request to add new holiday
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        $holidayDate = $_POST['holidayDate'];
        $holidayName = $_POST['holidayName'];

        if (empty($holidayDate) || empty($holidayName)) {
            echo json_encode(['success' => false, 'error' => 'Date and Name are required']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO holidays (date, name) VALUES (?, ?)");
        $stmt->bind_param("ss", $holidayDate, $holidayName);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Holiday added successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }

        $stmt->close();
        $conn->close();
        exit;
    }
}

// Handle DELETE request to remove holiday
if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $holidayDate = $_DELETE['holidayDate'];

    if (empty($holidayDate)) {
        echo json_encode(['success' => false, 'error' => 'Date is required']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM holidays WHERE date = ?");
    $stmt->bind_param("s", $holidayDate);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Holiday deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
    exit;
}

// Fetch holidays from the database
$sql = "SELECT date, name FROM holidays";
$result = $conn->query($sql);

$holidays = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $holidays[] = $row;
    }
}

$conn->close();

// Return holidays as JSON
header('Content-Type: application/json');
echo json_encode($holidays);
?>
