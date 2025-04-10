<?php
// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details
$dbHost     = "localhost";
$dbUsername = "";
$dbPassword = "";
$dbName     = "";

// Create database connection
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Debug: Check if clock_id is set
if (!isset($_POST['clock_id'])) {
    echo "<tr><td colspan='2'>Invalid request: clock_id not set</td></tr>";
    exit();
}

$clock_id = $_POST['clock_id'];
// Debug: Check the value of clock_id
error_log("Received clock_id: " . $clock_id);

// Prepare SQL statement to fetch notes
$sql = "SELECT notes FROM timeclock WHERE clock_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    // Debug: Check if statement preparation failed
    error_log("SQL statement preparation failed: " . $conn->error);
    echo "<tr><td colspan='2'>SQL statement preparation failed</td></tr>";
    exit();
}

// Bind parameters and execute the statement
$stmt->bind_param('i', $clock_id);
if (!$stmt->execute()) {
    // Debug: Check if statement execution failed
    error_log("SQL statement execution failed: " . $stmt->error);
    echo "<tr><td colspan='2'>SQL statement execution failed</td></tr>";
    exit();
}

// Bind result variables
$stmt->bind_result($notes);

// Fetch result
$notesHtml = '';
if ($stmt->fetch()) {
    // Debug: Check the notes content
    error_log("Fetched notes: " . $notes);

    // Parse notes by splitting them with commas
    $noteEntries = explode(',', $notes);
    foreach ($noteEntries as $entry) {
        // Debug: Check the note entry
        error_log("Note entry: " . $entry);

        // Use regex to extract timestamp, status, and the actual note
        if (preg_match('/^(.*?)(\[.*?\]) (.*)$/', trim($entry), $matches)) {
            // matches[1] => timestamp
            // matches[2] => status (including brackets)
            // matches[3] => actual note
            $timestamp = htmlspecialchars(trim($matches[1]));
            $status = htmlspecialchars(trim($matches[2]));
            $note = htmlspecialchars(trim($matches[3]));

            // Build HTML row
            $notesHtml .= "<tr><td>" . $timestamp . "</td><td>" . $status . "</td><td>" . $note . "</td></tr>";
        } else {
            // Handle cases where the format is not as expected
            error_log("Failed to parse note entry: " . $entry);
            $notesHtml .= "<tr><td colspan='3'>Failed to parse note entry: " . htmlspecialchars($entry) . "</td></tr>";
        }
    }
    
    echo $notesHtml;
} else {
    echo "<tr><td colspan='3'>No notes found</td></tr>";
}

// Clean up
$stmt->close();
$conn->close();
?>
