<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$fullname = isset($_SESSION['login_session']) ? $_SESSION['login_session'] : '';
$userIdDb = isset($_SESSION['userid']) ? $_SESSION['userid'] : '';
$isAdmin = isset($_SESSION['isadmin']) ? $_SESSION['isadmin'] : ''; // Assuming isAdmin is set in the session

if (!isset($_SESSION['login_session']) || !isset($_SESSION['userid'])) {
    header('Location: ../../../index.php');
    exit(); // Make sure to exit after redirection to prevent further code execution
}
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

// Construct SQL query based on admin status
if ($isAdmin) {
    // Admin: Fetch all notes
    $sql = "SELECT notes, employee FROM timeclock ORDER BY day DESC";
} else {
    // Non-admin: Fetch notes for specific user
    $sql = "SELECT notes, employee FROM timeclock WHERE employee_id = '$userIdDb' ORDER BY day DESC";
}

$result = $conn->query($sql);

// Check for query error
if (!$result) {
    die("SQL Error: " . $conn->error);
}

// Prepare array to store notes
$notes = [];

// Fetch and store notes in array
while ($row = $result->fetch_assoc()) {
    $individualNotes = explode(',', $row['notes']);
    foreach ($individualNotes as $noteWithTimestamp) {
        $parts = explode(' ', $noteWithTimestamp, 3); // Changed 2 to 3
        if (count($parts) < 3) {
            continue; // Skip if not properly formatted
        }
        $timestamp = $parts[0] . ' ' . $parts[1];
        $note = $parts[2];

        // Extract status text (assuming it's in brackets like [Status])
        preg_match('/\[(.*?)\]/', $note, $statusMatches);
        $statusText = isset($statusMatches[1]) ? $statusMatches[1] : 'Unknown';
        $noteContent = str_replace($statusMatches[0], '', $note); // Remove status from note content

        // Determine the CSS class for the status text
        $statusClass = getStatusClass($statusText);

        // Calculate "time ago" format based on fetched timestamp
        $timestampUnix = strtotime($timestamp); // Convert MySQL datetime to Unix timestamp
        if ($timestampUnix === false) {
            continue; // Skip invalid timestamps
        }
        $timeAgo = time_ago($timestampUnix); // Convert timestamp to "time ago" format

        // Construct the note entry
        $notes[] = [
            'notes' => '<span class="' . $statusClass . '">[' . $statusText . ']</span> ' . trim($noteContent), // Use status class here
            'employee' => $row['employee'],
            'time_ago' => $timeAgo,
            'timestamp' => $timestamp // Store original timestamp for detailed display
        ];
    }
}

// Function to get the CSS class based on status text
function getStatusClass($statusText) {
    switch (trim($statusText)) {
        case 'Clocked In':
            return 'status-clocked-in';
        case 'On Break':
            return 'status-on-break';
        case 'Clocked Out':
            return 'status-clocked-out';
        default:
            return 'status-unknown';
    }
}

// Function to calculate "time ago" format
function time_ago($timestamp) {
    $current_time = time();
    $time_diff = $current_time - $timestamp;
    if ($time_diff < 60) {
        return $time_diff . " seconds ago";
    } elseif ($time_diff < 3600) {
        return floor($time_diff / 60) . " minutes ago";
    } elseif ($time_diff < 86400) {
        return floor($time_diff / 3600) . " hours ago";
    } else {
        return floor($time_diff / 86400) . " days ago";
    }
}

// Close connection
$conn->close();

// Return notes as JSON
// echo json_encode($notes);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
    <title>Time Clock</title>
    <style>
 /* Notes section styles */
.notes-item {
    border-bottom: 1px solid #e0e0e0;
    padding: 15px;
    background-color: #ffffff;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 10px;
}

.notes-item:last-child {
    border-bottom: none;
}

.notes-item h6 {
    font-size: 16px;
    font-weight: 600;
    color: #007bff; /* Blue color for titles */
    margin: 0;
}

.notes-item small {
    color: #6c757d; /* Light gray color for small text */
}

.notes-item span {
    font-size: 14px;
    display: block;
    margin-top: 5px;
    color: #333; /* Dark text color for notes */
}

.notes-item small.timestamp {
    color: #adb5bd; /* Slightly lighter gray for timestamp */
}

/* Navbar styles */
nav {
    background-color: #ffffff; /* White background for navbar */
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

nav a {
    color: #007bff; /* Blue color for links */
    text-decoration: none;
}

nav a:hover {
    color: #0056b3; /* Darker blue for hover effect */
}

.info-box, .notification {
    margin-left: 1rem;
    font-size: 1.5rem;
}
.status-clocked-in {
    color: green; /* Green color for 'Clocked In' status */
}

.status-on-break {
    color: orange; /* Orange color for 'On Break' status */
}

.status-clocked-out {
    color: red; /* Red color for 'Clocked Out' status */
}

.status-unknown {
    color: gray; /* Gray for unknown status */
}
.sign-out-button {
    background-color: #4099ff;
    background-image: linear-gradient(to bottom, #4099ff, #73b4ff);
    transition: background-color 0.3s ease;
}

.sign-out-button a {
    color: white !important; /* Force the text color to white */
    text-decoration: none; /* Remove underline from the link */
}

.sign-out-button:hover {
    background-color: #4cd2e0;
}

    </style>
</head>
<body>

<section id="sidebar">
    <a class="brand">
        <div class="header">
                <span class="text">
                    Clock/Vacation Management System
                </span>
        </div>
    </a>
    <ul class="side-menu top">
        <li>
            <a class="nav-link" href="dashboard.php">
                <i class='bx bxs-home' style="color: #09b3b3;"></i>
                <span class="text">Dashboard</span>
            </a>
        </li>
        <li>
            <a class="nav-link" href="clock_system.php">
            <i class='bx bxs-shopping-bag-alt' style="color: #FF6347;"></i> 
                    <span class="text">Clock system</span>
                </a>
            </li>
            <li>
            <a class="nav-link" href="timeclock_table.php">
            <i class='bx bxs-doughnut-chart' style="color: #1E90FF;"></i> 
                    <span class="text">Timeclock Table</span>
                </a>
            </li>
            <li>
            <a class="nav-link" href="timeline.php">
            <i class='bx bx-loader-circle' style="color: #32CD32;"></i>
                    <span class="text">Timeline</span>
                </a>
            </li>
            
            <li>
            <a class="nav-link" href="request_leave.php">
            <i class='bx bx-calendar' style="color: #FF8C00;"></i>
                    <span class="text">Request leave</span>
                </a>
            </li>
            <li>
            <a class="nav-link" href="display_offdays.php">
            <i class='bx bx-calendar-x' style="color: #DC143C;"></i>
                    <span class="text"> Requested Off days</span>
                </a>
            </li>
            <li>
            <a class="nav-link"href="leave_table.php">
            <i class='bx bx-calendar-check' style="color: #4682B4;"></i>
                    <span class="text"> Leave Table</span>
                </a>
            </li>
            <?php if (isset($_SESSION['isadmin']) && $_SESSION['isadmin']): ?>
                <li>
            <a class="nav-link"href="add_holidays.php">
            <i class='bx bx-calendar-star' style="color: #FFD700;"></i> 
                    <span class="text"> Holidays</span>
                </a>
            </li>
         
            <li>
                <a class="nav-link" href="settings.php">
                    <i class='bx bx-cog' style="color: #8A2BE2;"></i>
                    <span class="text">Settings</span>
                </a>
            </li>
        <?php endif; ?>
       
       
            <li>
            <a class="nav-link" href="../../../activechannels_all_New.php">
            <i class="fa fa-arrow-circle-left text-primary"></i>
                <span class="text"> Billing System </span>
            </a>
            </li>
        </ul>
        </section>

        <section id="content">
        <!-- NAVBAR -->
        <nav>
    <i class='bx bx-menu'></i>
    <a href="#" class="nav-link">Categories</a>
    
    <form action="#">
        <!-- You can add form elements here if needed -->
    </form>
    
    <a href="infobox.php" class="info-box1" id="infoBoxLink1">
        <i class='bx bxs-info-circle bx-tada' style='font-size: 28px; color: #007bff;'></i>
    </a>
    
    <input type="checkbox" id="switch-mode" hidden>
    <label for="switch-mode" class="switch-mode"></label>
    
    <a href="widget.php" class="notification" id="notificationLink">
        <i class='bx bxs-bell' style='color: red;'></i>
    </a>

    <!-- User Menu Dropdown -->
    <li class="nav-item dropdown user-menu">
        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fa-solid fa-user"></i>
            <span class="employee-name"><?php echo htmlspecialchars($fullname); ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
            <!-- User Image -->
            <li class="user-header text-bg-primary">
                <!-- Optionally, add user image here -->
            </li>
            <li class="user-footer">
    <div class="sign-out-button">
        <a href="../../../logout.php" class="btn btn-default btn-flat">
            <i class="fa fa-sign-out-alt"></i> Sign out
        </a>
    </div>
</li>

        </ul>
    </li>
</nav>

    <!-- MAIN CONTENT -->
    <main>
        <div class="head-title">
            <div class="left">
                <h1>Dashboard</h1>
                <ul class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active" href="widget.php">Notes</a></li>
                </ul>
            </div>
        </div>

        <div class="container-fluid pt-4 px-4">
            <div class="row g-4">
                <div class="col-sm-12 col-xl-6">
                    <div class="bg-light rounded h-100 p-4">
                        <h6 class="mb-4">Notes</h6>
              


                <?php foreach ($notes as $note) { ?>
                            <div class="d-flex align-items-center notes-item">
                                <div class="w-100">
                                    <div class="d-flex justify-content-between">
                                    <h6 class="mb-0">
                                    <i class="fas fa-user"></i> <!-- User icon -->
                                    <?php echo htmlspecialchars($note['employee']); ?> <!-- Employee name -->
                                </h6>
                                
                                        <small><?php echo htmlspecialchars($note['time_ago']); ?></small>
                                    </div>
                                    <span class="note"><?= $note['notes']; ?></span>
                                    <small class="timestamp d-block"><?php echo htmlspecialchars($note['timestamp']); ?></small>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</section>

  <!-- jQuery (full version) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Full jQuery version -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
   


</body>
</html>
