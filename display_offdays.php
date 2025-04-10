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

// Check if the user is logged in by verifying the session variable
if (!isset($_SESSION['login_session']) || !isset($_SESSION['userid'])) {
    // If the session variables are not set, redirect to the login page
    header('Location: ../../../index.php');
    exit(); // Make sure to exit after redirection to prevent further code execution
}

// Retrieve the user's full name and ID from the session
$fullname = isset($_SESSION['login_session']) ? $_SESSION['login_session'] : '';
$userIdDb = isset($_SESSION['userid']) ? $_SESSION['userid'] : '';

// // Check if the user is in the user_permissions_leave table
// $permissionQuery = "SELECT COUNT(*) FROM user_permissions_leave WHERE admin_id = :userid";
// $permissionStmt = $conn->prepare($permissionQuery);
// $permissionStmt->bindValue(':userid', $userIdDb, PDO::PARAM_INT);
// $permissionStmt->execute();
// $hasPermission = $permissionStmt->fetchColumn() > 0;

// // Debugging output for user permissions
// if ($hasPermission) {
//     echo "User ID $userIdDb is found in user_permissions_leave.";
// } else {
//     echo "User ID $userIdDb is not found in user_permissions_leave.";
// }

// // Check if the user is in the admin_permissions_approve table
// $approvalQuery = "SELECT COUNT(*) FROM admin_permissions_approve WHERE admin_approve_id = :userid";
// $approvalStmt = $conn->prepare($approvalQuery);
// $approvalStmt->bindValue(':userid', $userIdDb, PDO::PARAM_INT);
// $approvalStmt->execute();
// $hasApprovalPermission = $approvalStmt->fetchColumn() > 0;

// // Debugging output for admin permissions
// if ($hasApprovalPermission) {
//     echo "User ID $userIdDb is found in admin_permissions_approve.";
// } else {
//     echo "User ID $userIdDb is not found in admin_permissions_approve.";
// }

// // Prepare SQL query to fetch leave requests
// $sql = "SELECT * FROM leave_requests WHERE employee_id = :employee_id";
// if (!$hasPermission && !$hasApprovalPermission) {
//     // Add filter to only show data for the logged-in user
//     $sql .= " AND requested_by = :userid";
// }

// $stmt = $conn->prepare($sql);
// $stmt->bindValue(':employee_id', $userIdDb, PDO::PARAM_INT); // Bind userIdDb to the placeholder
// if (!$hasPermission && !$hasApprovalPermission) {
//     $stmt->bindValue(':userid', $userIdDb, PDO::PARAM_INT); // Bind userid to the placeholder if needed
// }
// $stmt->execute();

// $offDays = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="uploads/favicon.png" type="image/jpeg">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">

    <link rel="stylesheet" href="style.css">

    <title>Time Clock</title>
    <style>
        /* Table Styles */
        .table-container {
            margin-top: 20px;
            background-color: #f4f6f9;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 15px; /* Add padding to the container */
        }

        .table-responsive {
            overflow-x: auto;
            margin: 20px 0; /* Margin around the table */
        }


        .table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto; /* Allow dynamic column sizing */
        }
        @media (max-width: 768px) {
            .table th, .table td {
                font-size: 12px; /* Smaller font */
                padding: 10px; /* Adjust padding */
            }

            .form-input .form-control {
                max-width: 100%; /* Full width */
            }

            .button-group .btn {
                flex-basis: 100%; /* Full width buttons */
            }
        }


        .table th, .table td {
            padding: 12px 15px;
            text-align: center;
            border: 1px solid #dee2e6;
            font-size: 14px;
            vertical-align: middle;
        }

        /* .table thead th {
            background-color: #007bff;
            color: #ffffff;
            font-weight: bold;
            position: relative;
        } */

        .table tbody tr:nth-child(even) {
            background-color: #ffffff;
        }

        /* .table tbody tr:hover {
            background-color: #e9ecef;
            cursor: pointer;
        } */

        .table input[type="text"] {
            width: 100%;
            padding: 8px 10px;
            box-sizing: border-box;
            font-size: 12px;
            border-radius: 4px;
            border: 1px solid #ced4da;
        }

        .table input[type="text"]:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.25);
        }

        .table th i {
            margin-left: 5px;
            cursor: pointer;
            color: #ffffff;
        }

        .table th i:hover {
            color: #FFD700;
        }

        .table th div {
            max-width: 150px;
            margin: 0 auto;
        }

        .inner-table th, .inner-table td {
            padding: 3px !important;
        }
        .filter-container {
            margin-bottom: 20px;
        }

        .form-input {
            display: flex;
            align-items: center;
            gap: 10px; /* Adds space between elements */
        }

        .form-input .form-control {
            min-width: 150px;
            max-width: 300px; /* Sets the maximum width for the input field */
            flex: 1; /* Allows the input field to grow and fill available space */
        }

        /* Ensure no text wraps inside buttons */
        .btn-primary {
            white-space: nowrap; /* Prevents text from wrapping */
            background-color: #007bff; /* Primary button color */
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3; /* Darker blue on hover */
            border-color: #004494;
        }

        /* Button Group Layout */
        .button-group {
            display: flex;
            flex-wrap: wrap; /* Allow buttons to wrap */
            gap: 10px; /* Space between buttons */
        }

        .button-group .btn {
            flex: 1 1 120px; /* Allow buttons to grow, shrink, and set a base width */
        }


        /* Red Circle Button */
        .button-red-circle {
            appearance: none;
            background-color: #240000;
            border-radius: 40em; /* Circle shape */
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
        }

        .button-red-circle:hover {
            background-color: #ff4d4d;
            box-shadow: #cc0000 0 -6px 8px inset;
            transform: scale(1.125); /* Slightly enlarges on hover */
        }

        .button-red-circle:active {
            transform: scale(1.025); /* Slightly reduces scale on click */
        }

        /* Red Rectangular Button */
        .button-red-rect {
            appearance: none;
            background-color: #e52c2c;
            border-radius: 0.5rem;
            border-style: none;
            box-shadow: #a20505 0 -6px 6px inset;
            box-sizing: border-box;
            color: #ffffff;
            cursor: pointer;
            display: inline-block;
            font-family: -apple-system, sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            margin: 0;
            outline: none;
            padding: 0.5rem 1rem;
            text-align: center;
            text-decoration: none;
            transition: all .15s;
            user-select: none;
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
            background-color: #05ac05;
            border-radius: 0.5rem;
            border-style: none;
            box-shadow: #089408 0 -6px 6px inset;
            box-sizing: border-box;
            color: #ffffff;
            cursor: pointer;
            display: inline-block;
            font-family: -apple-system, sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            margin: 0;
            outline: none;
            padding: 0.5rem 1rem;
            text-align: center;
            text-decoration: none;
            transition: all .15s;
            user-select: none;
        }

        .button-green-rect:hover {
            background-color: #10bd10;
            box-shadow: #067006 0 -6px 6px inset;
            transform: scale(1.125);
        }

        .button-green-rect:active {
            transform: scale(1.025);
        }

        /* Yellow Rectangular Button (For Pending) */
        .button-yellow-rect {
            appearance: none;
            background-color: #f8c146;
            border-radius: 0.5rem;
            border-style: none;
            box-shadow: #d89e3a 0 -6px 6px inset;
            box-sizing: border-box;
            color: #000000;
            cursor: not-allowed; /* Status buttons should not be clickable */
            font-family: -apple-system, sans-serif;
            font-size: 0.875rem;
            font-weight: 700;
            padding: 0.5rem 1rem;
            text-align: center;
            text-decoration: none;
            user-select: none;
            transition: all .15s;
        }

        .button-yellow-rect:hover {
            transform: none; /* Yellow button should not change on hover as it’s disabled */
            background-color: #f8c146;
            box-shadow: #d89e3a 0 -6px 6px inset;
        }

        .button-yellow-rect:active {
            transform: none;
        }
        .sign-out-button {
            background-color: #4099ff;
            background-image: linear-gradient(to bottom, #4099ff, #73b4ff);
            transition: background-color 0.3s ease;
        }

        .sign-out-button a {
            color: white !important;
            text-decoration: none;
        }

        .sign-out-button:hover {
            background-color: #4cd2e0;
        }
        .modal-header {
            background-color: #007bff;
            color: white;
            border-bottom: none;
            padding: 15px 20px;
        }

        .modal-title {
            font-weight: 600;
        }

        .modal-body {
            padding: 20px;
            background-color: #f8f9fa;
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: none;
            background-color: #f1f1f1;
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

<!-- CONTENT -->
<section id="content">
    <!-- NAVBAR -->
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
                <h1>Requested Off days</h1>
                <ul class="breadcrumb">
                    <li><a href="#">Home</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active" href="display_offdays.php">Requested Off days</a></li>
                </ul>
            </div>
        </div>

        <!-- Filter Options -->
        <!-- Filter Options -->
        <div class="filter-container">
            <div class="form-input d-flex align-items-center">
                <input type="text" id="search-input" class="form-control" placeholder="Search by employee">
                <button type="button" class="btn btn-primary ml-2">
                    <i class='bx bx-search'></i>
                </button>
                <select id="leave-type-filter" class="form-select ml-3">
                    <option value="">All Leave Types</option>
                    <option value="Paid Off">Paid Off</option>
                    <option value="Others">Others</option>
                    <option value="Sick Leaves">Sick Leaves</option>
                </select>


                <div class="form-input d-flex align-items-center">
                    <label for="check-date" class="mr-2">Check date:</label>
                    <input type="date" id="check-date" class="form-control">
                    <button type="button" class="btn btn-primary ml-2" id="check-vacation-btn">Check Vacation</button>
                </div>
            </div>
        </div>
        <div id="vacation-results">
            <!-- Results will be displayed here -->
        </div>
        <!-- Notifications -->
        <div class="app-wrapper" id="notifications-container">
            <!-- Notifications will be dynamically inserted here by JavaScript -->
        </div>




    </main>


</section>

<!-- Modal Structure -->
<div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationModalLabel">Notification</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="notificationModalBody">
                <!-- Message will be inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>


<!-- Load jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Load Moment.js (before datetime-moment.js) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

<!-- Load DataTables JavaScript -->
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>

<!-- Load DataTables datetime sorting plugin (datetime-moment.js) -->
<script src="https://cdn.datatables.net/plug-ins/1.11.5/sorting/datetime-moment.js"></script>

<!-- Bootstrap JavaScript Bundle (Includes Popper.js) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Your custom JavaScript -->
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
        if (sidebarWrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== "undefined") {
            OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                scrollbars: {
                    theme: Default.scrollbarTheme,
                    autoHide: Default.scrollbarAutoHide,
                    clickScroll: Default.scrollbarClickScroll,
                },
            });
        }

        const filterSelect = document.getElementById('leave-type-filter');
        const searchInput = document.getElementById('search-input');
        const checkDateInput = document.getElementById('check-date');
        const checkVacationBtn = document.getElementById('check-vacation-btn');
        const container = document.getElementById('notifications-container');

// Extract parameters from URL
        const employeeName = getQueryParam('employee') || getQueryParam('employee_name') || '';
        const year = getQueryParam('year') || '';

        function getQueryParam(param) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        }

// Unified fetch function for leave requests and vacation records
        function fetchData(url, params) {
            return fetch(`${url}?${new URLSearchParams(params)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                });
        }

// Modify the createTable function to add unique class names to inputs for easier debugging and styling
        function createTable(data) {
            const table = document.createElement('table');
            table.classList.add('table', 'table-striped', 'table-bordered', 'data-table');

            // Create thead element
            const thead = document.createElement('thead');

            // First row for the headers (this row should be clickable for sorting)
            thead.innerHTML = `
        <tr>
            <th>Date</th>
            <th>Employee</th>
            <th>Leave Type</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Number of Days</th>
            <th>Reason</th>
            <th>Medical Report</th>
            <th>Requested by</th>
            <th>Send To</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    `;

            // Append thead to the table
            table.appendChild(thead);

            // Create tbody for the search inputs row (this row should not be sortable)
            const searchRow = document.createElement('tr');
            searchRow.innerHTML = `
        <th><input type="text" placeholder="Search Date" class="search-date" /></th>
        <th><input type="text" placeholder="Search Employee" class="search-employee" /></th>
        <th><input type="text" placeholder="Search Leave Type" class="search-leave-type" /></th>
        <th><input type="text" placeholder="Search Start Date" class="search-start-date" /></th>
        <th><input type="text" placeholder="Search End Date" class="search-end-date" /></th>
        <th><input type="text" placeholder="Search Start Time" class="search-start-time" /></th>
        <th><input type="text" placeholder="Search End Time" class="search-end-time" /></th>
        <th><input type="text" placeholder="Search Number of Days" class="search-days" /></th>
        <th><input type="text" placeholder="Search Reason" class="search-reason" /></th>
        <th><input type="text" placeholder="Search Medical Report" class="search-medical-report" /></th>
        <th><input type="text" placeholder="Search Requested by" class="search-requested-by" /></th>
        <th><input type="text" placeholder="Search Send To" class="search-send-to" /></th>
        <th><input type="text" placeholder="Search Status" class="search-status" /></th>
        <th><input type="text" placeholder="Search Actions" class="search-actions" /></th>
    `;

            // Append the search inputs row to the thead
            table.appendChild(searchRow);

            // Create tbody element and append rows based on the data
            const tbody = document.createElement('tbody');
            data.forEach(request => {
                const tr = createTableRow(request); // Assumes createTableRow is defined elsewhere
                tbody.appendChild(tr);
            });

            // Append tbody to the table
            table.appendChild(tbody);

            // Return the complete table
            return table;
        }




// Create a single row in the table
        function createTableRow(request) {
            const status = request.status.toLowerCase();
            let statusHtml = '';
            let buttonsHtml = '';

            // Render status buttons
            if (status === 'approved') {
                statusHtml = `<button type="button" class="btn button-green-rect" disabled>
                        <i class="fas fa-check"></i> Approved
                      </button>`;
            } else if (status === 'declined') {
                statusHtml = `<button type="button" class="btn button-red-rect" disabled>
                        <i class="fas fa-times"></i> Declined
                      </button>`;
            } else {
                statusHtml = `<button type="button" class="btn button-yellow-rect" disabled>
                        <i class="fas fa-hourglass-start"></i> Pending
                      </button>`;
                buttonsHtml = `
            <div class="button-group">
                <button type="button" class="btn button-green-rect approve-btn" data-id="${request.id}">Approve</button>
                <button type="button" class="btn button-red-rect decline-btn" data-id="${request.id}">Decline</button>
            </div>
        `;
            }

            let medicalReportHtml = '';
            if (request.leave_type.toLowerCase() !== 'paid off' && request.leave_type.toLowerCase() !== 'others') {
                medicalReportHtml = request.medical_report_url
                    ? `<a href="${request.medical_report_url}" download>Download Report</a>`
                    : 'No report available';
            }

            const tr = document.createElement('tr');

            tr.innerHTML = `
        <td>${request.created_at}</td>
        <td>${request.employee}</td>
        <td>${request.leave_type}</td>
        <td>${request.start_date}</td>
        <td>${request.end_date}</td>
        <td>${request.start_time}</td>
        <td>${request.end_time}</td>
        <td>${request.no_days}</td>
        <td>${request.reason}</td>
        <td>${medicalReportHtml}</td>
        <td>${request.requested_by}</td>
        <td>${request.send_to.split(',').join('<br>')}</td>
        <td class="status">${statusHtml}</td>
        <td>${buttonsHtml}</td>
    `;

            return tr;
        }


        function initializeDataTable(table) {
            // Load the moment.js date parser and DataTables date-time sorting plugin
            $.fn.dataTable.moment('DD/MM/YYYY');

            const dataTable = $(table).DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true,
                autoWidth: false,
                order: [[0, 'desc']], // Set initial sort to descending on the first column (which contains dates)
                initComplete: function () {
                    const api = this.api();

                    // Initialize search inputs for each column in the thead
                    api.columns().every(function (index) {
                        const column = this;
                        const input = $(table).find('input').eq(index); // Ensure this targets the correct input

                        input.on('keyup change clear', function () {
                            if (column.search() !== this.value) {
                                column.search(this.value).draw();
                            }
                        });
                    });
                }
            });
        }


        function handleRequest(id, action) {
            // Show a loading indicator (optional)
            const loadingIndicator = document.createElement('div');
            loadingIndicator.textContent = 'Processing...';
            document.body.appendChild(loadingIndicator); // Append to the body or a specific container

            fetch('handle_request.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    id: id,
                    action: action
                })
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Remove loading indicator
                    loadingIndicator.remove();

                    const row = document.querySelector(`.${action}-btn[data-id="${id}"]`).closest('tr');
                    const statusElement = row.querySelector('.status');

                    if (data.status === 'success') {
                        statusElement.innerHTML = action === 'approve'
                            ? `<button type="button" class="btn button-green-rect" disabled>
                    <i class="fas fa-check"></i> Approved
                  </button>`
                            : `<button type="button" class="btn button-red-rect" disabled>
                    <i class="fas fa-times"></i> Declined
                  </button>`;

                        // Remove the approve/decline buttons after the action
                        row.querySelector('.approve-btn').remove();
                        row.querySelector('.decline-btn').remove();

                        // Only update leave balance if action is 'approve'
                        if (action === 'approve') {
                            updateLeaveBalance(id);
                        } else {
                            showModal('Leave request has been declined.');
                        }
                    } else {
                        showModal('Failed to ' + action + ' the request: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error handling request:', error);
                    loadingIndicator.remove(); // Remove loading indicator if an error occurs
                    showModal('Failed to ' + action + ' the request due to a network error.');
                });
        }

// Function to show notifications as modals
        function showModal(message) {
            const modalBody = document.getElementById('notificationModalBody');
            modalBody.textContent = message; // Set the modal message

            // Show the modal using Bootstrap's modal method
            $('#notificationModal').modal('show');
        }

        function updateLeaveBalance(id) {
            // Show a loading message
            const loadingIndicator = document.createElement('div');
            loadingIndicator.textContent = 'Updating leave balance... Please wait.';
            document.body.appendChild(loadingIndicator);

            // Send the request to the server
            fetch('update_leave_balance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    id: id,
                    action: 'approve'  // Action for leave approval
                })
            })
                .then(response => {
                    if (response.ok) {
                        return response.json().catch(() => {
                            throw new Error('Invalid JSON response from server.');
                        });
                    } else {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                })
                .then(data => {
                    if (data.status === 'success') {
                        showModal('✅ Success: ' + data.message);
                    } else if (data.status === 'warning') {
                        showModal('⚠️ Warning: ' + data.message);
                    } else if (data.status === 'error') {
                        showModal('❌ Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error updating leave request:', error);
                    console.error('Debug info: Leave ID - ', id);
                    showModal('Failed to update leave request: ' + error.message);
                })
                .finally(() => {
                    if (loadingIndicator && loadingIndicator.parentNode) {
                        loadingIndicator.remove();
                    }
                });
        }

// Fetch initial leave requests (optional)
        fetchLeaveRequests();

// Fetch leave requests based on filters
        function fetchLeaveRequests(filter = '', search = '') {
            const params = {
                employee_name: employeeName,
                year: year,
                filter: filter,
                search: search
            };

            fetchData('fetch_leave_requests.php', params)
                .then(data => {
                    container.innerHTML = '';  // Clear existing content

                    if (data.error) {
                        container.innerHTML = `<p>${data.error}</p>`;
                        return;
                    }

                    const table = createTable(data);
                    container.appendChild(table);
                    initializeDataTable(table); // Initialize DataTables after inserting the table
                })
                .catch(error => console.error('Error fetching leave requests:', error));
        }

// Event listeners for filter and search inputs
        filterSelect.addEventListener('change', function() {
            fetchLeaveRequests(this.value, searchInput.value);
        });

        searchInput.addEventListener('input', function() {
            fetchLeaveRequests(filterSelect.value, this.value);
        });

        checkVacationBtn.addEventListener('click', function() {
            const checkDate = checkDateInput.value;

            if (!checkDate) {
                alert('Please select a date.');
                return;
            }
 
            fetch('check_vacation.php?date=' + encodeURIComponent(checkDate))
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('notifications-container');
                    container.innerHTML = '';  // Clear existing content

                    // Check for success and handle response
                    if (data.status !== 'success') {
                        container.innerHTML = `<p>${data.error || 'An error occurred.'}</p>`;
                        return;
                    }

                    const table = createTable(data.employees);
                    const tbody = table.querySelector('tbody');

                    // If no employees are found, display a message
                    if (data.employees.length === 0) {
                        const messageRow = document.createElement('tr');
                        const messageCell = document.createElement('td');
                        messageCell.colSpan = 12; // Adjust according to the number of columns
                        messageCell.innerHTML = 'No vacation records found for the selected date.';
                        messageRow.appendChild(messageCell);
                        tbody.appendChild(messageRow); // Make sure this is added to the tbody
                    }

                    container.appendChild(table);

                    // Initialize DataTable after adding the table to the DOM
                    $(table).DataTable();
                })
        });

// Use event delegation for the action buttons
        container.addEventListener('click', function(event) {
            const target = event.target;
            if (target.classList.contains('approve-btn')) {
                handleRequest(target.getAttribute('data-id'), 'approve');
            } else if (target.classList.contains('decline-btn')) {
                handleRequest(target.getAttribute('data-id'), 'decline');
            }
        });
    });


</script>



</body>
</html>