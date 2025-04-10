<?php
session_start();
// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
                <i class="fa fa-arrow-circle-left" style="color: #1E90FF;"></i>
                <span class="text">Billing System</span>
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
                <h1>Dashboard</h1>
                <ul class="breadcrumb">
                    <li><a href="#">Home</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active" href="dashboard.php">Dashboard</a></li>
                </ul>
            </div>
        </div>

        <section class="user-manual">
            <h2>Timeclock & Holiday Management System - User Manual</h2>

            <h3>Table of Contents</h3>
            <ul>
                <li><a href="#dashboard">1. Dashboard</a></li>
                <li><a href="#clock-system">2. Clock System</a></li>
                <li><a href="#timeclock-table">3. Timeclock Table</a></li>
                <li><a href="#timeline">4. Timeline</a></li>
                <li><a href="#request-leave">5. Request Leave</a></li>
                <li><a href="#requested-off-days">6. Requested Off Days</a></li>
                <li><a href="#leave-table">7. Leave Table</a></li>
                <li><a href="#holidays">8. Holidays</a></li>
                <li><a href="#settings">9. Settings</a></li>
                <li><a href="#billing-system">10. Billing System</a></li>
            </ul>

            <h3 id="dashboard">1. Dashboard</h3>
            <p>On the dashboard, you can find two tables:  Absent Employee Table and  Sick Employee Table.</p>
            <p>You can also check the period of time by using the start and end date filters to view specific time ranges for each employee.</p>

            <h3 id="clock-system">2. Clock System </h3>
            <p>To clock in, simply press the "Clock In" button. To clock out, press the "Clock Out" button.
                You can also start and end breaks during the day:
                <br>Start Break: Click on "Start Break" when you begin your break.
                <br>End Break: Click on "End Break" when you finish your break.
                <br>After you clock in, the system records your working hours automatically until you clock out.</p>

            <h3 id="timeclock-table">3. Timeclock Table</h3>
            <p>The Timeclock Table provides a detailed record of employee working hours, including clock-in and clock-out times, breaks, and total hours worked. Users can search and filter the table using various fields to find specific records.</p>

            <p><strong>Table Columns:</strong></p>
            <ul>
                <li><strong>Employee ID:</strong> A unique identifier assigned to each employee.</li>
                <li><strong>Day:</strong> The date of the recorded work entry.</li>
                <li><strong>Employee:</strong> The full name of the employee.</li>
                <li><strong>Clock In:</strong> The exact time when the employee clocked in.</li>
                <li><strong>Clock Out:</strong> The exact time when the employee clocked out.</li>
                <li><strong>Breaks:</strong> The number of breaks taken during the shift.</li>
                <li><strong>Total Hours/h:</strong> The total working hours recorded for the day.</li>
                <li><strong>Break Duration:</strong> The total duration of breaks taken.</li>
                <li><strong>Daily Total/h:</strong> The total hours worked after accounting for breaks.</li>
                <li><strong>Regular Hours/h:</strong> The number of regular working hours.</li>
                <li><strong>Overtime/h:</strong> Any extra hours worked beyond regular hours.</li>
                <li><strong>Notes:</strong> Additional comments or details related to the work entry.</li>
                <li><strong>Actions:</strong> Provides options to edit or manage the time entry.</li>
            </ul>

            <p>The table also includes search functionality for each field, allowing users to filter records based on employee ID, date, clock-in/out times, total hours, and other criteria.</p>

            <h3 id="timeline">4. Timeline</h3>
            <p>The timeline feature shows a detailed breakdown of your workday, including when you clocked in and out.</p>

            <h3 id="request-leave">5. Request Leave</h3>
            <p>Employees can request leave by filling out the leave request form. The form includes several fields to ensure accurate leave tracking and approval.</p>

            <p><strong>Leave Request Form Fields:</strong></p>

            <ul>
                <li><strong style="color:gold;">Overdue days from last year:</strong> Displays any unused leave days carried over from the previous year.</li>
                <li><strong style="color:blue;">Quota:</strong> Shows the total number of leave days the employee is entitled to for the current year.</li>
                <li><strong style="color:red;">Used Days:</strong> Indicates the number of leave days already taken by the employee.</li>
                <li><strong style="color:green;">Remaining:</strong> Displays the number of leave days still available for use.</li>
            </ul>


            <p><strong>Fields to Fill Out When Requesting Leave:</strong></p>
            <ul>
                <li><strong>Select an employee:</strong> Choose the employee for whom the leave request is being submitted (visible only to admins or managers).</li>
                <li><strong>Leave type:</strong> Select the type of leave from the available options:</li>
                <ul>
                    <li><strong>Paid Off:</strong> Typically used for extended holidays, such as summer vacations or long breaks. These leave days are deducted from the employee’s leave balance.</li>
                    <li><strong>Sick Leave:</strong> If the employee provides a medical report, these days are not deducted from the leave balance. However, without a medical report, they will be deducted.</li>
                    <li><strong>School Reason:</strong> Used for academic-related absences (e.g., exams, university obligations). These days are not deducted from the leave balance.</li>
                    <li><strong>Others:</strong> For any other reasons that do not fit into the categories above, such as personal matters, emergencies, or special occasions.  These days are not deducted from the leave balance.</li>
                </ul>

                <li><strong>Start Date:</strong> Choose the date when the leave will begin.</li>
                <li><strong>End Date:</strong> Choose the date when the leave will end.</li>
                <li><strong>Send To:</strong> Select the recipient(s) (e.g., manager, HR) who should receive the leave request for approval.</li>
                <li><strong>Reason:</strong> Provide a brief explanation for the leave request.</li>
            </ul>

            <p>Once the form is completed, the employee submits the request, which will be reviewed and processed by the relevant authority.</p>


            <h3 id="leave-table">7. Leave Table</h3>
            <p>The Leave Table provides a comprehensive view of all leave requests submitted by employees, along with their current leave balance and usage history. It helps track leave entitlements, carry-over days, and remaining leave days.</p>

            <p><strong>Columns in the Leave Table:</strong></p>
            <ul>
                <li><strong>Employee:</strong> The name of the employee.</li>
                <li><strong>Hire Date:</strong> The date the employee joined the company.</li>
                <li><strong>Year:</strong> The year for which leave balance is being calculated.</li>
                <li><strong>Quota:</strong> The total number of leave days allocated to the employee for the year.(Specified on settings as Current year Total Vacation Days).</li>
                <li><strong>Overdue Days:</strong> Any unused leave days carried over from previous years. Who will be used till 30 april</li>
                <li><strong>Used:</strong> The number of leave days the employee has already taken.</li>
                <li><strong>Remaining:</strong> The number of leave days still available for use. This value is dynamically updated based on the employee’s hire date and leave usage.</li>
                <ul>
                    <li><strong>For New Employees:</strong> The remaining leave is calculated proportionally based on the number of days worked in the current year:
                        <br><code>Remaining = (Quota / 365) * (Current Date - Hire Date) + Forward Days - Used</code>
                    </li>
                    <li><strong>For Old Employees:</strong> Employees who have completed at least one full year of work follow this formula:
                        <br><code>Remaining = Forward Days + Quota - Used</code>
                    </li>
                </ul>

                <li><strong>Actions:</strong> Options to manage the employee’s leave, such as editing or deleting</li>
            </ul>



            <h3 id="holidays">8. Holidays</h3>
            <p>Admins can manage holidays through the "Holidays" section, where they can:</p>
            <ul>
                <li><strong>Enable Holiday Flag:</strong> A toggle option to enable or disable holiday management for the current year. Once enabled, employees will see the registered holidays on their calendars.</li>
                <li><strong>Adding a New Holiday Manually:</strong>
                    <ul>
                        <li><strong>Select Holiday Date:</strong> Choose the date of the holiday from the date picker (format: dd/mm/yyyy).</li>
                        <li><strong>Holiday Name:</strong> Enter the name of the holiday (e.g., "New Year’s Eve" ).</li>
                        <li><strong>Save:</strong> Once entered, save the holiday to update the system records.</li>
                    </ul>
                </li>
                <li><strong>Bulk Uploading Holidays via CSV:</strong>
                    <ul>
                        <li><strong>Upload Holidays CSV:</strong> Allows admins to upload a list of holidays in bulk using a CSV (Comma-Separated Values) file.</li>
                        <li><strong>File Format:</strong> The CSV must contain two columns: Date (YYYY-MM-DD format) and Holiday Name.</li>
                        <li><strong>Example:</strong>
                            <pre>2024-01-01, Festat e Vitit te Ri </pre>
                        </li>
                    </ul>
                </li>
                <li><strong>Viewing and Managing Holidays:</strong>
                    <p>Below the form, a <strong>Holidays Table</strong> lists all previously added holidays. The table displays:</p>
                    <ul>
                        <li><strong>Date:</strong> The specific date of the holiday.</li>
                        <li><strong>Holiday Name:</strong> The title or reason for the holiday.</li>
                        <li><strong>Actions:</strong> Options to delete a holiday if needed.</li>
                    </ul>
                </li>
            </ul>


            <h3 id="settings">9. Settings</h3>
            <p>The settings section allows admins to adjust system configurations to suit the company’s needs. Below are some key configurations:</p>
            <ul>
                <li><strong>Current Year Total Vacation Days:</strong> This section displays the annual quota assigned to employees, typically around 22 business days (or approximately 4 calendar weeks).</li>
                <li><strong>Who can make requests for others?</strong> This option allows admins to specify who can make leave requests on behalf of employees who cannot do so themselves. Select the appropriate users.</li>
                <li><strong>Who can approve requests?</strong> This section shows the users who have the authority to approve leave requests. Admins can select users who have permission to approve requests for others.</li>
            </ul>


            <h3 id="billing-system">10. Billing System</h3>
            <p>The billing system allows you to be back on the system.</p>
        </section>
    </main>



    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="script.js"></script>

</body>
</html>

