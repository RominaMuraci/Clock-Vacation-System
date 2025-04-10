<?php
session_start();

// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

$rootDir = __DIR__ . '../../../../';
// Include your email service class
include '../../../Classes/EmailService.php';

// Include database connection
$conn = include($rootDir . 'config/connection.php');

// // Check if the user is logged in by verifying the session variable
if (!isset($_SESSION['login_session']) || !isset($_SESSION['userid'])) {
    header('Location: ../../../index.php');
    exit(); // Make sure to exit after redirection to prevent further code execution
}

// Retrieve the user's full name and ID from the session
$fullname = isset($_SESSION['login_session']) ? $_SESSION['login_session'] : '';
$userIdDb = isset($_SESSION['userid']) ? $_SESSION['userid'] : '';
//
//$employee_id = isset($userIdDb) ? $userIdDb : ''; // Ensure $userIdDb is set
//$current_year = date("Y");
//
//try {
//    // Fetch the latest quota from quota_timeclock for the current year
//    $quota_query = "SELECT quota FROM quota_timeclock WHERE year = :year";
//    $stmt_quota = $conn->prepare($quota_query);
//    $stmt_quota->bindParam(':year', $current_year, PDO::PARAM_INT);
//    $stmt_quota->execute();
//    $quota_data = $stmt_quota->fetch(PDO::FETCH_ASSOC);
//    $stmt_quota->closeCursor();
//
//    if (!$quota_data) {
//        error_log("No quota found for the year: $current_year");
//
//    } else {
//        $quota = $quota_data['quota'];
//    }
//
//    // Fetch leave balance for the current year
//    $balance_query = "SELECT
//                        brought_forward,
//                        quota,
//                        used,
//                        remaining
//                      FROM employee_balances
//                      WHERE employee_id = :employee_id AND year = :year";
//
//    $stmt_balance = $conn->prepare($balance_query);
//    $stmt_balance->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
//    $stmt_balance->bindParam(':year', $current_year, PDO::PARAM_INT);
//    $stmt_balance->execute();
//    $balance = $stmt_balance->fetch(PDO::FETCH_ASSOC);
//
//
//    // Update leave balance with the latest quota
//    $leaveBalance = [
//        'brought_forward' => $balance['brought_forward'],
//        'quota' => $balance['quota'],
//        'used' => $balance['used'],
//        'remaining' => $balance['brought_forward'] + $balance['quota'] - $balance['used']
//    ];
//
//} catch (PDOException $e) {
//    error_log("Database error: " . $e->getMessage());
//    echo "Database error.";
//}
//
//$conn = null;
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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/adminlte.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css">


    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!-- Chosen CSS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:ital,wght@0,300;0,400;0,700;1,400&display=swap" rel="stylesheet">
    <!--end::Fonts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.1.0/styles/overlayscrollbars.min.css" integrity="sha256-LWLZPJ7X1jJLI5OG5695qDemW1qQ7lNdbTfQ64ylbUY=" crossorigin="anonymous">
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Third Party Plugin(Font Awesome)-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.3.0/css/all.min.css" integrity="sha256-/4UQcSmErDzPCMAiuOiWPVVsNN2s3ZY/NsmXNcj0IFc=" crossorigin="anonymous">
    <!--end::Third Party Plugin(Font Awesome)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="css/adminlte.css">


    <title>Time Clock</title>
    <style>

        .required-label::after {
            content: " *";
            color: red;
        }


        /* Color and Background Styles */
        .order-card {
            color: #fff;
        }

        .bg-c-blue {
            background: linear-gradient(45deg, #4099ff, #73b4ff);
        }

        .bg-c-green {
            background: linear-gradient(45deg, #2ed8b6, #59e0c5);
        }

        .bg-c-yellow {
            background: linear-gradient(45deg, #FFB64D, #ffcb80);
        }

        .bg-c-red {
            background: linear-gradient(45deg, #FF5370, #ff869a);
        }

        /* Card Styles */
        .card {
            border-radius: 5px;
            box-shadow: 0 1px 2.94px 0.06px rgba(4, 26, 55, 0.16);
            border: none;
            margin-bottom: 30px;
            transition: all 0.3s ease-in-out;
            display: flex; /* Use flex layout */
            flex-direction: column;
        }

        .card .card-block {
            padding: 25px;
        }

        .order-card i {
            font-size: 26px;
        }

        /* Float Classes */
        .f-left {
            float: left;
        }

        .f-right {
            float: right;
        }

        /* Container Styles */
        .container-fluid {
            max-width: 100%; /* Allow full width on smaller screens */
            width: 100%; /* Ensure the container fills available space */
            margin: 0 auto; /* Center the form */
        }

        /* Form Container Styles */
        .form-container {
            border: 1px solid #ddd; /* Add border to the form */
            border-radius: 10px; /* Round the corners */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            padding: 2%; /* Padding inside the container */
            background-color: #f8f9fa; /* Light background color */
            margin: 2% auto; /* Center the form */
            max-width: 700px; /* Limit the width of the form container */
        }

        /* Label Styles */
        .form-label {
            font-weight: bold; /* Make labels bold */
            margin-bottom: 0.5rem; /* Spacing below labels */
        }

        /* Input and Select Styles */
        .form-control, .form-select {
            height: 38px; /* Smaller input height */
            font-size: 0.875rem; /* Slightly smaller font */
        }

        /* Button Styles */
        .btn {
            padding: 0.375rem 0.75rem; /* Adjust button padding */
            font-size: 0.875rem; /* Slightly smaller font */
        }

        /* Margin Bottom Adjustments */
        .mb-1 {
            margin-bottom: 0.75rem; /* Adjust spacing between elements */
        }

        /* Medical Report Question Container */
        #medicalReportQuestionContainer,
        #medicalReportContainer {
            margin-top: 1rem; /* Space above the medical report question */
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

        .flatpickr-day.weekend {
            background-color: #ffcccc; /* Light red */
            color: #cc0000; /* Dark red text */
        }

        /* Highlight holidays with a different color */
        .flatpickr-day.holiday {
            background-color: #ff9999; /* Slightly darker red */
            color: #cc00cc; /* Dark pink text */
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
    <nav>
        <i class='bx bx-menu'></i>
        <a href="#" class="nav-link">Categories</a>
        <form action="#">

        </form>
        <a href="infobox.php" class="info-box1" id="infoBoxLink1">
            <i class='bx bxs-info-circle bx-tada' style='font-size: 28px; color: #007bff;'></i>
        </a>
        <input type="checkbox" id="switch-mode" hidden>
        <label for="switch-mode" class="switch-mode"></label>
        <a href="widget.php" class="notification" id="notificationLink">
            <i class='bx bxs-bell' style='color: red;'></i>

            <!--begin::User Menu Dropdown-->
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-user"></i>
                    <span class="employee-name"><?php echo htmlspecialchars($fullname); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <!--begin::User Image-->
                    <li class="user-header text-bg-primary">
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
        </a>
    </nav>


    <main>
        <div class="head-title">
            <div class="left">
                <h1>Request Leave</h1>
                <ul class="breadcrumb">
                    <li>
                        <a >Home</a>
                    </li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li>
                        <a class="active" href="request_leave.php"> Request leave</a>
                    </li>
                </ul>
            </div>
        </div>
        
        
        <?php
        // Display success or error message if set in URL
        if (isset($_GET['success'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_GET['success']) . '</div>';
        } elseif (isset($_GET['error'])) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($_GET['error']) . '</div>';
        }
        
        ?>
        <div id="alertContainer" class="alert alert-warning alert-dismissible fade show" style="display: none; margin: 10px 0;">
            <button type="button" class="btn-close" aria-label="Close" onclick="dismissAlert()"></button>
            <div id="alertMessage"></div>
        </div>



        <div class="row">
            <!-- <h6 class="mb-4 font-weight-bold">Your Leave Balance</h6> -->
            <h7 class="mb-4 font-weight-bold">
                Here is your leave balance for Paid Time Off for January 1st - December 31st. (Overdue days from last year + Quota - Used days = Remaining days) </h7>
        </div>

        <div class="row">
            <!-- Small Box Widget (Forward Days) -->
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-warning p-2">
                    <div class="inner">
                        <h5 class="text-light mb-1">Overdue days from last years</h5>
                        <h3 class="text-light mb-1" id="forwardDays"></h3>
                    </div>
                    <i class="fa-solid fa-calendar-day small-box-icon"></i>
                </div>
            </div>

            <!-- Small Box Widget (Quota) -->
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-primary p-2">
                    <div class="inner">
                        <h5 class="text-light mb-1">Quota</h5>
                        <h3 class="text-light mb-1" id="quota"></h3>
                    </div>
                    <i class="fa-solid fa-calendar-check small-box-icon"></i>
                </div>
            </div>

            <!-- Small Box Widget (Used Days) -->
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-danger p-2">
                    <div class="inner">
                        <h5 class="text-light mb-1">Used Days</h5>
                        <h3 class="text-light mb-1" id="usedDays"></h3>
                    </div>
                    <i class="fa-solid fa-calendar-times small-box-icon"></i>
                </div>
            </div>

            <!-- Small Box Widget (Remaining Days) -->
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-success p-2">
                    <div class="inner">
                        <h5 class="text-light mb-1">Remaining</h5>
                        <h3 class="text-light mb-1" id="remainingDays"></h3>
                    </div>
                    <i class="fa-solid fa-calendar-plus small-box-icon"></i>
                </div>
            </div>
        </div>

        <div id="alert-container" class="container mt-1"></div>
        <div class="container-fluid p-0">
            <div class="form-container">
                <div class="bg-light rounded p-2">
                    <form action="submit_leave.php" method="POST" enctype="multipart/form-data">

                        <!-- Preselected Employee Section (Initially Hidden) -->
                        <div class="mb-1" id="preselected-container" style="display: none;">
                            <label for="preselectedOptions" class="form-label font-weight-bold required-label">Select an employee</label>
                            <select class="form-select form-select-sm" id="preselectedOptions" name="preselectedOptions">
                                <option selected disabled>Select an employee</option>
                            </select>
                        </div>

                        <!-- Hidden input fields -->
                        <input type="hidden" id="employee_fullname" name="employee_fullname" value="">
                        <input type="hidden" id="selected_employee" name="selected_employee" value="">

                        <!-- Leave Type Section -->
                        <div class="mb-1">
                            <label for="leaveType" class="form-label font-weight-bold required-label">Leave type</label>
                            <select class="form-select form-select-sm" id="leaveType" name="leaveType" required>
                                <option selected disabled>Select leave type</option>
                                <option value="Paid off">Paid off</option>
                                <option value="Sick leaves">Sick leaves</option>
                                <option value="School">School</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>

                        <!-- Start Date -->
                        <!-- Start Date -->
                        <div class="mb-1 position-relative">
                            <label for="startDate" class="form-label font-weight-bold required-label">Start Date</label>
                            <input type="date" class="form-control form-control-sm" id="startDate" name="startDate" required>
                            <small class="form-text text-muted">Select a date</small>
                        </div>

                        <!-- End Date -->
                        <div class="mb-1 position-relative">
                            <label for="endDate" class="form-label font-weight-bold required-label">End Date</label>
                            <input type="date" class="form-control form-control-sm" id="endDate" name="endDate" required>
                            <small class="form-text text-muted">Select a date</small>
                        </div>


                        <!-- Start Time for School Leave -->
                        <div class="mb-1 position-relative" id="startTimeContainer" style="display: none;">
                            <label for="startTime" class="form-label font-weight-bold required-label">Start Time</label>
                            <input type="time" class="form-control form-control-sm" id="startTime" name="startTime" required>
                        </div>

                        <!-- End Time for School Leave -->
                        <div class="mb-1 position-relative" id="endTimeContainer" style="display: none;">
                            <label for="endTime" class="form-label font-weight-bold required-label">End Time</label>
                            <input type="time" class="form-control form-control-sm" id="endTime" name="endTime" required>
                        </div>




                        <!-- Send To Section -->
                        <div class="mb-1">
                            <label for="sendTo" class="form-label font-weight-bold">Send To</label>
                            <select name="sendTo[]" id="sendTo" class="form-control form-control-sm chosen-select" multiple required>
                                <?php include 'fetch_emails.php'; ?>
                            </select>
                        </div>

                        <!-- Reason Section -->
                        <div class="mb-1">
                            <label for="reason" class="form-label font-weight-bold required-label">Reason</label>
                            <textarea class="form-control form-control-sm" id="reason" name="reason" placeholder="Leave a comment here" style="height: 60px;" required></textarea>
                        </div>

                        <!-- Medical Report Question -->
                        <div class="mb-1" id="medicalReportQuestionContainer" style="display: none;">
                            <label class="form-label required-label d-block mb-1">Medical Report?</label>
                            <div class="form-check form-check-inline me-2">
                                <input class="form-check-input" type="radio" id="medicalReportYes" name="medicalReportAvailable" value="Yes">
                                <label class="form-check-label" for="medicalReportYes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" id="medicalReportNo" name="medicalReportAvailable" value="No" checked>
                                <label class="form-check-label" for="medicalReportNo">No</label>
                            </div>
                        </div>

                        <!-- Medical Report Upload Section -->
                        <div class="mb-1" id="medicalReportContainer" style="display: none;">
                            <label for="medicalReportFile" class="form-label">Medical Report</label>
                            <input type="file" class="form-control form-control-sm" id="medicalReportFile" name="medicalReportFile">
                        </div>

                        <!-- Hidden input fields for employee details -->
                        <input type="hidden" id="employee" name="employee" value="<?php echo htmlspecialchars($fullname); ?>">
                        <input type="hidden" id="employee_id" name="employee_id" value="<?php echo htmlspecialchars($userIdDb); ?>">

                        <!-- Form buttons -->
                        <div class="d-flex justify-content-end mt-1">
                            <button type="reset" class="btn btn-outline-danger btn-sm me-1">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm">Submit Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


    </main>



</section>

<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.1.0/browser/overlayscrollbars.browser.es6.min.js" integrity="sha256-NRZchBuHZWSXldqrtAOeCZpucH/1n1ToJ3C8mSK95NU=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js" integrity="sha384-zYPOMqeu1DAVkHiLqWBUTcbYfZ8osu1Nd6Z89ify25QV9guujx43ITvfi12/QExE" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.min.js" integrity="sha384-Y4oOpwW3duJdCWv5ly8SCFYWqFDsfob/3GkgExXKV4idmbt98QcxXYs9UoXAB7BZ" crossorigin="anonymous"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<!-- Include Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script src="js/adminlte.js"></script>
<script src="script.js"></script>


<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Fetch leave balance data from the backend
        fetch('getLeaveBalance.php')
            .then(response => response.json())
            .then(data => {
                if (data && data.leaveBalance) {
                    const leaveBalance = data.leaveBalance;

                    // Directly display the leave balance values from the backend
                    document.getElementById('forwardDays').textContent = `${leaveBalance.brought_forward} days`;
                    document.getElementById('quota').textContent = `${leaveBalance.quota} days`;
                    document.getElementById('usedDays').textContent = `${leaveBalance.used} days`;
                    document.getElementById('remainingDays').textContent = `${leaveBalance.remaining} days`; // Use 'remaining' from backend

                } else {
                    // Show a warning if the leave balance data is not found
                    showAlert('Leave balance data not found in the response.', 'warning');
                    console.error('Leave balance data not found in response.');
                }
            })
            .catch(error => {
                // Handle errors during the fetch process
                showAlert('You are not authorized to make a leave request. Please contact HR for further assistance.', 'warning');
                console.error('Error fetching leave balance:', error);
            });

        // Function to display custom alert messages
        function showAlert(message, type = 'warning') {
            const alertContainer = document.getElementById('alertContainer');
            const alertMessage = document.getElementById('alertMessage');
            alertContainer.className = `alert alert-${type} alert-dismissible fade show`; // Dynamic Bootstrap alert class
            alertMessage.textContent = message;
            alertContainer.style.display = 'block';
        }

        // Function to dismiss alert
        function dismissAlert() {
            const alertContainer = document.getElementById('alertContainer');
            alertContainer.style.display = 'none';
        }
    });
</script>

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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fetch emails and populate the select box
        fetch('fetch_emails.php')
            .then(response => response.json())
            .then(data => {
                const sendToSelect = document.getElementById('sendTo');
                if (sendToSelect) {
                    data.forEach(user => {
                        const option = new Option(user.fullname, user.email, false, false);
                        option.title = user.email; // Optional: Show the email when hovering over the option
                        sendToSelect.options.add(option);
                    });
                    // Initialize Chosen on the #sendTo select element
                    $(sendToSelect).chosen({
                        no_results_text: "Oops, nothing found!"
                    }).val(null).trigger('chosen:updated');
                }
            })
            .catch(error => console.error('Error fetching emails:', error));
    });
</script>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        const leaveTypeSelect = document.getElementById('leaveType');
        const medicalReportQuestionContainer = document.getElementById('medicalReportQuestionContainer');
        const medicalReportContainer = document.getElementById('medicalReportContainer');
        const medicalReportYes = document.getElementById('medicalReportYes');
        const medicalReportNo = document.getElementById('medicalReportNo');
        const startTimeContainer = document.getElementById('startTimeContainer');
        const endTimeContainer = document.getElementById('endTimeContainer');
        const startTime = document.getElementById('startTime');
        const endTime = document.getElementById('endTime');

        // Function to update visibility based on leave type
        function updateVisibility() {
            const selectedLeaveType = leaveTypeSelect.value;

            // Display/hide medical report section for Sick leaves
            if (selectedLeaveType === 'Sick leaves') {
                medicalReportQuestionContainer.style.display = 'block';
                updateMedicalReportUploadVisibility(); // Call function to set initial upload visibility
            } else {
                medicalReportQuestionContainer.style.display = 'none';
                medicalReportContainer.style.display = 'none';
            }

            // Display/hide start and end time for School leave, and set required attributes
            const isSchoolLeave = selectedLeaveType === 'School';
            startTimeContainer.style.display = isSchoolLeave ? 'block' : 'none';
            endTimeContainer.style.display = isSchoolLeave ? 'block' : 'none';

            // Add/remove required attribute for start and end time inputs
            startTime.required = isSchoolLeave;
            endTime.required = isSchoolLeave;
        }

        // Function to update file upload visibility based on medical report question
        function updateMedicalReportUploadVisibility() {
            medicalReportContainer.style.display = medicalReportYes.checked ? 'block' : 'none';
        }

        // Event listeners
        leaveTypeSelect.addEventListener('change', updateVisibility);
        medicalReportYes.addEventListener('change', updateMedicalReportUploadVisibility);
        medicalReportNo.addEventListener('change', updateMedicalReportUploadVisibility);

        // Initial setup to reflect current selections
        updateVisibility();
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fetch user data and selected user IDs from the server
        fetch('fetch_user_ids.php')
            .then(response => response.json())
            .then(data => {
                // Retrieve the userId from the server-side PHP
                const userId = <?php echo json_encode($userIdDb); ?>;
                const preselectedContainer = document.getElementById('preselected-container');
                const preselectedSelect = document.getElementById('preselectedOptions');

                // Ensure userId is a string for comparison
                const userIdStr = String(userId);

                // Check if the current user's ID is in the selectedUserIds array
                if (data.selectedUserIds.includes(userIdStr)) {
                    // Show the preselected options container
                    preselectedContainer.style.display = 'block';

                    // Add options for users
                    data.allUsers.forEach(user => {
                        const opt = document.createElement('option');
                        opt.value = user.userid;
                        opt.textContent = user.fullname;
                        preselectedSelect.appendChild(opt);
                    });
                }
            })
            .catch(error => console.error('Error fetching user data:', error));

        // Add an event listener to update hidden inputs when selection changes
        document.getElementById('preselectedOptions').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const employeeId = selectedOption.value;
            const employeeName = selectedOption.textContent;

            // Update hidden inputs with selected employee details
            document.getElementById('employee_fullname').value = employeeName;
            document.getElementById('selected_employee').value = employeeId;
        });
    });
</script>

<script>
    // Function to check if a date is a weekend
    function isWeekend(date) {
        const day = date.getDay();
        return day === 6 || day === 0; // 0 = Sunday, 6 = Saturday
    }

    // Fetch holiday data from the server
    async function fetchHolidays() {
        try {
            const response = await fetch('getHolidays.php'); // Replace with your actual holiday-fetching endpoint
            const data = await response.json();
            return data.holidays; // Assuming the server returns a JSON object with a "holidays" array
        } catch (error) {
            console.error('Error fetching holidays:', error);
            return [];
        }
    }
  
    // Initialize Flatpickr for both date inputs
    async function initializeDatePickers() {
        const holidays = await fetchHolidays();

        // Define the options for Flatpickr
        const options = {
            dateFormat: "Y-m-d", // Format for the input
            onDayCreate: (dObj, dStr, fp, dayElem) => {
                const date = fp.parseDate(dayElem.dateObj); // Ensure the date is parsed correctly

                // Highlight weekends
                if (isWeekend(date)) {
                    dayElem.classList.add('weekend');
                }

                // Highlight holidays and add tooltip
                holidays.forEach(holiday => {
                    const holidayDate = new Date(holiday.date + 'T00:00:00'); // Create a Date object for the holiday

                    // Compare dates using toISOString to ensure format is consistent
                    if (holidayDate.toISOString().split('T')[0] === date.toISOString().split('T')[0]) {
                        dayElem.classList.add('holiday');
                        dayElem.setAttribute('title', holiday.name); // Add tooltip with holiday name
                        dayElem.style.cursor = 'pointer'; // Change cursor to pointer for better UX
                    }
                });
            }
        };

        // Initialize Flatpickr for both date inputs
        flatpickr("#startDate", options);
        flatpickr("#endDate", options);
    }

    // Call the function to initialize date pickers
    initializeDatePickers();
</script>




</body>
</html>