<?php
session_start();
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
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$fullname = isset($_SESSION['login_session']) ? $_SESSION['login_session'] : '';
$userIdDb = isset($_SESSION['userid']) ? $_SESSION['userid'] : '';

if (!$fullname || !$userIdDb) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

function convertToDecimalHours($timeString) {
    $parts = explode(':', $timeString . ':00'); // Default seconds to 00 if missing
    $hours = isset($parts[0]) ? (int)$parts[0] : 0;
    $minutes = isset($parts[1]) ? (int)$parts[1] : 0;
    $seconds = isset($parts[2]) ? (int)$parts[2] : 0;
    return $hours + ($minutes / 60) + ($seconds / 3600);
}

function clockIn($conn, $userId, $fullname, $currentTime, $currentDate) {
    error_log("Clock In Attempt: UserID=$userId, Date=$currentDate, Time=$currentTime");

    $checkQuery = "SELECT * FROM timeclock 
                   WHERE employee_id = '$userId' 
                   AND DATE_FORMAT(day, '%Y-%m-%d') = '$currentDate'";

    $result = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($result) > 0) {
        return json_encode(['error' => 'You have already clocked in today.']);
    } else {
        $emptyBreaks = json_encode([]);
        $query = "INSERT INTO timeclock (employee_id, employee, clock_in, day, breaks) 
                  VALUES ('$userId', '$fullname', '$currentTime', '$currentDate', '$emptyBreaks')";

        if (mysqli_query($conn, $query)) {
            return json_encode(['success' => 'You have successfully clocked in.']);
        } else {
            $errorMsg = mysqli_error($conn);
            return json_encode(['error' => 'There was a database error while attempting to clock in.', 'message' => $errorMsg]);
        }
    }
}

function clockOut($conn, $userId, $currentTime) {
    $query = "SELECT clock_in, breaks FROM timeclock 
              WHERE employee_id='$userId' AND clock_out IS NULL";

    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $clockInTime = $row['clock_in'];
        $breaksJSON = $row['breaks'];

        if (!$clockInTime) {
            return json_encode(['error' => 'Unfortunately, the clock-in time could not be found.']);
        }

        $totalHours = calculateTotalHours($clockInTime, $currentTime);
        $totalBreakDuration = calculateTotalBreakDuration($breaksJSON);

        $dailyTotal = $totalHours - ($totalBreakDuration / 3600.0); // Subtract breaks in hours
        $fixedRegularHours = 8.0;  // Regular hours in decimal format
        $overtime = max(0.0, $dailyTotal - $fixedRegularHours);

        $updateQuery = "UPDATE timeclock 
                        SET clock_out='$currentTime', 
                            tot_hours='$totalHours', 
                            daily_total='$dailyTotal', 
                            regular_hours='$fixedRegularHours', 
                            overtime='$overtime',
                            break_duration_sum='$totalBreakDuration'
                        WHERE employee_id='$userId' AND clock_out IS NULL";

        if (mysqli_query($conn, $updateQuery)) {
            return json_encode(['success' => 'You have successfully clocked out for today.']);
        } else {
            return json_encode(['error' => 'There was a database error while attempting to clock out.']);
        }
    } else {
        return json_encode(['error' => 'No clock-in record found for today, or you have already clocked out.']);
    }
}

function startBreak($conn, $userId, $currentTime) {
    $queryCheckBreak = "SELECT breaks FROM timeclock WHERE employee_id='$userId' AND clock_out IS NULL";
    $resultCheckBreak = $conn->query($queryCheckBreak);

    if ($resultCheckBreak && $resultCheckBreak->num_rows > 0) {
        $row = $resultCheckBreak->fetch_assoc();
        $breaksJSON = $row['breaks'];
        $breaksArray = json_decode($breaksJSON, true);

        foreach ($breaksArray as $break) {
            if ($break['break_end'] === null) {
                return json_encode(['error' => 'You already have an ongoing break.']);
            }
        }

        $newBreak = [
            'break_start' => $currentTime,
            'break_end' => null,
            'break_duration' => null
        ];

        $breaksArray[] = $newBreak;
        $breaksJSONUpdated = json_encode($breaksArray);

        $updateQuery = "UPDATE timeclock 
                        SET breaks='$breaksJSONUpdated'
                        WHERE employee_id='$userId' AND clock_out IS NULL";

        if (mysqli_query($conn, $updateQuery)) {
            return json_encode(['success' => 'Your break has started successfully.', 'breaks' => generateBreaksTable($breaksArray)]);
        } else {
            return json_encode(['error' => 'There was a database error while attempting to start your break.']);
        }
    } else {
        return json_encode(['error' => 'No clock-in record found for today, or you have already clocked out.']);
    }
}
function endBreak($conn, $userId, $currentTime) {
    // Fetch breaks for the user where they haven't clocked out yet
    $queryFetchBreaks = "SELECT breaks FROM timeclock WHERE employee_id='$userId' AND clock_out IS NULL";
    $resultFetchBreaks = $conn->query($queryFetchBreaks);

    if ($resultFetchBreaks && $resultFetchBreaks->num_rows > 0) {
        $row = $resultFetchBreaks->fetch_assoc();
        $breaksJSON = $row['breaks'];
        $breaksArray = json_decode($breaksJSON, true);

        // Get the last break
        $lastBreakIndex = count($breaksArray) - 1;
        $lastBreak = $breaksArray[$lastBreakIndex];

        if ($lastBreak['break_end'] !== null) {
            return json_encode(['error' => 'No ongoing break found to end.']);
        }

        // End the last break by setting the end time
        $lastBreak['break_end'] = $currentTime;
        $breakStart = $lastBreak['break_start'];
        $breakEnd = $lastBreak['break_end'];

        // Calculate break duration in seconds
        $breakDurationInSeconds = calculateBreakDuration($breakStart, $breakEnd);

        // Convert the break duration to HH:MM:SS format and store it
        $lastBreak['break_duration'] = secondsToDynamicFormat($breakDurationInSeconds);

        // Update the breaks array with the ended break
        $breaksArray[$lastBreakIndex] = $lastBreak;
        $breaksJSONUpdated = json_encode($breaksArray);

        // Recalculate the total break duration across all breaks
        $totalBreakDurationInSeconds = calculateTotalBreakDuration($breaksJSONUpdated);

        // Convert the total break duration to HH:MM:SS format
        $totalBreakDurationFormatted = secondsToDynamicFormat($totalBreakDurationInSeconds);

        // Update the database with the new breaks data and the updated total break duration
        $updateBreaksQuery = "UPDATE timeclock 
                              SET breaks='$breaksJSONUpdated',
                                  break_duration_sum='$totalBreakDurationFormatted'
                              WHERE employee_id='$userId' AND clock_out IS NULL";

        if (mysqli_query($conn, $updateBreaksQuery)) {
            return json_encode([
                'success' => 'Break ended',
                'break_duration' => secondsToDynamicFormat($breakDurationInSeconds), // Display the individual break duration
                'total_break_duration' => $totalBreakDurationFormatted,              // Display the total break duration
                'breaks' => generateBreaksTable($breaksArray)                       // Update the breaks table
            ]);
        } else {
            return json_encode(['error' => 'Database error']);
        }
    } else {
        return json_encode(['error' => 'Break start time not found or already clocked out']);
    }
}





function generateBreaksTable($breaksArray) {
    $tableHtml = "<table class='inner-table'>";
    $tableHtml .= "<tr><th>Break Start</th><th>Break End</th><th>Break Duration</th></tr>";

    foreach ($breaksArray as $break) {
        $breakStart = isset($break['break_start']) ? $break['break_start'] : '';
        $breakEnd = isset($break['break_end']) ? $break['break_end'] : '';
        $breakDuration = isset($break['break_duration']) ? $break['break_duration'] : '00:00:00';

        $tableHtml .= "<tr>";
        $tableHtml .= "<td>$breakStart</td>";
        $tableHtml .= "<td>$breakEnd</td>";
        $tableHtml .= "<td>$breakDuration</td>";
        $tableHtml .= "</tr>";
    }

    $tableHtml .= "</table>";

    return $tableHtml;
}




function calculateTotalHours($clockIn, $clockOut) {
    $clockInTime = new DateTime($clockIn);
    $clockOutTime = new DateTime($clockOut);
    $interval = $clockInTime->diff($clockOutTime);

    $totalHours = $interval->h + ($interval->i / 60) + ($interval->s / 3600);

    return $totalHours;
}

function calculateTotalBreakDuration($breaksJSONUpdated) {
    // Decode the JSON into an array
    $breaksArray = json_decode($breaksJSONUpdated, true);
    $totalDurationInSeconds = 0;

    // Loop through each break and accumulate the durations
    foreach ($breaksArray as $break) {
        if (isset($break['break_duration'])) {
            // Parse the break duration from HH:MM:SS into seconds
            $durationParts = explode(':', $break['break_duration']);
            $hours = isset($durationParts[0]) ? (int)$durationParts[0] : 0;
            $minutes = isset($durationParts[1]) ? (int)$durationParts[1] : 0;
            $seconds = isset($durationParts[2]) ? (int)$durationParts[2] : 0;

            // Convert everything to seconds
            $durationInSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;
            $totalDurationInSeconds += $durationInSeconds;
        }
    }

    return $totalDurationInSeconds;
}




function calculateBreakDuration($breakStart, $breakEnd) {
    $startTime = new DateTime($breakStart);
    $endTime = new DateTime($breakEnd);
    $interval = $startTime->diff($endTime);

    // Return duration in seconds
    return ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
}

function secondsToDynamicFormat($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $remainingSeconds = $seconds % 60;

    return sprintf("%02d:%02d:%02d", $hours, $minutes, $remainingSeconds);
}


// Handle POST request actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $currentTime = date('H:i:s'); // Current time formatted as HH:MM:SS
    $currentDate = date('Y-m-d'); // Current date formatted as YYYY-MM-DD

    switch ($action) {
        case 'Clock In':
            echo clockIn($conn, $userIdDb, $fullname, $currentTime, $currentDate);
            break;
        case 'Clock Out':
            echo clockOut($conn, $userIdDb, $currentTime);
            break;
        case 'Start Break':
            echo startBreak($conn, $userIdDb, $currentTime);
            break;
        case 'End Break':
            echo endBreak($conn, $userIdDb, $currentTime);
            break;
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
}

$conn->close();
?>
