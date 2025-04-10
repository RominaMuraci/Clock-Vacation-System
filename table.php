<?php
session_start();
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
if (!isset($_SESSION['login_session']) || !isset($_SESSION['userid'])) {
    header('Location: ../../../index.php');
    exit(); // Make sure to exit after redirection to prevent further code execution
}

// Retrieve the user's full name and ID from the session
$userIdDb = isset($_SESSION['userid']) ? $_SESSION['userid'] : '';
$isAdmin = isset($_SESSION['isadmin']) ? $_SESSION['isadmin'] : '';
$fullname = isset($_SESSION['login_session']) ? $_SESSION['login_session'] : '';

// // Debug: Print the value and type of $isAdmin
// echo "<pre>Value of \$isAdmin: "; var_dump($isAdmin); echo "</pre>";
// echo "<pre>Type of \$isAdmin: " . gettype($isAdmin) . "</pre>";

if (!isset($_SESSION['login_session']) || !isset($_SESSION['userid'])) {
    header('Location: ../../../index.php');
    exit(); // Make sure to exit after redirection to prevent further code execution
}


// Adjust the comparison based on the actual type of $isAdmin
if ($isAdmin === true) {
    // Admin: Fetch all records and sort by day in descending order
    $sql = "SELECT clock_id, day, employee_id, employee, clock_in, clock_out, breaks, tot_hours, break_duration_sum, daily_total, regular_hours, overtime, notes 
            FROM timeclock 
            ORDER BY clock_in DESC";
} else {
    // Non-admin: Fetch records for the specific employee and sort by day in descending order
    $sql = "SELECT clock_id, day, employee_id, employee, clock_in, clock_out, breaks, tot_hours, break_duration_sum, daily_total, regular_hours, overtime, notes 
            FROM timeclock 
            WHERE employee_id = $userIdDb 
            ORDER BY clock_in DESC";
}


// Debug: Print the SQL query
// echo "<pre>Executing SQL query: $sql</pre>";

// Execute the SQL query
$result = mysqli_query($conn, $sql);

if (!$result) {
    // Handle query execution error
    echo json_encode(['error' => 'Query execution failed: ' . mysqli_error($conn)]);
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Boxicons -->
    <!-- <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>


 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
   
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>





<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css"> -->
<link rel="icon" href="uploads/favicon.png" type="image/jpeg">
   <!-- Boxicons -->
   <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>

<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">


<!-- Font Awesome CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">




    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
    <title>Time Clock</title>
    <style>
       /* Table Container */
.table-container {
    margin: 20px 0;
}

/* Table */
.table {
    width: 100%;
    border-collapse: collapse;
    background-color: #f9f9f9;
}

/* Table Header */
.table th, .table td {
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

/* Header Styling */
/* .table thead th {
    background-color: #007bff; 
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    position: relative; 
} */

/* Header Hover Effect */
/* .table thead th:hover {
    background-color:  #007bff; /* Example: Darker shade on hover */
/* } */ 

/* Row Hover Effect */
.table tbody tr:hover {
    background-color: #f1f1f1;
}

/* Input Styling */
input[type="text"] {
    width: 100%;
    box-sizing: border-box;
    padding: 2px 5px;
    font-size: 12px;
}

/* Button Styling */
.btn {
    padding: 2px 5px;
    font-size: 12px;
}

/* Inner Table */
.inner-table th, .inner-table td {
    padding: 3px !important;
}

/* Custom div inside th */
th div {
    max-width: 150px;
    margin: 0 auto;
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



/* Icon styles */
/* Icon styles */
.status-circle {
    display: inline-block;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    vertical-align: middle;
    margin-right: 8px; /* Spacing between icon and text */
    color: transparent; /* Ensure icon color is transparent */
}

/* Red circle for clocked out */
.status-circle.clocked-out {
    color: #d40808; /* Red color for the circle */
}

/* Green circle for clocked in */
.status-circle.clocked-in {
    color: #0ba10b; /* Green color for the circle */
}

/* Ensure any default border or additional styles are removed */
.bx.bxs-circle {
    border: none; /* Remove any border if it exists */
    box-shadow: none; /* Remove any shadow if it exists */
}

/* Red Circular Button */
.button-red-circle {
        appearance: none;
        background-color: #ff0000;
        border-radius: 40em;
        border-style: none;
        box-shadow: #cc0000 0 -12px 6px inset;
        box-sizing: border-box;
        color: #ffffff;
        cursor: pointer;
        display: inline-block;
        font-family: -apple-system, sans-serif;
        font-size: 1.1rem;
        font-weight: 700;
        letter-spacing: -.24px;
        margin: 0;
        outline: none;
        padding: 0.5rem 1rem;
        text-align: center;
        text-decoration: none;
        transition: all .15s;
        user-select: none;
        -webkit-user-select: none;
        touch-action: manipulation;
    }

    .button-red-circle:hover {
        background-color: #ff4d4d;
        box-shadow: #cc0000 0 -6px 8px inset;
        transform: scale(1.125);
    }
    .button-red-circle:active {
        transform: scale(1.025);
    }

    /* Blue Rectangular Button */
    .button-blue-rect {
        appearance: none;
        background-color: #58b0e5;
        border-radius: 0.5rem;
        border-style: none;
        box-shadow: #074977 0 -6px 6px inset;
        box-sizing: border-box;
        color: #ffffff;
        cursor: pointer;
        display: inline-block;
        font-family: -apple-system, sans-serif;
        font-size: 1rem;
        font-weight: 700;
        margin: 0;
        outline: none;
        padding: 0.5rem 1rem 1rem 1rem;
        text-align: center;
        text-decoration: none;
        transition: all .15s;
        user-select: none;
        -webkit-user-select: none;
        touch-action: manipulation;
    }

    .button-blue-rect:hover {
        background-color: #5fd7f1;
        box-shadow: #08657a 0 -6px 6px inset;
        transform: scale(1.125);
    }

    .button-blue-rect:active {
        transform: scale(1.025);
    }

    /* Red Rectangular Button */
    .button-red-rect {
        appearance: none;
        background-color: #ff3131;
        border-radius: 0.5rem;
        border-style: none;
        box-shadow: #a20505 0 -6px 6px inset;
        box-sizing: border-box;
        color: #ffffff;
        cursor: pointer;
        display: inline-block;
        font-family: -apple-system, sans-serif;
        font-size: 1rem;
        font-weight: 700;
        margin: 0;
        outline: none;
        padding: 0.5rem 1rem 1rem 1rem;
        text-align: center;
        text-decoration: none;
        transition: all .15s;
        user-select: none;
        -webkit-user-select: none;
        touch-action: manipulation;
    }

    .button-red-rect:hover {
        background-color: #fc5151;
        box-shadow: #d00505 0 -6px 6px inset;
        transform: scale(1.125);
    }

    .button-red-rect:active {
        transform: scale(1.025);
    }

    /* Green Rectangular Button */
    .button-green-rect {
        appearance: none;
        background-color: #70e070;
        border-radius: 0.5rem;
        border-style: none;
        box-shadow: #089408 0 -6px 6px inset;
        box-sizing: border-box;
        color: #ffffff;
        cursor: pointer;
        display: inline-block;
        font-family: -apple-system, sans-serif;
        font-size: 1rem;
        font-weight: 700;
        margin: 0;
        outline: none;
        padding: 0.5rem 1rem 1rem 1rem;
        text-align: center;
        text-decoration: none;
        transition: all .15s;
        user-select: none;
        -webkit-user-select: none;
        touch-action: manipulation;
    }

    .button-green-rect:hover {
        background-color: #8ef68e;
        box-shadow: #067006 0 -6px 6px inset;
        transform: scale(1.125);
    }

    .button-green-rect:active {
        transform: scale(1.025);
    }
    .button-blue-rect, .button-green-rect, .button-red-rect {
       padding: 5px 10px; /* Reduce padding */
       font-size: 12px; /* Reduce font size */
       border-radius: 0.3rem; /* Adjust border radius for smaller buttons */
   }

   .button-blue-rect i, .button-green-rect i, .button-red-rect i {
       font-size: 14px; /* Smaller icon size */
   }

    </style>
</head>
<body>
<section id="sidebar">
<a class="brand">
            <i class='bx bxs-smile'></i>
            <span class="text">Time clock</span>
        </a>
        <ul class="side-menu top">
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
            <li>
            <a class="nav-link"href="add_holidays.php">
            <i class='bx bx-calendar-star' style="color: #FFD700;"></i> 
                    <span class="text"> Holidays</span>
                </a>
            </li>
            <?php if (isset($_SESSION['isadmin']) && $_SESSION['isadmin']): ?> 
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

    <!-- CONTENT -->
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


        <!-- MAIN -->
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Timeclock table</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="#">Home</a>
                        </li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li>
                            <a class="active" href="#">Timeclock table</a>
                        </li>
                    </ul>
                </div>
            </div>


            <label for="taskFilter"></label>
<select id="taskFilter" class="form-select">
    <option value="active" selected>Show Clocked In</option>
    <option value="inactive">Show Clocked Out</option>
    <option value="all">Show All</option>
</select>
            <div class="table-container">
    <div class="table-responsive">
        <table class="table table-bordered table-hover" id="timeClockTable">
        <thead>
            <tr>
                <!-- <th scope="col" onclick="sortTable(0)" class="sortable">
                    #<span class="sort-icon"></span>
                </th> -->
                <th scope="col">Employee ID</th>
                <th scope="col">Day</th>
                
                <th scope="col">Employee</th>
                <th scope="col">Clock In</th>
                <th scope="col">Clock Out</th>
                <th scope="col">Breaks</th>
                 <th scope="col">Total Hours/h</th>
                 <th scope="col">Break Duration</th>
                 <th scope="col"> Daily Total/h</th>
                 <th scope="col">Regular Hours/h</th>
                 <th scope="col">Overtime/h</th>
                 <th scope="col">Notes</th>
                 <th scope="col">Actions</th>

            </tr>
            <tr>
                <!-- <th><input type="text" id="search0" placeholder="Search #"></th> -->
                <th><input type="text" placeholder="Search Employee ID"></th>
                <th><input type="text" placeholder="Search Day"></th>
                
                <th><input type="text" placeholder="Search Employee"></th>
                <th><input type="text" placeholder="Search Clock In"></th>
                <th><input type="text" placeholder="Search Clock Out"></th>
                <th><input type="text"  placeholder="Search Breaks"></th>
                <th><input type="text" placeholder="Search Total Hours"></th>
                <th><input type="text" placeholder="Search Break Duration"></th>
                <th><input type="text" placeholder="Search Daily Total"></th>
                <th><input type="text" placeholder="Search Regular Hours"></th>
                <th><input type="text"  placeholder="Search Overtime"></th>
                <th><input type="text"  placeholder="Search Notes"></th>
                <th><input type="text"  placeholder="Search Actions"></th>
            </tr>
        </thead>
        <tbody>
    <?php
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Determine icon class based on clock-in and clock-out status
            $statusClass = '';

            if (!empty($row["clock_out"])) {
                $statusClass = 'clocked-out'; // Red circle
            } elseif (!empty($row["clock_in"])) {
                $statusClass = 'clocked-in'; // Green circle
            }

            // Output main table row with data
            echo "<tr>";
            // <i class='bx bxs-circle bx-flip-vertical bx-flashing'  ></i>
            // Display employee ID with appropriate status circle
            echo "<td><i class='bx bxs-circle bx-flip-vertical bx-flashing status-circle $statusClass'></i> " . htmlspecialchars($row["employee_id"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["day"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["employee"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["clock_in"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["clock_out"]) . "</td>";
            echo "<td><button class='btn btn-primary view-breaks-btn' data-toggle='modal' data-target='#breaksModal" . htmlspecialchars($row["clock_id"]) . "' title='Press View breaks to see all breaks of the employee during a day'>View Breaks</button></td>";
            echo "<td>" . htmlspecialchars($row["tot_hours"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["break_duration_sum"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["daily_total"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["regular_hours"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["overtime"]) . "</td>";
            echo "<td><button class='btn btn-primary view-note-btn' data-clock_id='" . htmlspecialchars($row["clock_id"]) . "' data-toggle='modal' data-target='#notesModal" . htmlspecialchars($row["clock_id"]) . "' title='Press View notes to see all notes of the employee during a day'>View Notes</button></td>";
            echo "<td>" .
            // "<button class='button-blue-rect view-task-btn' data-id='" . htmlspecialchars($row["clock_id"]) . "' title='View'>" .
            // "<i class='fas fa-eye'></i>" .
            // "</button> " .
            "<button class='button-green-rect edit-task-btn' data-id='" . htmlspecialchars($row["clock_id"]) . "'
                data-day='" . htmlspecialchars($row["day"]) . "'
                data-employee='" . htmlspecialchars($row["employee"]) . "'
                data-clock-in='" . htmlspecialchars($row["clock_in"]) . "'
                data-clock-out='" . htmlspecialchars($row["clock_out"]) . "'
                data-tot-hours='" . htmlspecialchars($row["tot_hours"]) . "'
                data-break-duration='" . htmlspecialchars($row["break_duration_sum"]) . "'
                data-daily-total='" . htmlspecialchars($row["daily_total"]) . "'
                data-regular-hours='" . htmlspecialchars($row["regular_hours"]) . "'
                data-overtime='" . htmlspecialchars($row["overtime"]) . "'
                title='Edit'>" .
            "<i class='fas fa-edit'></i>" .
            "</button> " .
           "<button class='button-red-rect delete-btn' data-id='" . htmlspecialchars($row["clock_id"]) . "' data-employee='" . htmlspecialchars($row["employee"]) . "' title='Delete'>" .
     "<i class='fas fa-trash-alt'></i></button>".
            "</td>";
       
       
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='13'>No records found</td></tr>";
    }
    ?>
</tbody>






</table>
</div>
</div>
<?php
// Reset result pointer to start
mysqli_data_seek($result, 0);

// Modal for viewing breaks
while ($row = mysqli_fetch_assoc($result)) {
    $breaks = json_decode($row["breaks"], true);
    $breaksContent = '';

    if (is_array($breaks) && !empty($breaks)) {
        $breaksContent .= "<table class='table table-bordered'>";
        $breaksContent .= "<tr><th>Break Start</th><th>Break End</th><th>Break Duration</th></tr>";
        foreach ($breaks as $break) {
            $breaksContent .= "<tr>";
            $breaksContent .= "<td>" . htmlspecialchars($break["break_start"]) . "</td>";
            $breaksContent .= "<td>" . htmlspecialchars($break["break_end"]) . "</td>";
            $breaksContent .= "<td>" . htmlspecialchars($break["break_duration"]) . "</td>";
            $breaksContent .= "</tr>";
        }
        $breaksContent .= "</table>";
    } else {
        $breaksContent = "No breaks recorded";
    }

    echo "<div class='modal fade' id='breaksModal" . htmlspecialchars($row["clock_id"]) . "' tabindex='-1' role='dialog' aria-labelledby='breaksModalLabel" . htmlspecialchars($row["clock_id"]) . "' aria-hidden='true'>";
    echo "    <div class='modal-dialog' role='document'>";
    echo "        <div class='modal-content'>";
    echo "            <div class='modal-header'>";
    echo "                <h5 class='modal-title' id='breaksModalLabel" . htmlspecialchars($row["clock_id"]) . "'>Breaks Details</h5>";
    echo "                <button type='button' class='close' data-dismiss='modal' aria-label='Close'>";
    echo "                    <span aria-hidden='true'>&times;</span>";
    echo "                </button>";
    echo "            </div>";
    echo "            <div class='modal-body'>";
    echo "                " . $breaksContent;
    echo "            </div>";
    echo "            <div class='modal-footer'>";
    echo "                <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>";
    echo "            </div>";
    echo "        </div>";
    echo "    </div>";
    echo "</div>";
}
?>


<?php
// Reset result pointer to start
mysqli_data_seek($result, 0);

// Modals for viewing breaks
while ($row = mysqli_fetch_assoc($result)) {
    echo "<div class='modal fade' id='notesModal" . htmlspecialchars($row["clock_id"]) . "' tabindex='-1' role='dialog' aria-labelledby='notesModalLabel" . htmlspecialchars($row["clock_id"]) . "' aria-hidden='true'>";
    echo "    <div class='modal-dialog' role='document'>";
    echo "        <div class='modal-content'>";
    echo "            <div class='modal-header'>";
    echo "                <h5 class='modal-title' id='notesModalLabel" . htmlspecialchars($row["clock_id"]) . "'>Note Details</h5>";
    echo "                <button type='button' class='close' data-dismiss='modal' aria-label='Close'>";
    echo "                    <span aria-hidden='true'>&times;</span>";
    echo "                </button>";
    echo "            </div>";
    echo "            <div class='modal-body'>";
    echo "                <table class='table'>";
    echo "                    <thead>";
    echo "                        <tr>";
    echo "                            <th>Timestamp</th>";

    echo "                            <th>Status</th>";
    echo "                            <th>Note</th>";
    echo "                        </tr>";
    echo "                    </thead>";
    echo "                    <tbody id='notesModalBody" . htmlspecialchars($row["clock_id"]) . "'>";
    echo "                        <!-- Notes content will be populated here -->";
    echo "                    </tbody>";
    echo "                </table>";
    echo "            </div>";
    echo "            <div class='modal-footer'>";
    echo "                <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>";
    echo "            </div>";
    echo "        </div>";
    echo "    </div>";
    echo "</div>";
}

// Reset result pointer to start again for breaks modal
mysqli_data_seek($result, 0);

// Modals for viewing breaks
while ($row = mysqli_fetch_assoc($result)) {
    echo "<div class='modal fade' id='breaksModal" . htmlspecialchars($row["clock_id"]) . "' tabindex='-1' role='dialog' aria-labelledby='breaksModalLabel" . htmlspecialchars($row["clock_id"]) . "' aria-hidden='true'>";
    echo "    <div class='modal-dialog' role='document'>";
    echo "        <div class='modal-content'>";
    echo "            <div class='modal-header'>";
    echo "                <h5 class='modal-title' id='breaksModalLabel" . htmlspecialchars($row["clock_id"]) . "'>Breaks Details</h5>";
    echo "                <button type='button' class='close' data-dismiss='modal' aria-label='Close'>";
    echo "                    <span aria-hidden='true'>&times;</span>";
    echo "                </button>";
    echo "            </div>";
    echo "            <div class='modal-body'>";
    echo "                <table class='table'>";
    echo "                    <thead>";
    echo "                        <tr>";
    echo "                            <th>Break Start</th>";
    echo "                            <th>Break End</th>";
    echo "                            <th>Break Duration</th>";
    echo "                        </tr>";
    echo "                    </thead>";
    echo "                    <tbody id='breaksModalBody" . htmlspecialchars($row["clock_id"]) . "'>";
    echo "                        <!-- Breaks content will be populated here -->";
    echo "                    </tbody>";
    echo "                </table>";
    echo "            </div>";
    echo "            <div class='modal-footer'>";
    echo "                <button type='button' class='btn btn-secondary' data-dismiss='modal'>Close</button>";
    echo "            </div>";
    echo "        </div>";
    echo "    </div>";
    echo "</div>";
}
?>

             

        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script> -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


<script src="script.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTables
    var table = $('#timeClockTable').DataTable({
        "paging": true,
        "lengthMenu": [10, 25, 50, 100],
        "pageLength": 10,
        "ordering": true,
        "info": true,
        "searching": true
    });

    // Define the column indexes for numeric values (Update based on actual table structure)
    var numericColumns = [6, 7, 8, 9, 10]; // Numeric columns for total hours, break duration, etc.
    var statusColumnIndex = 0; // The index where the status icon and Employee ID are located

    // Custom filter for both numeric comparisons and clock-in/clock-out status
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var selectedItem = $('#taskFilter').val(); // Get the selected value from the dropdown
        var statusHtml = table.row(dataIndex).nodes().to$().find('td').eq(statusColumnIndex).html(); // Get the status icon cell

        // Status filtering logic
        var currentStatus = '';
        if (statusHtml.includes('clocked-out')) {
            currentStatus = 'inactive'; // Mark as inactive if clocked-out class is found
        } else if (statusHtml.includes('clocked-in')) {
            currentStatus = 'active';  // Mark as active if clocked-in class is found
        }

        if (selectedItem !== "all") {
            if (selectedItem === "active" && currentStatus !== "active") return false;
            if (selectedItem === "inactive" && currentStatus !== "inactive") return false;
        }

        // Numeric filtering logic for columns
        var valid = true;
        $('#timeClockTable thead input').each(function(index) {
            let inputVal = $(this).val().trim(); // Get the search input value
            let cellValue = data[index]; // Get the value from the cell as text
            let numericCellValue = parseFloat(cellValue) || 0; // Parse the value as a number for numeric columns

            if (numericColumns.includes(index) && inputVal) {
                // Handle numeric comparison for numeric columns
                if (inputVal.startsWith('<')) {
                    let threshold = parseFloat(inputVal.replace('<', '').trim());
                    if (numericCellValue >= threshold) valid = false;
                } else if (inputVal.startsWith('>')) {
                    let threshold = parseFloat(inputVal.replace('>', '').trim());
                    if (numericCellValue <= threshold) valid = false;
                } else if (inputVal.startsWith('=')) {
                    let threshold = parseFloat(inputVal.replace('=', '').trim());
                    if (numericCellValue !== threshold) valid = false;
                } else if (!isNaN(parseFloat(inputVal))) {
                    let threshold = parseFloat(inputVal);
                    if (numericCellValue !== threshold) valid = false;
                }
            } else if (inputVal && !numericColumns.includes(index)) {
                // Handle standard text search for non-numeric columns
                if (!cellValue.toLowerCase().includes(inputVal.toLowerCase())) {
                    valid = false; // Mark row as invalid if text doesn't match
                }
            }
        });

        return valid; // Return true if the row matches all conditions
    });

    // Trigger filtering when the user inputs a value or changes dropdown selection
    $('#timeClockTable thead input').on('input', function() {
        table.draw();  // Redraw the table to apply the new filter
    });

    $("#taskFilter").change(function() {
        table.draw();  // Redraw the table to apply the new filter
    });

    // Prevent sorting when searching in the input boxes
    $('#timeClockTable thead input').on('focus', function() {
        table.order([]).draw();  // Disable sorting on focus
    });

    $('#timeClockTable thead input').on('keydown', function(e) {
        e.stopPropagation(); // Prevent event from bubbling up to the header
    });

    $('#timeClockTable thead input').on('click', function(e) {
        e.stopPropagation(); // Prevent event from bubbling up to the header
        table.order([]); // Reset order
    });

      // Set default filter to show all when the page loads
      $('#taskFilter').val('all').change(); // Trigger change to apply default filtering


    // Initial draw of the table
    table.draw();
});
</script>





<script>
    // Event delegation to handle dynamically created buttons
    $(document).on("click", ".view-note-btn", function() {
        const clock_id = $(this).data("clock_id");

        // Debug: Log clock_id being sent
        console.log("Sending clock_id: ", clock_id);

        $.ajax({
            url: 'fetch_notes.php',
            type: 'POST',
            data: { clock_id: clock_id },
            success: function(data) {
                // Debug: Log received data
                console.log("Received data: ", data);
                $("#notesModalBody" + clock_id).html(data);
            },
            error: function(xhr, status, error) {
                // Debug: Log error details
                console.error("AJAX error: ", status, error);
                $("#notesModalBody" + clock_id).html("<tr><td colspan='2'>Failed to fetch notes</td></tr>");
            }
        });
    });
</script>
<!-- Delete Confirmation Modal -->
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmationLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteConfirmationLabel">Confirm Deletion</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Are you sure you want to delete this record for employee <span id="employeeNameToDelete"></span>?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
      </div>
    </div>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    let clockIdToDelete = null;
    let employeeNameToDelete = null;  // Variable to hold the employee name

    // Event listener for delete buttons
    document.querySelectorAll('.delete-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            clockIdToDelete = this.getAttribute('data-id');
            employeeNameToDelete = this.getAttribute('data-employee');  // Get employee name from data attribute

            // Inject the employee name into the modal
            document.getElementById('employeeNameToDelete').textContent = employeeNameToDelete;

            // Show the delete confirmation modal
            $('#deleteConfirmationModal').modal('show');
        });
    });

    // Event listener for the confirmation button inside the modal
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (clockIdToDelete !== null) {
            // Call the deleteRecord function to handle AJAX deletion
            deleteRecord(clockIdToDelete);
        }
    });

    function deleteRecord(clockId) {
        // Create an AJAX request to the server
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "delete_record.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    alert("Record deleted successfully!");

                    // Optionally, you can remove the row from the table without reloading the page
                    var row = document.querySelector('button[data-id="' + clockId + '"]').closest('tr');
                    row.parentNode.removeChild(row);

                    // Hide the modal after deletion
                    $('#deleteConfirmationModal').modal('hide');
                } else {
                    alert("Error deleting the record.");
                }
            }
        };

        // Send the request with the clock_id
        xhr.send("clock_id=" + clockId);
    }
});

</script>

<!-- Edit Record Modal -->
<div class="modal fade" id="editRecordModal" tabindex="-1" role="dialog" aria-labelledby="editRecordLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editRecordLabel">Edit Employee Record</h5>
        <!-- Close button -->
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="editRecordForm">
          <input type="hidden" id="editClockId" name="clock_id" value="">

          <div class="form-group">
            <label for="editDay">Day</label>
            <input type="date" class="form-control" id="editDay" name="day" required>
          </div>

          <div class="form-group">
            <label for="editEmployeeName">Employee Name</label>
            <input type="text" class="form-control" id="editEmployeeName" name="employee" required disabled>
          </div>

          <div class="form-group">
            <label for="editClockIn">Clock In</label>
            <input type="time" class="form-control" id="editClockIn" name="clock_in" step="1" required>
          </div>

          <div class="form-group">
            <label for="editClockOut">Clock Out</label>
            <input type="time" class="form-control" id="editClockOut" name="clock_out" step="1" required>
          </div>

          <div class="form-group">
            <label for="editTotalHours">Total Hours</label>
            <input type="number" class="form-control" id="editTotalHours" name="tot_hours" required readonly>
          </div>

          <div class="form-group">
            <label for="editBreakDuration">Break Duration</label>
            <input type="time" class="form-control" id="editBreakDuration" name="break_duration_sum" step="1" required>
          </div>

          <div class="form-group">
            <label for="editDailyTotal">Daily Total</label>
            <input type="number" class="form-control" id="editDailyTotal" name="daily_total" required readonly>
          </div>

          <div class="form-group">
            <label for="editRegularHours">Regular Hours</label>
            <input type="number" class="form-control" id="editRegularHours" name="regular_hours" value="8.00" required readonly>
          </div>

          <div class="form-group">
            <label for="editOvertime">Overtime</label>
            <input type="number" class="form-control" id="editOvertime" name="overtime" required readonly>
          </div>

          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variable to hold the clock ID to edit
    let clockIdToEdit = null;

    // Event listener for edit buttons
    document.querySelectorAll('.edit-task-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            clockIdToEdit = this.getAttribute('data-id');

            // Populate modal fields with current values
            document.getElementById('editClockId').value = clockIdToEdit;
            document.getElementById('editDay').value = this.getAttribute('data-day');
            document.getElementById('editEmployeeName').value = this.getAttribute('data-employee');
            document.getElementById('editClockIn').value = this.getAttribute('data-clock-in');
            document.getElementById('editClockOut').value = this.getAttribute('data-clock-out');
            document.getElementById('editTotalHours').value = this.getAttribute('data-tot-hours');
            document.getElementById('editBreakDuration').value = this.getAttribute('data-break-duration');
            document.getElementById('editDailyTotal').value = this.getAttribute('data-daily-total');
            document.getElementById('editRegularHours').value = 8.00; // Default value
            document.getElementById('editOvertime').value = this.getAttribute('data-overtime');

            // Show the modal
            $('#editRecordModal').modal('show');

            // Remove previous event listeners to avoid duplicate handlers
            removeEventListeners();

            // Set event listeners for calculating totals
            document.getElementById('editClockIn').addEventListener('change', calculateTotals);
            document.getElementById('editClockOut').addEventListener('change', calculateTotals);
            document.getElementById('editBreakDuration').addEventListener('input', calculateTotals);
        });
    });

    // Function to remove previous event listeners
    function removeEventListeners() {
        document.getElementById('editClockIn').removeEventListener('change', calculateTotals);
        document.getElementById('editClockOut').removeEventListener('change', calculateTotals);
        document.getElementById('editBreakDuration').removeEventListener('input', calculateTotals);
    }

    // Event listener for form submission
    document.getElementById('editRecordForm').addEventListener('submit', function(event) {
        event.preventDefault();  // Prevent form from submitting the traditional way

        // Validate clock out time and format (HH:mm:ss validation)
        const clockInTime = document.getElementById('editClockIn').value;
        const clockOutTime = document.getElementById('editClockOut').value;

        // Ensure clock-out is later than clock-in
        if (clockOutTime && new Date(`1970-01-01T${clockOutTime}`) <= new Date(`1970-01-01T${clockInTime}`)) {
            alert("Clock Out time must be later than Clock In time.");
            return;
        }

        const formData = new FormData(this);

        // Log form data entries
        for (var [key, value] of formData.entries()) {
            console.log(key + ': ' + value);
        }

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "edit_record.php", true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    console.log(xhr.responseText); // Log response
                    alert("Record updated successfully!");
                    // Optionally reload the page or update the table row dynamically
                    location.reload(); // Reloading the page to reflect changes
                } else {
                    console.error("Error updating the record:", xhr.responseText);
                    alert("Error updating the record: " + xhr.responseText);
                }
            }
        };

        xhr.send(formData);  // Send form data directly
    });

    // Function to calculate totals
    function calculateTotals() {
        const clockIn = document.getElementById('editClockIn').value;
        const clockOut = document.getElementById('editClockOut').value;

        // Validate if both clock in and clock out times are filled
        if (!clockIn || !clockOut) {
            console.log("Clock In and Clock Out values must be entered.");
            return;
        }

        // Convert break duration from HH:mm:ss to decimal hours
        const breakDurationInput = document.getElementById('editBreakDuration').value;
        const breakDuration = timeToHours(breakDurationInput);

        // Create Date objects for calculations (Use only HH:mm:ss format)
        const clockInDate = new Date('1970-01-01T' + clockIn);
        const clockOutDate = new Date('1970-01-01T' + clockOut);

        // Check if clock out is less than or equal to clock in
        if (clockOutDate <= clockInDate) {
            alert("Clock Out time must be later than Clock In time.");
            document.getElementById('editTotalHours').value = 0; // Reset total hours if invalid
            document.getElementById('editDailyTotal').value = 0; // Reset daily total if invalid
            return;  // Stop execution
        }

        // Calculate total hours
        const totalHours = (clockOutDate - clockInDate) / 3600000; // Convert milliseconds to hours
        document.getElementById('editTotalHours').value = totalHours.toFixed(2); // Set total hours in the form

        // Calculate daily total
        const dailyTotal = totalHours - breakDuration;
        document.getElementById('editDailyTotal').value = dailyTotal.toFixed(2);

        // Calculate overtime
        const regularHours = 8.00; // Default regular hours
        const overtime = dailyTotal - regularHours;
        document.getElementById('editOvertime').value = overtime > 0 ? overtime.toFixed(2) : 0;  // Overtime is only positive
    }

    // Helper function to convert time in HH:mm:ss to decimal hours
    function timeToHours(time) {
        const [hours, minutes, seconds] = time.split(':').map(Number);
        return hours + (minutes / 60) + (seconds / 3600);
    }
});
</script>








</body>
</html>
