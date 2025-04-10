<?php
session_start();


$fullname = isset($_SESSION['login_session']) ? $_SESSION['login_session'] : '';
$userIdDb = isset($_SESSION['userid']) ? $_SESSION['userid'] : '';

if (!isset($_SESSION['login_session']) || !isset($_SESSION['userid'])) {
    header('Location: ../../../index.php');
    exit(); // Make sure to exit after redirection to prevent further code execution
}
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
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/adminlte.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css">


    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.0/papaparse.min.js"></script>

    <!-- Chosen CSS -->

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
    .toggle-container {
        margin: 20px;
    }

    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }

    .toggle-switch input {
        display: none;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: 0.4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: 0.4s;
        border-radius: 50%;
    }

    input:checked + .slider {
        background-color: #2196F3;
    }

    input:checked + .slider:before {
        transform: translateX(26px);
    }


    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Check for 'error', 'message', and 'warning' query parameters in the URL
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');
            const message = urlParams.get('message');
            const warning = urlParams.get('warning');
            
            if (error) {
                showAlert('danger', decodeURIComponent(error));
            } else if (message) {
                showAlert('success', decodeURIComponent(message));
            } else if (warning) {
                showAlert('warning', decodeURIComponent(warning));
            }

            function showAlert(type, message) {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
                alertDiv.role = 'alert';
                alertDiv.innerHTML = `
                    ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                `;
                const alertContainer = document.getElementById('alert-container');
                alertContainer.appendChild(alertDiv);
            }
        });
    </script>
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
        <main>
            <div class="head-title">
                <div class="left">
                    <h1> Holidays</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="#">Home</a>
                        </li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li>
                            <a class="active" href="#">Holidays </a>
                        </li>
                    </ul>
                </div>
            </div>

    <div id="alert-container" class="container mt-3"></div>
            <div class="toggle-container" style="display: flex; align-items: center;">
                <label for="toggleSwitch" style="margin-right: 10px; font-weight: bold; color: #4A4A4A; font-size: 14px; text-transform: capitalize;">
                    Enable this flag after adding holidays for the current year.
                </label>
                <label class="toggle-switch">
                    <input type="checkbox" id="toggleSwitch">
                    <span class="slider"></span>
                </label>
            </div>
    
<div class="container-fluid">
    <div class="row">
        <!-- Column for adding a single holiday -->
        <div class="col-sm-12 col-md-6 col-lg-5 mb-3">
            <!-- Form for adding a holiday -->
            <form id="holidayForm" action="holidays.php" method="POST">
                <div class="mb-3">
                    <label for="holidayDate" class="form-label">Select Holiday Date</label>
                    <input type="date" class="form-control" id="holidayDate" name="holidayDate" required>
                </div>
                <div class="mb-3">
                    <label for="holidayName" class="form-label">Holiday Name</label>
                    <input type="text" class="form-control" id="holidayName" name="holidayName" placeholder="Enter holiday name" required>
                </div>
                <input type="hidden" name="action" value="add">
                <button type="submit" class="btn btn-primary mb-3">Add Holiday</button>
            </form>
        </div>
        
        <!-- Column for CSV upload -->
        <div class="col-sm-12 col-md-6 col-lg-7 mb-3">
            <!-- File Upload and Import Button -->
            <div class="mb-3">
                <label for="uploadHolidays" class="form-label">Upload Holidays CSV</label>
                <input type="file" class="form-control" id="uploadHolidays" accept=".csv">
            </div>
               <div class="alert alert-info" role="alert">
        Please upload a CSV file containing holiday dates. Make sure the format includes columns for <strong>date</strong> and <strong>holiday name</strong>.(i.e -> 2024-11-03, Festat e Vitit te Ri)
    </div>
            <button type="button" class="btn btn-success mb-3" id="exportHolidaysBtn">Import Holidays</button>
        </div>
    </div>

    <!-- Table to display holidays -->
    <div class="row">
        <div class="col-12">
            <table class="table" id="holidaysTable">
                <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Holiday Name</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Rows will be added here dynamically -->
                </tbody>
            </table>
        </div>
    </div>
</div>

</main>
    </section>


<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the holiday ?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>


    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.1.0/browser/overlayscrollbars.browser.es6.min.js" integrity="sha256-NRZchBuHZWSXldqrtAOeCZpucH/1n1ToJ3C8mSK95NU=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js" integrity="sha384-zYPOMqeu1DAVkHiLqWBUTcbYfZ8osu1Nd6Z89ify25QV9guujx43ITvfi12/QExE" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.min.js" integrity="sha384-Y4oOpwW3duJdCWv5ly8SCFYWqFDsfob/3GkgExXKV4idmbt98QcxXYs9UoXAB7BZ" crossorigin="anonymous"></script>
    






    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>


    <script src="js/adminlte.js"></script>
    <script src="script.js"></script>

<script>
        document.addEventListener("DOMContentLoaded", function () {
        const toggleSwitch = document.getElementById("toggleSwitch");

        // Fetch current toggle state from the server
        fetch('get_toggle_status.php')
        .then(response => {
        if (!response.ok) {
        throw new Error(`Server responded with status ${response.status}`);
    }
        return response.json();
    })
        .then(data => {
        if (data.success) {
        console.log("Current year_flag:", data.year_flag); // Debugging line
        toggleSwitch.checked = data.year_flag === 1; // Set toggle based on year_flag

        // If the year_flag is 0, send an email reminder
        if (data.year_flag === 0) {
        sendEmailReminder();
    }
    } else {
        console.error('Failed to fetch toggle status:', data.message);
        alert('Failed to fetch toggle status.');
    }
    })
        .catch(error => {
        console.error('Error fetching toggle status:', error);
        alert('An error occurred while fetching the toggle status.');
    });

        // Update toggle state in the database when it changes
        toggleSwitch.addEventListener("change", function () {
        const isEnabled = this.checked ? 1 : 0;
        console.log("Updating year_flag to:", isEnabled); // Debugging line

        fetch('update_toggle_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ year_flag: isEnabled })
    })
        .then(response => response.json())
        .then(data => {
        if (!data.success) {
        console.error('Failed to update toggle status:', data.message);
        alert('Failed to update toggle status.');
    }
    })
        .catch(error => {
        console.error('Error updating toggle status:', error);
        alert('An error occurred while updating the toggle status.');
    });
    });

        // Function to send an email reminder if the flag is 0
        function sendEmailReminder() {
        fetch('send_email_holiday.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ sendEmail: true }) // Trigger email sending
    })
        .then(response => response.json())
        .then(data => {
        if (data.success) {
        console.log('Email sent successfully:', data.message);
    } else {
        console.error('Failed to send email:', data.message);
    }
    })
        .catch(error => {
        console.error('Error sending email:', error);
    });
    }
    });




        $(document).ready(function() {
            function fetchHolidays() {
                $.ajax({
                    url: 'holidays.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#holidaysTable tbody').empty();
                        // Sort holidays by date (year, month, day)
                        // Sort holidays: first by year descending, then by month and day ascending
                        data.sort(function(a, b) {
                            const dateA = new Date(a.date);
                            const dateB = new Date(b.date);

                            // First compare years (descending)
                            if (dateA.getFullYear() !== dateB.getFullYear()) {
                                return dateB.getFullYear() - dateA.getFullYear(); // Year in descending order
                            }

                            // If years are the same, compare months (ascending)
                            if (dateA.getMonth() !== dateB.getMonth()) {
                                return dateA.getMonth() - dateB.getMonth(); // Month in ascending order
                            }

                            // If months are also the same, compare days (ascending)
                            return dateA.getDate() - dateB.getDate(); // Day in ascending order
                        });

                        // Append sorted holidays to the table
                        data.forEach(holiday => {
                            $('#holidaysTable tbody').append(`
                        <tr>
                            <td>${holiday.date}</td>
                            <td>${holiday.name}</td>
                            <td><button class="btn btn-danger btn-sm delete-btn" data-date="${holiday.date}" data-name="${holiday.name}">Delete</button></td>
                        </tr>
                    `);
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching holidays:", error);
                        $('#alert-container').html('<div class="alert alert-danger">Error fetching holidays. Please try again later.</div>');
                    }
                });
            }

    $('#holidayForm').on('submit', function(e) {
        e.preventDefault();
        const holidayDate = $('#holidayDate').val();
        const holidayName = $('#holidayName').val();

        $.ajax({
            url: 'holidays.php',
            type: 'POST',
            data: {
                holidayDate: holidayDate,
                holidayName: holidayName,
                action: 'add'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#holidayForm')[0].reset();
                    fetchHolidays();
                    $('#alert-container').html('<div class="alert alert-success">' + response.message + '</div>');
                } else {
                    $('#alert-container').html('<div class="alert alert-danger">' + response.error + '</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error adding holiday:", error);
                $('#alert-container').html('<div class="alert alert-danger">Failed to add holiday. Please try again later.</div>');
            }
        });
    });

    $(document).on('click', '.delete-btn', function() {
        const date = $(this).data('date');
        const name = $(this).data('name');
        $('#holidayName').text(name);
        $('#holidayDate').text(date);
        $('#confirmDeleteModal').modal('show');
        $('#confirmDeleteBtn').data('date', date);
    });

    $('#confirmDeleteBtn').on('click', function() {
        const date = $(this).data('date');

        $.ajax({
            url: 'holidays.php',
            type: 'DELETE',
            data: {
                holidayDate: date
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    fetchHolidays();
                    $('#alert-container').html('<div class="alert alert-success">' + response.message + '</div>');
                } else {
                    $('#alert-container').html('<div class="alert alert-danger">' + response.error + '</div>');
                }
                $('#confirmDeleteModal').modal('hide');
            },
            error: function(xhr, status, error) {
                console.error("Error deleting holiday:", error);
                $('#alert-container').html('<div class="alert alert-danger">Failed to delete holiday. Please try again later.</div>');
                $('#confirmDeleteModal').modal('hide');
            }
        });
    });

    $('#exportHolidaysBtn').on('click', function() {
    const fileInput = $('#uploadHolidays')[0];
    if (fileInput.files.length === 0) {
        alert('Please select a CSV file to upload.');
        return;
    }

    const file = fileInput.files[0];
    Papa.parse(file, {
        header: false,
        skipEmptyLines: true,
        complete: function(results) {
            const formattedHolidays = results.data.map(row => {
                // Split the date assuming the format is DD/MM/YYYY
                const [day, month, year] = row[0].split('/');
                // Reformat date to YYYY-MM-DD
                const formattedDate = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
                
                return {
                    date: formattedDate, // Reformat date to YYYY-MM-DD
                    name: row[1]  // CSV second column as holiday name
                };
            });

            $.ajax({
                url: 'import_holidays.php',
                type: 'POST',
                data: { holidays: JSON.stringify(formattedHolidays) },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        fetchHolidays(); // Refresh the holidays list
                        $('#alert-container').html('<div class="alert alert-success">' + response.message + '</div>');
                    } else {
                        $('#alert-container').html('<div class="alert alert-danger">' + response.error + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error importing holidays:", error);
                    $('#alert-container').html('<div class="alert alert-danger">Failed to import holidays. Please try again later.</div>');
                }
            });
        }
    });
});

    fetchHolidays();
});




    </script>




</body>
</html>