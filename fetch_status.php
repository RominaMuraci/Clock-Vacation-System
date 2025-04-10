<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['login_session']) || !isset($_SESSION['userid'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$rootDir = __DIR__ . '../../../../'; // Adjust this path as needed

// Include the PDO connection script
$conn = include($rootDir . 'config/connection.php'); // Ensure this returns a PDO instance

if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$userId = $_SESSION['userid']; // Assuming user ID is stored in session

function getCurrentStatus($conn, $userId) {
    $query = "SELECT clock_in, clock_out, breaks FROM timeclock WHERE employee_id = :userId ORDER BY clock_id DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    
    // Bind the user ID parameter
    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $status = 'No Record';
        $breakStatus = 'No Break';

        // Check clock in and out status
        if (is_null($row['clock_out']) && !is_null($row['clock_in'])) {
            $status = 'Clocked In';
        } elseif (!is_null($row['clock_out'])) {
            $status = 'Clocked Out';
        }

        // Check breaks status
        if (!empty($row['breaks'])) {
            $breaks = json_decode($row['breaks'], true); // Assuming breaks is a JSON string

            foreach ($breaks as $break) {
                if (is_null($break['break_end']) && !is_null($break['break_start'])) {
                    $breakStatus = 'On Break';
                    break; // If on break, no need to check further
                } elseif (!is_null($break['break_end'])) {
                    $breakStatus = 'Break Ended';
                }
            }

            // Combine status if on break
            if ($breakStatus == 'On Break' && $status == 'Clocked In') {
                return 'Clocked In and On Break';
            } elseif ($breakStatus == 'Break Ended' && $status == 'Clocked In') {
                return 'Clocked In and Break Ended';
            }
        }

        return $status;
    }

    return 'No Record';
}

$status = getCurrentStatus($conn, $userId);

echo json_encode(['status' => $status]);

$conn = null; // Close the PDO connection
?> 