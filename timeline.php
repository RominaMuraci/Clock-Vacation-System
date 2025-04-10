<?php
session_start();
// Enable error reporting for debugging purposes
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
    die("Connection failed: " . $conn->connect_error);
}

// Set today's date
$today = date('Y-m-d');

// SQL query to fetch data based on whether the user is an admin
if ($isAdmin == '1') {
    // Admins can see all records
    $sql = "SELECT day, employee, clock_in, clock_out FROM timeclock ORDER BY day DESC";
} else {
    // Non-admins can only see their own records
    $sql = "SELECT day, employee, clock_in, clock_out 
            FROM timeclock 
            WHERE employee_id = '$userIdDb' 
            ORDER BY day DESC";
}

$result = mysqli_query($conn, $sql);

if (!$result) {
    // Handle query execution error
    echo json_encode(['error' => 'Query execution failed: ' . mysqli_error($conn)]);
    exit;
}

// Count the number of distinct employees who have clocked in today (Admins can see all, others just themselves)
if ($isAdmin == '1') {
    $sql_today = "SELECT DISTINCT employee_id, employee FROM timeclock WHERE DATE(day) = '$today'";
} else {
    $sql_today = "SELECT DISTINCT employee_id, employee 
                  FROM timeclock 
                  WHERE DATE(day) = '$today' AND employee_id = '$userIdDb'";
}

$result_today = mysqli_query($conn, $sql_today);
$employeeCount = mysqli_num_rows($result_today);

if ($employeeCount > 0) {
    $employees = [];
    while ($row_today = mysqli_fetch_assoc($result_today)) {
        $employees[] = $row_today;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="uploads/favicon.png" type="image/jpeg">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:ital,wght@0,300;0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.1.0/styles/overlayscrollbars.min.css" integrity="sha256-LWLZPJ7X1jJLI5OG5695qDemW1qQ7lNdbTfQ64ylbUY=" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.3.0/css/all.min.css" integrity="sha256-/4UQcSmErDzPCMAiuOiWPVVsNN2s3ZY/NsmXNcj0IFc=" crossorigin="anonymous">
    <link rel="stylesheet" href="css/adminlte.css">
    <link rel="stylesheet" href="style.css">
    <title>Time Clock</title>
    <style>
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
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Timeline </h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Home</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="timeline.php">Timeline </a></li>
                    </ul>
                </div>
            </div>
            
            <!--begin::App Content-->
            <div class="app-content">
                <!--begin::Container-->
                <div class="container-fluid">
                    <!-- Timelime example  -->
                    <div class="row">
                        <div class="col-md-12">
                            <!-- The time line -->
                            <div class="timeline">
                                <!-- Loop through the fetched data and display it in the timeline -->
                                <div class="timeline-item">
                                    <h3 class="timeline-header no-border">
                                        <?php echo $employeeCount . ($employeeCount == 1 ? ' employee has' : ' employees have') . ' clocked in today'; ?>
                                    </h3>
                                </div>

      

                               
                               <?php
 

                              $current_date = '';
$first_iteration = true;

// Check if there are rows in the result set
if (mysqli_num_rows($result) > 0) {
    $employeeCount = 0; // Initialize employee count for each date
    $currentDateCount = 0; // Initialize count for current date

    // Iterate over the result set
    while ($row = mysqli_fetch_assoc($result)) {
        // Format the date and time
        $day = date('d M. Y', strtotime($row['day']));
        $clock_in = date('H:i', strtotime($row['clock_in']));
        $clock_out = date('H:i', strtotime($row['clock_out']));

        // Check if the current date is different from the previous one
        if ($current_date != $day) {
            // Close the previous timeline div if not the first iteration
            if (!$first_iteration) {
                // Display the header with the count of clocked-in employees
                echo "<div class='timeline-header no-border'>";
                echo $employeeCount . ($employeeCount == 1 ? ' employee has' : ' employees have') . ' clocked in today';
                echo '</div>';
                echo '</div>'; // Close previous timeline
            }

            // Update current date and start a new timeline
            $current_date = $day;
            $employeeCount = 0; // Reset count for new date
            echo "<div class='timeline'>";
            echo "<div class='time-label'><span class='text-bg-danger'>$current_date</span></div>";
        }

        // Increment employee count for the current date
        $employeeCount++;

        // Print the clock-in entry
        echo "
        <div>
            <i class='timeline-icon fa-solid fa-user text-bg-success'></i>
            <div class='timeline-item'>
                <span class='time'><i class='fa-solid fa-clock'></i> $clock_in</span>
                <h3 class='timeline-header no-border'><a href='#'>{$row['employee']}</a> clocked in</h3>
            </div>
        </div>";

        // Print the clock-out entry if it exists
        if (!empty($row['clock_out'])) {
            echo "
            <div>
                <i class='timeline-icon fa-solid fa-user text-bg-success'></i>
                <div class='timeline-item'>
                    <span class='time'><i class='fa-solid fa-clock'></i> $clock_out</span>
                    <h3 class='timeline-header no-border'><a href='#'>{$row['employee']}</a> clocked out</h3>
                </div>
            </div>";
        }

        // Mark the first iteration as done
        $first_iteration = false;
    }

    // Display the header for the last date
    if (!$first_iteration) {
        echo "<div class='timeline-header no-border'>";
        echo $employeeCount . ($employeeCount == 1 ? ' employee has' : ' employees have') . ' clocked in today';
        echo '</div>';
        echo '</div>'; // Close timeline for the last date
    }
} else {
    echo '<p>No records found.</p>';
}
                                // Close the database connection
                                mysqli_close($conn);
                                ?>
                                <div>
                                    <i class="timeline-icon fa-solid fa-clock text-bg-secondary"></i>
                                </div>
                            </div>
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->
                </div>
                <!--end::Container-->
            </div>
            <!--end::App Content-->
        </main>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.1.0/browser/overlayscrollbars.browser.es6.min.js" integrity="sha256-NRZchBuHZWSXldqrtAOeCZpucH/1n1ToJ3C8mSK95NU=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js" integrity="sha384-zYPOMqeu1DAVkHiLqWBUTcbYfZ8osu1Nd6Z89ify25QV9guujx43ITvfi12/QExE" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.min.js" integrity="sha384-Y4oOpwW3duJdCWv5ly8SCFYWqFDsfob/3GkgExXKV4idmbt98QcxXYs9UoXAB7BZ" crossorigin="anonymous"></script>
    
    <script src="js/adminlte.js"></script>
    <script src="script.js"></script>
    <script>
        const SELECTOR_SIDEBAR_WRAPPER = ".sidebar-wrapper";
        const Default = {
            scrollbarTheme: "os-theme-light",
            scrollbarAutoHide: "leave",
            scrollbarClickScroll: true,
        };

        document.addEventListener("DOMContentLoaded", function() {
            const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
            if (
                sidebarWrapper &&
                typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== "undefined"
            ) {
                OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                    scrollbars: {
                        theme: Default.scrollbarTheme,
                        autoHide: Default.scrollbarAutoHide,
                        clickScroll: Default.scrollbarClickScroll,
                    },
                });
            }
        });
    </script>
</body>
</html>
