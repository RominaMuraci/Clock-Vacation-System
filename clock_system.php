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
    <link href="https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css">




    <link rel="stylesheet" href="style.css">
    <title>Time Clock</title>
    <style>
        .container {
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 600px;
            height: 600px;
            border-radius: 10px;
            position: relative;
            z-index: 1;
        }

        .grid-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .grid-item {
            background-color: #e0e0e0;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .grid-item:hover {
            background-color: #d0d0d0;
        }

        .grid-item img {
            width: 80px;
            height: 80px;
            margin-bottom: 30px;
        }

        .grid-item span {
            font-size: 16px;
        }

        .alert-container {
            position: absolute;
            top: 100px;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1001;
        }

        .clock {
            font-size: 24px;
            color: #333;
            font-weight: bold;
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: block;
            text-align: center;
            width: auto;
            min-width: 300px;
        }

        .clock span {
            display: block;
            width: 100px;
            text-align: center;
        }

        .alert {
            display: none;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .success { background-color: #4CAF50; }
        .error { background-color: #f44336; }


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
                    <h1>Clock System</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Home</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="clock_system.php">Clock System</a></li>
                    </ul>
                </div>
            </div>
      <div class="status-display" id="statusDisplay"></div> 
            <div id="alertMessage" class="alert" style="display:none;"></div>




            <div class="container">
                <header>
                    <h1>Time Clock</h1>
                    <p id="demo-company-name"></p>
                </header>
                <div class="grid-container">
                    <div id="clockInBtn" class="grid-item" onclick="clockAction('Clock In')">
                        <img src="icons/clock-in-icon.png" alt="Clock In">
                        <span>Clock in</span>
                    </div>
                    <div id="clockOutBtn" class="grid-item" onclick="clockAction('Clock Out')">
                        <img src="icons/clock-out-icon.png" alt="Clock Out">
                        <span>Clock out</span>
                    </div>
                    <div id="startBreakBtn" class="grid-item" onclick="clockAction('Start Break')">
                        <img src="icons/start-break-icon.png" alt="Start Break">
                        <span>Start break</span>
                    </div>
                    <div id="endBreakBtn" class="grid-item" onclick="clockAction('End Break')">
                        <img src="icons/end-break-icon.png" alt="End Break">
                        <span>End break</span>
                    </div>
                </div>
                <div class="notes-container">
    <form id="notesForm" action="save_notes.php" method="post">
        <div class="mb-3">
            <label for="notes" class="form-label">Notes:</label>
            <textarea id="notes" name="notes" class="form-control" rows="4"></textarea>
        </div>
        <input type="hidden" id="status" name="status" value="">
        <input type="hidden" id="action" name="action" value="">
        <input type="hidden" id="time" name="time" value="">

        <button type="submit" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Press Save to submit a note">Save Notes</button>
    </form>
</div>

            </div>
        </main>
    </section>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<!-- Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="script.js"></script>
 
    <script>
document.addEventListener('DOMContentLoaded', function() {
    fetchStatus(); // Fetch the current status when the document loads
});

let status = ''; // Variable to store the current status

// Fetch the current status from the server (fetch_status.php)
function fetchStatus() {
    return fetch('fetch_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            action: 'get_status' // Send action parameter
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            console.error(`Error: ${data.error}`);
            throw new Error(data.error);
        } else {
            // Update the status hidden input field
            document.getElementById('status').value = data.status; // Set the hidden field with the fetched status
            console.log("Fetched Status:", data.status); // Debug log
            updateButtonColors(data.status); // Call to update buttons based on status
        }
    })
    .catch(error => {
        console.error('Error fetching status:', error);
    });
}

// Function to handle the notes form submission
function saveNotes(event) {
    event.preventDefault(); // Prevent the default form submission

    // Fetch the latest status before submitting the notes
    fetchStatus().then(() => {
        // After the status is fetched, submit the form
        const form = document.getElementById('notesForm');
        const formData = new FormData(form); // Prepare the form data

        // Send the form data to save_notes.php
        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Notes saved successfully!');
                form.reset(); // Clear the form after submission
            } else {
                alert('Failed to save notes. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error saving notes:', error);
            alert('An error occurred while saving notes.');
        });
    });
}



// Function to update button colors based on the current status
function updateButtonColors(status) {
    const clockInBtn = document.getElementById('clockInBtn');
    const clockOutBtn = document.getElementById('clockOutBtn');
    const startBreakBtn = document.getElementById('startBreakBtn');
    const endBreakBtn = document.getElementById('endBreakBtn');

    // Reset button colors
    resetButtonColors(clockInBtn, clockOutBtn, startBreakBtn, endBreakBtn);

    // Set specific colors based on status
    switch (status) {
        case 'Clocked In':
            if (clockInBtn) clockInBtn.style.backgroundColor = '#4CAF50'; // Green
            break;
        case 'Clocked Out':
            if (clockOutBtn) clockOutBtn.style.backgroundColor = '#f44336'; // Red
            break;
        case 'On Break':
            if (startBreakBtn) startBreakBtn.style.backgroundColor = '#2196F3'; // Blue
            break;
        case 'Break Ended':
            if (endBreakBtn) endBreakBtn.style.backgroundColor = '#FFEB3B'; // Yellow
            break;
        case 'Clocked In and On Break':
            if (clockInBtn) clockInBtn.style.backgroundColor = '#4CAF50'; // Green
            if (startBreakBtn) startBreakBtn.style.backgroundColor = '#2196F3'; // Blue
            break;
        case 'Clocked In and Break Ended':
            if (clockInBtn) clockInBtn.style.backgroundColor = '#4CAF50'; // Green
            if (endBreakBtn) endBreakBtn.style.backgroundColor = '#FFEB3B'; // Yellow
            break;
        default:
            break;
    }
}

// Function to reset button colors to default
function resetButtonColors(clockInBtn, clockOutBtn, startBreakBtn, endBreakBtn) {
    if (clockInBtn) clockInBtn.style.backgroundColor = '';
    if (clockOutBtn) clockOutBtn.style.backgroundColor = '';
    if (startBreakBtn) startBreakBtn.style.backgroundColor = '';
    if (endBreakBtn) endBreakBtn.style.backgroundColor = '';
}

// Function to handle clock action (Clock In, Clock Out, Start Break, End Break)
function clockAction(action) {
    hideAllAlerts(); // Hide existing alerts

    const alertMessage = document.getElementById('alertMessage');

    // Set the action and time values
    document.getElementById('action').value = action;
    document.getElementById('time').value = new Date().toISOString().slice(0, 19).replace('T', ' '); // ISO format for time
    
    const data = new FormData();
    data.append('action', action);

    fetch('clock_action.php', {
        method: 'POST',
        body: data
    })
    .then(response => response.json())
    .then(result => {
        if (result.error) {
            alertMessage.textContent = result.error;
            alertMessage.className = "alert alert-danger";
            alertMessage.style.display = "block";
        } else if (result.success) {
            alertMessage.textContent = result.success;
            alertMessage.className = "alert alert-success";
            alertMessage.style.display = "block";
        }
        fetchStatus(); // Update status after action
    })
    .catch(error => {
        alertMessage.textContent = "An unexpected error occurred.";
        alertMessage.className = "alert alert-danger";
        alertMessage.style.display = "block";
        console.error('Error:', error);
    });

    setTimeout(() => {
        alertMessage.style.display = "none";
    }, 5000); // Hide the alert after 5 seconds
}

// Function to hide all alert messages
function hideAllAlerts() {
    const alertMessage = document.getElementById('alertMessage');
    if (alertMessage) {
        alertMessage.style.display = "none";
    }
}
</script>
<script>
// Function to display a success message if the success parameter is present in the URL
function displaySuccessMessage() {
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');
    if (success === 'true') {
        alert('Notes saved successfully!');
    }
}

// Call the function to check for the success parameter
displaySuccessMessage();
</script>


</body>
</html>
