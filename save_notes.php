<?php
session_start();

// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

$rootDir = __DIR__ . '../../../../';

// Include database connection
$conn = include($rootDir . 'config/connection.php');

// Check for session variables
$fullname = isset($_SESSION['login_session']) ? $_SESSION['login_session'] : '';
$userIdDb = isset($_SESSION['userid']) ? $_SESSION['userid'] : '';

if (!$fullname || !$userIdDb) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

// Check if form data exists and is not empty
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $newNote = isset($_POST['notes']) ? trim($_POST['notes']) : ''; // Get the notes and trim whitespace
    $currentTimestamp = date('Y-m-d H:i:s'); // Get the current timestamp in server time

    // Check if notes are empty
    if (empty($newNote)) {
        echo json_encode(['success' => false, 'error' => 'Notes cannot be empty.']);
        exit;
    }

    // Get the clock status sent from the form
    $status = isset($_POST['status']) ? trim($_POST['status']) : ''; // Use status from form data

    // Validate the status (optional: you can check against a set of expected values)
    if (empty($status)) {
        echo json_encode(['success' => false, 'error' => 'Status cannot be empty.']);
        exit;
    }

    // Format the new note with its timestamp and status
    $newNoteWithTimestamp = $currentTimestamp . ' [' . htmlspecialchars($status) . '] ' . htmlspecialchars($newNote);

    try {
        // Prepare and execute the statement to fetch existing notes
        $stmt = $conn->prepare("SELECT notes FROM timeclock WHERE employee_id = :userId AND day = CURDATE()");
        $stmt->bindValue(':userId', $userIdDb, PDO::PARAM_INT);
        $stmt->execute();
        $existingNotes = $stmt->fetchColumn(); // Fetch the single value

        // Check if the new note already exists
        if ($existingNotes === false) {
            // No existing notes for today, create a new entry
            $updatedNotes = $newNoteWithTimestamp;
        } else {
            // Append new note to existing notes
            if (strpos($existingNotes, $newNoteWithTimestamp) === false) {
                $updatedNotes = $existingNotes . ',' . $newNoteWithTimestamp;
            } else {
                // Note already exists
                echo json_encode(['success' => false, 'error' => 'Note already exists']);
                exit;
            }
        }

        // Prepare and execute the statement to update notes
        $stmt = $conn->prepare("UPDATE timeclock SET notes = :updatedNotes WHERE employee_id = :userId AND day = CURDATE()");
        $stmt->bindValue(':updatedNotes', $updatedNotes, PDO::PARAM_STR);
        $stmt->bindValue(':userId', $userIdDb, PDO::PARAM_INT); // Correct variable usage

        if ($stmt->execute()) {
            // Update successful
            header('Location: clock_system.php?success=true');
          
        
        } else {
            // Update failed
            echo json_encode(['success' => false, 'error' => 'Failed to save notes. Please try again.']);
        }
    } catch (PDOException $e) {
        // Handle any database-related errors
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    // Invalid request
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}

$conn = null; // Close the connection
?>
