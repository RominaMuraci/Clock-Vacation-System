<?php
session_start();
// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SESSION['isclient']) {
    header("Location: clientbalance.php");
}

$rootDir = __DIR__ . '../../../../';

// Include database connection
$conn = include($rootDir . 'config/connection.php');

$fullname = isset($_SESSION['login_session']) ? $_SESSION['login_session'] : '';
$userIdDb = isset($_SESSION['userid']) ? $_SESSION['userid'] : '';


// Check if the user is logged in by verifying the session variable
if (!isset($_SESSION['login_session']) || !isset($_SESSION['userid'])) {
    header('Location: ../../../index.php');
    exit(); // Make sure to exit after redirection to prevent further code execution
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="uploads/favicon.png" type="image/jpeg">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">


    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <title>Time Clock</title>
    <style>


        /* General styles for labels */
        .form-input label {
            margin-right: 10px;
            font-weight: bold;
            color: #495057;
            white-space: normal;
            word-wrap: break-word;
            display: block; /* Make labels block elements for full-width spacing */
            margin-bottom: 5px; /* Add bottom margin for spacing between label and input */
        }

        /* Input styles for dates */
        .form-input input[type="date"],
        .form-input input[type="text"] {
            margin-right: 10px;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            width: 150px; /* Fixed width */
            transition: border-color 0.3s ease;
        }

        /* Input focus effect */
        .form-input input[type="date"]:focus,
        .form-input input[type="text"]:focus {
            border-color: #007bff;
            outline: none;
        }

        /* Button styles */
        .form-input button {
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        /* Button hover effect */
        .form-input button:hover {
            background-color: #0056b3;
        }

        /* Table header styling */
        .table-header-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
            background-color: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 5px solid #007bff;
            text-align: left;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .form-input {
                display: flex;
                flex-direction: column; /* Column layout for small screens */
                gap: 10px; /* Add spacing between elements */
            }

            .form-input input[type="date"],
            .form-input input[type="text"] {
                width: 100%;           /* Full width for small screens */
            }

            .form-input button {
                width: 100%;           /* Full width for button */
            }
        }

        /* Sign-out button styles */
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
                <h1>Dashboard </h1>
                <ul class="breadcrumb">
                    <li><a href="#">Home</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active" href="dashboard.php"> Dashboard</a></li>
                </ul>
            </div>
        </div>

        <div class="row">
            <!-- Absent Employees Section -->
            <div class="col-md-6">
                <div class="form-input mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <label for="absent-start-date" class="mr-2">Absent Start date:</label>
                        <input type="date" id="absent-start-date" class="form-control mr-2" aria-label="Absent Start date">
                    </div>
                    <div class="d-flex align-items-center mb-2">

                        <label for="absent-end-date" class="mr-2">Absent End date:</label>
                        <input type="date" id="absent-end-date" class="form-control mr-2" aria-label="Absent End date">
                    </div>
                    <button type="button" class="btn btn-primary" id="check-absent-btn">Check Absent</button>
                </div>
                <h3 class="table-header-title">Absent Employee Table</h3>
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="absentEmployees">
                            <thead>
                            <tr class="table-light">
                                <th scope="col" class="table-header">Absence Date</th>
                                <th scope="col" class="table-header">Absent Employees</th>
                                <th scope="col" class="table-header">Num. of Absent Employees</th>
                            </tr>
                            </thead>
                            <thead>
                            <tr class="table-light">
                                <th><input type="date" class="form-control" placeholder="Search Absence Date" aria-label="Search Absence Date"></th>
                                <th><input type="text" class="form-control" placeholder="Search Absent Employees" aria-label="Search Absent Employees"></th>
                                <th><input type="text" class="form-control" placeholder="Search Num. of Absent Employees" aria-label="Search Num. of Absent Employees"></th>
                            </tr>
                            </thead>
                            <tbody>
                            <!-- PHP code to fetch absent employees can go here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sick Employees Section -->
            <div class="col-md-6">
                <div class="form-input mb-3">
                    <div class="d-flex align-items-center mb-2">

                        <label for="sick-start-date" class="mr-2">Sick Start date:</label>
                        <input type="date" id="sick-start-date" class="form-control mr-2" aria-label="Sick Start date">
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <label for="sick-end-date" class="mr-2">Sick End date:</label>
                        <input type="date" id="sick-end-date" class="form-control mr-2" aria-label="Sick End date">
                    </div>
                    <button type="button" class="btn btn-primary" id="check-sick-btn">Check Sick</button>
                </div>
                <h3 class="table-header-title">Sick Employee Table</h3>
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="sickEmployees">
                            <thead>
                            <tr class="table-light">
                                <th scope="col" class="table-header">Sick Interval</th>
                                <th scope="col" class="table-header">Sick Employee</th>
                                <th scope="col" class="table-header">Total Sick Days</th>

                            </tr>
                            </thead>
                            <thead>
                            <tr class="table-light">
                                <th><input type="text" class="form-control" placeholder="Search Date" aria-label="Search Date"></th>
                                <th><input type="text" class="form-control" placeholder="Search Sick Employee" aria-label="Search Sick Employee"></th>
                                <th><input type="text" class="form-control" placeholder="Search Total Sick Days" aria-label="Search Total Sick Days"></th>

                            </tr>
                            </thead>
                            <tbody>
                            <!-- PHP code to fetch sick employees can go here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </main>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="script.js"></script>
<script>
    $(document).ready(function () {
        // Set today's date as the default value for absent and sick date inputs
        const today = new Date().toISOString().split('T')[0]; // Get the current date in YYYY-MM-DD format
        $('#absent-start-date').val(today); // Set start date for absent to today
        $('#absent-end-date').val(today); // Set end date for absent to today
        $('#sick-start-date').val(today); // Set start date for sick to today
        $('#sick-end-date').val(today); // Set end date for sick to today

        // Initialize DataTables for absentEmployees with sorting and searching enabled
        const absentTable = $('#absentEmployees').DataTable({
            "paging": true, // Disable pagination
            "ordering": true, // Enable column sorting
            "order": [[0, 'asc']], // Sort by the first column (Date) in ascending order by default
            "searching": true // Ensure searching is enabled
        });

        // Initialize DataTables for sickEmployees with sorting and searching enabled
        const sickTable = $('#sickEmployees').DataTable({
            "paging": true, // Disable pagination
            "ordering": true, // Enable column sorting
            "order": [[0, 'asc']], // Sort by the first column (Date) in ascending order by default
            "searching": true // Ensure searching is enabled
        });

        // Custom search functionality for absentEmployees
        $('#absentEmployees thead th input').on('keyup change', function () {
            const index = $(this).parent().index();
            absentTable.column(index).search(this.value).draw();
        });

        // Custom search functionality for sickEmployees
        $('#sickEmployees thead th input').on('keyup change', function () {
            const index = $(this).parent().index();
            sickTable.column(index).search(this.value).draw();
        });

// Function to fetch absent employees
        function fetchAbsentEmployees() {
            const startDate = $('#absent-start-date').val();
            const endDate = $('#absent-end-date').val();

            if (!startDate || !endDate || new Date(endDate) < new Date(startDate)) {
                alert("Please select a valid date range.");
                return;
            }

            $.ajax({
                url: 'check_absent_employees.php',
                type: 'GET',
                data: { start_date: startDate, end_date: endDate },
                dataType: 'json',
                success(response) {
                    if (response.status === 'success') {
                        absentTable.clear(); // Clear existing rows before updating
                        response.employees.forEach(employee => {
                            // Skip entries with zero absent employees
                            if (parseInt(employee.num_of_absent_employees) === 0) return;

                            // Safely handle null or empty absent_employees data
                            const employeesList = employee.absent_employees
                                ? employee.absent_employees.split(',').map(emp => emp.trim())
                                : ['No absent employees'];

                            absentTable.row.add([
                                employee.date || 'N/A',
                                employeesList.join(', '),
                                employee.num_of_absent_employees,
                            ]).draw();
                        });
                    } else {
                        alert(response.message || "Failed to fetch data.");
                        console.error("Server Error:", response.message);
                    }
                },
                error(xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    alert("An error occurred while fetching absent employees: " + error);
                }
            });
        }

// Function to fetch sick employees
        function fetchSickEmployees() {
            const startDate = $('#sick-start-date').val();
            const endDate = $('#sick-end-date').val();

            if (!startDate || !endDate) {
                alert("Please select both start and end dates for sick employees.");
                return;
            }

            // Fetch sick employees data via AJAX
            $.ajax({
                url: 'check_sick_employees.php',
                type: 'GET',
                data: {
                    start_date: startDate,
                    end_date: endDate
                },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        sickTable.clear(); // Clear the table body

                        // Log the response to inspect its structure
                        console.log(response);

                        // Extract and display the general sick interval
                        const sickInterval = response.data.sick_interval || "No interval specified";
                        $('#sick-interval-display').text(sickInterval);

                        // Populate the table with new data
                        response.data.employees.forEach(function (employee) {
                            const employeeInterval = employee.sick_interval || 'N/A'; // Specific interval for each employee
                            const employeeName = employee.sick_employee || 'N/A'; // Employee name
                            const totalSickDays = employee.total_sick_days || 0; // Total sick days


                            // Add row to the table
                            sickTable.row.add([
                                employeeInterval,  // Display sick interval per employee
                                employeeName,
                                totalSickDays

                            ]).draw(); // Add row and redraw the table
                        });
                    } else {
                        alert(response.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", status, error);
                    alert("An error occurred while fetching sick employees.");
                }
            });
        }



        // Call fetchAbsentEmployees and fetchSickEmployees functions on button click
        $('#check-absent-btn').click(fetchAbsentEmployees);
        $('#check-sick-btn').click(fetchSickEmployees);

        // Automatically fetch data for both absent and sick employees when the page loads
        fetchAbsentEmployees();
        fetchSickEmployees();
    });

</script>
</body>
</html>

