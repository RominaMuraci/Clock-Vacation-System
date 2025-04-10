
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

// Start session to access session variables

if (!isset($_SESSION['login_session']) || !isset($_SESSION['userid'])) {
    header('Location: ../../../index.php');
    exit(); // Make sure to exit after redirection to prevent further code execution
}
// Assuming you store user data in session after login
$userIdDb = isset($_SESSION['userid']) ? $_SESSION['userid'] : '';
$fullname = isset($_SESSION['login_session']) ? $_SESSION['login_session'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Boxicons -->

    <link rel="icon" href="uploads/favicon.png" type="image/jpeg">

    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <!-- Font Awesome (Choose one version only) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">
    <!-- Overlay Scrollbars -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.1.0/styles/overlayscrollbars.min.css" integrity="sha256-LWLZPJ7X1jJLI5OG5695qDemW1qQ7lNdbTfQ64ylbUY=" crossorigin="anonymous">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">

</head>

<!-- Custom CSS -->
<link rel="stylesheet" href="style.css">
<title>Time Clock</title>
<style>
    /* Modal styling for padding and input sizing */
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

    .form-control {
        border-radius: 5px;
        box-shadow: none;
        transition: border-color 0.3s ease-in-out;
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: none;
    }

    button.btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }

    button.btn-secondary {
        background-color: #6c757d;
    }

    button.close {
        outline: none;
    }

    button.btn-danger {
        background-color: #cc0400;
        border-color: #007bff;
    }



    /* Table Styles */
    .table-container {
        margin-top: 20px;
        background-color: #f4f6f9; /* Light background for contrast */
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Soft shadow */
    }

    .table-responsive {
        overflow-x: auto; /* Ensure responsiveness */
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table th, .table td {
        padding: 10px 12px;
        text-align: center;
        border: 1px solid #dee2e6;
        font-size: 16px;
        vertical-align: middle;
    }
    .table tbody tr:nth-child(even) {
        background-color: #f2f2f2; /* Light gray background for even rows */
    }

    .table tbody tr:hover {
        background-color: #e9ecef; /* Slightly darker gray on hover */
        cursor: pointer; /* Pointer cursor on hover */
    }

    .table input[type="text"] {
        width: 100%;
        padding: 8px 10px;
        box-sizing: border-box;
        font-size: 12px;
        border-radius: 4px;
        border: 1px solid #ced4da;
    }

    .table input[type="text"]:focus {
        border-color: #007bff; /* Blue border on focus */
        outline: none;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.25); /*  Light blue shadow on focus */
    }

    .table th i {
        margin-left: 5px;
        cursor: pointer;
        color: #ffffff; /* White icons in header */
    }

    .table th i:hover {
        color: #FFD700; /* Gold color on hover */
    }

    .table th div {
        max-width: 150px;
        margin: 0 auto;
    }

    .inner-table th, .inner-table td {
        padding: 3px !important;
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
    /* Blue Circle Button */
    .button-blue-circle {
        appearance: none;
        background-color: #0044cc; /* Blue background */
        border-radius: 50%; /* Circle shape */
        border: none;
        box-shadow: #003399 0 -8px 4px inset; /* Darker blue shadow */
        color: white;
        cursor: pointer;
        display: inline-block;
        font-family: -apple-system, sans-serif;
        font-size: 0.9rem; /* Smaller font size */
        font-weight: 700;
        padding: 0.3rem 0.6rem; /* Reduced padding */
        text-align: center;
        text-decoration: none;
        transition: all .15s;
        user-select: none;
        width: 60px; /* Smaller size */
        height: 60px; /* Maintain circular shape */
    }

    .button-blue-circle:hover {
        background-color: #3366ff; /* Lighter blue on hover */
        box-shadow: #0033cc 0 -4px 6px inset; /* Darker blue shadow on hover */
        transform: scale(1.1); /* Slightly enlarges on hover */
    }

    .button-blue-circle:active {
        transform: scale(1.05); /* Slightly reduces scale on click */
    }

    /* Blue Rectangular Button */
    .button-blue-rect {
        appearance: none;
        background-color: #007bff; /* Blue background */
        border-radius: 0.5rem;
        border: none;
        box-shadow: #0056b3 0 -4px 4px inset; /* Darker blue shadow */
        color: white;
        cursor: pointer;
        display: inline-block;
        font-family: -apple-system, sans-serif;
        font-size: 0.75rem; /* Smaller font size */
        font-weight: 700;
        padding: 0.3rem 0.6rem; /* Reduced padding */
        text-align: center;
        text-decoration: none;
        transition: all .15s;
        user-select: none;
    }

    .button-blue-rect:hover {
        background-color: #3399ff; /* Lighter blue on hover */
        box-shadow: #0066cc 0 -4px 4px inset; /* Darker blue shadow on hover */
        transform: scale(1.1); /* Slightly enlarges on hover */
    }

    .button-blue-rect:active {
        transform: scale(1.05); /* Slightly reduces scale on click */
    }


    /* Red Circle Button */
    .button-red-circle {
        appearance: none;
        background-color: #cc0400; /* Red background */
        border-radius: 50%; /* Circle shape */
        border: none;
        box-shadow: #990300 0 -8px 4px inset; /* Darker red shadow */
        color: white;
        cursor: pointer;
        display: inline-block;
        font-family: -apple-system, sans-serif;
        font-size: 0.9rem; /* Smaller font size */
        font-weight: 700;
        padding: 0.3rem 0.6rem; /* Reduced padding */
        text-align: center;
        text-decoration: none;
        transition: all .15s;
        user-select: none;
        width: 60px; /* Smaller size */
        height: 60px; /* Maintain circular shape */
    }

    .button-red-circle:hover {
        background-color: #ff4d4d; /* Lighter red on hover */
        box-shadow: #cc0000 0 -4px 6px inset; /* Darker red shadow on hover */
        transform: scale(1.1); /* Slightly enlarges on hover */
    }

    .button-red-circle:active {
        transform: scale(1.05); /* Slightly reduces scale on click */
    }

    /* Red Rectangular Button */
    .button-red-rect {
        appearance: none;
        background-color: #e52c2c; /* Red background */
        border-radius: 0.5rem;
        border: none;
        box-shadow: #a20505 0 -4px 4px inset; /* Darker red shadow */
        color: white;
        cursor: pointer;
        display: inline-block;
        font-family: -apple-system, sans-serif;
        font-size: 0.75rem; /* Smaller font size */
        font-weight: 700;
        padding: 0.3rem 0.6rem; /* Reduced padding */
        text-align: center;
        text-decoration: none;
        transition: all .15s;
        user-select: none;
    }

    .button-red-rect:hover {
        background-color: #fc5151; /* Lighter red on hover */
        box-shadow: #d00505 0 -4px 4px inset; /* Darker red shadow on hover */
        transform: scale(1.1); /* Slightly enlarges on hover */
    }

    .button-red-rect:active {
        transform: scale(1.05); /* Slightly reduces scale on click */
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
        <a href="../../../logout.php" class="btn btn-default btn-flat">Sign out</a>
    </div>
</li>

                        </ul>
                    </li>
          </a>
</nav>
        <!-- NAVBAR -->
 
        <!-- MAIN -->
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Leave table</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="#">Home</a>
                        </li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li>
                            <a class="active" href="leave_table.php">Leave table</a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Check for status and message from the URL -->
            <?php if (isset($_GET['status']) && isset($_GET['message'])): ?>
                <?php
                $status = $_GET['status'];
                $message = urldecode($_GET['message']); // Decode URL-encoded message
                ?>
                <!-- Display success alert -->
                <?php if ($status == 'success'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> <?= $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>

                    <!-- Display error alert -->
                <?php elseif ($status == 'error'): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error!</strong> <?= $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="container-fluid">
                <!-- Button to Show the Form -->
                <button type="button" class="btn btn-primary my-3" id="addEmployeeButton">Add New Employee</button>

                <div id="preselected-container" class="container-fluid" style="display: none;">
                    <h5 class="mt-3">Update Days for Users Less than 1 Year and Add Old Employees</h5>
                    <div id="alert-container"></div>

                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <form id="updateForm" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="employee_select" class="form-label font-weight-bold">Select Employee:</label>
                                    <select id="employee_select" class="form-control" required>
                                        <option selected disabled>Select an employee</option>
                                        <!-- Options will be dynamically populated -->
                                    </select>
                                    <input type="hidden" id="selected_employee" name="employee_id" value=""> <!-- Hidden for employee ID -->
                                    <input type="hidden" id="selected_employee_name" name="employee_name" value=""> <!-- Hidden for employee name -->
                                </div>


                                <div class="mb-3">
                                    <label for="hire_date" class="form-label">Enter Hire Date:</label>
                                    <input type="date" id="hire_date" name="hire_date" class="form-control" required>
                                </div>

                                <button type="submit" class="btn btn-success" id="addRemainingButton">Add</button>

                                <div class="mt-3" id="processing" style="display: none;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Calculating...</span>
                                    </div>
                                    <span> Processing calculation, please wait...</span>
                                </div>

                                <!-- Display Step-by-Step Calculation -->
                                <div class="mt-3" id="calculationSteps" style="display: none;">
                                    <p>Step 1: Current Year Total Vacation Days divided by 12 months: <span id="divisionResult"></span></p>
                                    <p>Step 2: Months to work from hire date till the end of the year : <span id="monthsWorkedResult"></span></p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="timeClockTable">
                        <thead>
                        <tr>
                            <th scope="col">Employee</th>
                            <th scope="col">Overdue Days from last years</th>
                            <th scope="col">Used</th>
                            <th scope="col">Remaining</th>
                            <th scope="col">Sick days/ medical report</th>
                            <th scope="col">Quota</th>
                            <th scope="col">Year</th>
                            <th scope="col">Hire Date</th>
                            <th scope="col">Actions</th>


                        </tr>
                        </thead>

                        <thead>
                        <tr>
                            <th><input type="text" placeholder="Search Employee"></th>
                            <th><input type="text" placeholder="Search Overdue Days"></th>
                            <th><input type="text" placeholder="Search Used"></th>
                            <th><input type="text" placeholder="Search Remaining"></th>
                            <th><input type="text" placeholder="Search Sick Days"></th>
                            <th><input type="text" placeholder="Search Quota"></th>
                            <th><input type="text" placeholder="Search Year"></th>
                            <th><input type="text" placeholder="Search Hire Date"></th>
                            <th></th> <!-- Empty header for Actions column -->
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete this employee?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Modal for Editing Employee Leave Balance -->
            <div id="editModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel">Edit Employee Leave Balance</h5>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form id="editForm">
                            <div class="modal-body">
                                <input type="hidden" id="editEmployeeId">

                                <div class="form-group">
                                    <label for="editEmployeeName" class="font-weight-bold">Employee Name</label>
                                    <input type="text" class="form-control" id="editEmployeeName" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="editYear" class="font-weight-bold">Year</label>
                                    <input type="number" class="form-control" id="editYear" placeholder="Enter year" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="editHireDate" class="font-weight-bold">Hire date</label>
                                    <input type="date" class="form-control" id="editHireDate">
                                </div>

                                <div class="form-group">
                                    <label for="editQuota" class="font-weight-bold">Quota</label>
                                    <input type="number" class="form-control" id="editQuota" placeholder="Enter quota" required>
                                </div>

                                <div class="form-group">
                                    <label for="editBroughtForward" class="font-weight-bold">Overdue days from last year Days</label>
                                    <input type="number" class="form-control" id="editBroughtForward" placeholder="Enter Overdue days" required>
                                </div>

                                <div class="form-group">
                                    <label for="editUsed" class="font-weight-bold">Used</label>
                                    <input type="number" class="form-control" id="editUsed" placeholder="Enter used leave" required>
                                </div>

                                <div class="form-group">
                                    <label for="editRemaining" class="font-weight-bold">Remaining</label>
                                    <input type="number" class="form-control" id="editRemaining" placeholder="Enter remaining leave" readonly>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal for Status Message -->
            <div id="statusModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="statusModalLabel">Status</h5>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" id="statusMessage">
                            <!-- Status message will be inserted here -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Include jQuery, Bootstrap, DataTables, and Custom Script -->
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>

            <script src="script.js"></script>

            <script>
                $(document).ready(function () {
                    let isAdmin = false; // Initialize isAdmin variable

                    // Initialize DataTable
                    const table = $('#timeClockTable').DataTable({
                        processing: true,
                        serverSide: false,
                        paging: true,
                        pageLength: 10,
                        ajax: {
                            url: 'fetch_employees.php',
                            dataSrc: function (json) {
                                if (json.status === 'success') {
                                    isAdmin = json.isAdmin; // Store admin status
                                    return json.data; // Return employee data
                                } else {
                                    alert(`Error fetching data: ${json.message}`);
                                    return [];
                                }
                            },
                        },
                        columns: [
                            {
                                data: 'employee',
                                render: (data, type, row) =>
                                    `<a href='display_offdays.php?employee_name=${encodeURIComponent(row.employee)}&year=${row.year}'>${data}</a>`,
                            },
                            { data: 'brought_forward' },
                            { data: 'used' },
                            { data: 'remaining' },
                            {
                                data: 'sick_days',
                                render: (data) => data || '0', // Default to "0" if sick_days is null or undefined
                            },
                            { data: 'quota' },
                            { data: 'year' },
                            { data: 'hire_date' },
                            {
                                data: null,
                                orderable: false,
                                searchable: false,
                                render: (data, type, row) => `
                    <button data-id="${row.employee_id}" class="btn button-blue-rect edit-btn">Edit</button>
                    <button data-id="${row.employee_id}" class="btn button-red-rect remove-btn">Remove</button>
                `,
                                visible: false, // Initially hidden, shown if admin
                            },
                        ],
                        order: [[3, 'desc']], // Default order by "remaining" column
                        initComplete: function () {
                            if (isAdmin) {
                                // Show the "Actions" column
                                table.column(8).visible(true);
                            }
                        },
                    });

                    // Add column search functionality
                    $('#timeClockTable thead tr:eq(1) th input').on('keyup change', function () {
                        const colIdx = $(this).parent().index();
                        table.column(colIdx).search(this.value).draw();
                    });

                    // Fetch and update sick employees
                    function fetchSickEmployees() {
                        $.ajax({
                            url: 'fetch_sick_employees.php',
                            type: 'GET',
                            dataType: 'json',
                            success: function (response) {
                                if (Array.isArray(response)) {
                                    response.forEach((employee) => {
                                        table.rows().every(function () {
                                            const rowData = this.data();
                                            if (rowData.employee === employee.employee) {
                                                rowData.sick_days = employee.total_sick_days || '0';
                                                this.data(rowData).draw();
                                            }
                                        });
                                    });
                                } else {
                                    alert('Error: Unexpected response format');
                                }
                            },
                            error: function (xhr, status, error) {
                                console.error('Error fetching sick employees:', error);
                                alert('Failed to fetch sick employee data.');
                            },
                        });
                    }

                    fetchSickEmployees();

                    // Handle "Edit" button click
                    $(document).on('click', '.edit-btn', function () {
                        const employeeId = $(this).data('id');
                        const row = $(this).closest('tr');

                        // Populate modal fields
                        $('#editEmployeeId').val(employeeId);
                        $('#editEmployeeName').val(row.find('td:eq(0)').text().trim());
                        $('#editHireDate').val(row.find('td:eq(7)').text().trim());
                        $('#editYear').val(row.find('td:eq(6)').text().trim());
                        $('#editQuota').val(row.find('td:eq(5)').text().trim());
                        $('#editBroughtForward').val(row.find('td:eq(1)').text().trim());
                        $('#editUsed').val(row.find('td:eq(2)').text().trim());
                        $('#editRemaining').val(row.find('td:eq(3)').text().trim());

                        $('#editModal').modal('show');
                    });

                    // Handle "Edit Form" submission
                    $('#editForm').on('submit', function (event) {
                        event.preventDefault();

                        const formData = {
                            employee_id: $('#editEmployeeId').val(),
                            year: $('#editYear').val(),
                            quota: $('#editQuota').val(),
                            brought_forward: $('#editBroughtForward').val(),
                            used: $('#editUsed').val(),
                            remaining: $('#editRemaining').val(),
                            hire_date: $('#editHireDate').val(),
                        };

                        $.ajax({
                            url: 'edit_balance.php',
                            type: 'POST',
                            dataType: 'json',
                            data: formData,
                            success: function (response) {
                                if (response.status === 'success') {
                                    const row = $(`button[data-id='${formData.employee_id}']`).closest('tr');

                                    // Update the cells with the new values
                                    row.find('td:eq(5)').text(formData.quota);
                                    row.find('td:eq(1)').text(formData.brought_forward);
                                    row.find('td:eq(2)').text(formData.used);
                                    row.find('td:eq(3)').text(formData.remaining);
                                    row.find('td:eq(7)').text(formData.hire_date);

                                    // Close the modal and show a success message
                                    $('#editModal').modal('hide');
                                    showAlert('Leave balance updated successfully.', 'success');
                                } else {
                                    // Show an error message if the response is not successful
                                    showAlert(`Update failed: ${response.message}`, 'danger');
                                }
                            },
                            error: function (xhr, status, error) {
                                // Handle any errors during the AJAX request
                                let errorMessage = xhr.responseText || 'An error occurred';
                                showAlert(`Error: ${errorMessage}`, 'danger');
                            },
                        });
                    });
                    
                function calculateRemaining() {
                    const currentDate = new Date(); // Assume current date is today (e.g., Dec 15, 2024)
                    const hireDate = new Date(document.getElementById("editHireDate").value); // Hire date from input
                    const quota = parseFloat(document.getElementById("editQuota").value) || 0;
                    const broughtForward = parseFloat(document.getElementById("editBroughtForward").value) || 0;
                    const used = parseFloat(document.getElementById("editUsed").value) || 0;

                    // Calculate days worked since hire date, ignoring time portion
                    const oneDay = 1000 * 60 * 60 * 24; // milliseconds in a day
                    const daysWorked = Math.floor((currentDate.getTime() - hireDate.getTime()) / oneDay);

                    let remaining = 0;

                    // Debugging logs
                    console.log("Quota:", quota);
                    console.log("Brought Forward:", broughtForward);
                    console.log("Used:", used);
                    console.log("Days Worked:", daysWorked);

                    // If the employee has been hired for less than 1 year
                    if (daysWorked < 365) {
                        // Proportional calculation of leave based on the time worked in the first year
                        const proportionalLeave = (quota * (daysWorked / 365));
                        remaining = proportionalLeave + broughtForward - used;

                        console.log("Proportional Leave Calculated:", proportionalLeave);
                    } else {
                        // For employees with more than 1 year: full quota + brought forward - used
                        remaining = quota + broughtForward - used;
                    }

                    // Debug remaining value before flooring
                    console.log("Calculated Remaining (before flooring):", remaining);

                    // Ensure remaining days are rounded down and not negative
                    document.getElementById("editRemaining").value = Math.floor(remaining);

                    // Final debug log for output
                    console.log("Final Remaining (after flooring):", document.getElementById("editRemaining").value);
                }




                // Add event listeners to update the remaining days on input changes
                document.getElementById("editQuota").addEventListener("input", calculateRemaining);
                document.getElementById("editBroughtForward").addEventListener("input", calculateRemaining);
                document.getElementById("editUsed").addEventListener("input", calculateRemaining);
                document.getElementById("editHireDate").addEventListener("input", calculateRemaining);


                function showModal(message) {
                    $('#statusMessage').text(message);
                    $('#statusModal').modal('show');
                }

                // Handle the click event for the "Remove" button
                $(document).on('click', '.remove-btn', function() {
                    var employeeId = $(this).data('id');

                    $('#deleteModal').modal('show');

                    $('#confirmDeleteBtn').off('click').on('click', function() {
                        $.ajax({
                            url: 'remove_new_employee.php',
                            type: 'POST',
                            data: { employee_id: employeeId },
                            dataType: 'json',
                            success: function(response) {
                                if (response.status === 'success') {
                                    $("button[data-id='" + employeeId + "']").closest('tr').remove();
                                    $('#deleteModal').modal('hide');
                                    showAlert('Employee removed successfully.', 'success');
                                } else {
                                    showAlert('Error: ' + response.message, 'danger');
                                }
                            },
                            error: function(jqXHR) {
                                showAlert('Error occurred: ' + jqXHR.responseText, 'danger');
                            }
                        });
                    });
                });
                // Show the Add New Employee form when the button is clicked
                $('#addEmployeeButton').on('click', function() {
                    $('#preselected-container').toggle();
                });

                // Fetch User Data and Populate Dropdown
                fetch('fetch_user_ids.php')
                    .then(response => response.json())
                    .then(data => {
                        data.allUsers.forEach(user => {
                            $('<option>', {
                                value: user.userid,            // The employee's ID is used as the value
                                text: user.fullname            // The employee's full name is displayed as text
                            }).appendTo('#employee_select');
                        });
                    })
                    .catch(error => console.error('Error fetching user data:', error));

                // Capture selection of employee and update hidden fields
                $('#employee_select').on('change', function() {
                    // Get the selected employee's ID and name
                    var selectedEmployeeId = $(this).val();
                    var selectedEmployeeName = $('#employee_select option:selected').text();

                    // Update the hidden input fields with the selected values
                    $('#selected_employee').val(selectedEmployeeId);
                    $('#selected_employee_name').val(selectedEmployeeName);
                });



                // Handle the "Add New Employee" form submission
                $('#updateForm').on('submit', function(event) {
                    event.preventDefault();

                    var selectedEmployeeId = $('#selected_employee').val();
                    var selectedEmployeeName = $('#selected_employee_name').val();
                    var hireDate = $('#hire_date').val();

                    // Validation check
                    if (!selectedEmployeeId || !selectedEmployeeName) {
                        showAlert("Please select an employee and enter a hire date.", 'danger');
                        return;
                    }

                    $('#processing').show();

                    var formData = {
                        employee_id: selectedEmployeeId,
                        employee_name: selectedEmployeeName,
                        hire_date: hireDate
                    };

                    $.ajax({
                        url: 'add_new_employee.php',
                        type: 'POST',
                        dataType: 'json',
                        data: JSON.stringify(formData),
                        contentType: 'application/json',
                        success: function(response) {
                            $('#processing').hide();
                            if (response.status === 'success') {
                                $('#calculationSteps').show();
                                $('#divisionResult').text(response.divisionResult);
                                $('#monthsWorkedResult').text(response.monthsWorkedResult);
                                showAlert('Employee added successfully!', 'success');
                                location.reload();
                            } else {
                                showAlert('Error: ' + response.message, 'danger');
                            }
                        },
                        error: function(xhr, status, error) {
                            $('#processing').hide();
                            showAlert('An error occurred: ' + error, 'danger');
                        }
                    });
                });


                // Function to show alert messages
                function showAlert(message, type) {
                    var alertContainer = $('#alert-container');
                    var alertDiv = $('<div>', {
                        class: `alert alert-${type}`,
                        text: message
                    }).appendTo(alertContainer);

                    // Remove alert after 5 seconds
                    setTimeout(() => alertDiv.remove(), 5000);
                }
                });


            </script>
</body>
</html>
