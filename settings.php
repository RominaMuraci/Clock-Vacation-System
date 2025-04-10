
<?php
session_start();

// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);
//$isAdmin = isset($_SESSION['isadmin']) ? $_SESSION['isadmin'] : false;

// Redirect non-admin users to a different page (e.g., a 403 error page or home page)
$rootDir = __DIR__ . '../../../../';
$conn = include($rootDir . 'config/connection.php');

$userIdDb = $_SESSION['userid'];
 try {
     // Check if the user has permissions from admin_permissions_approve table
     $checkPermissionSql = "SELECT COUNT(*) as count FROM admin_permissions_approve WHERE admin_approve_id = ?";
     $checkPermissionStmt = $conn->prepare($checkPermissionSql);

     if ($checkPermissionStmt === false) {
         throw new Exception('Failed to prepare statement.');
     }

     // Execute the statement
     $checkPermissionStmt->execute([$userIdDb]);

     // Fetch the result
     $result = $checkPermissionStmt->fetch(PDO::FETCH_ASSOC);

     // Check if user has permission
     if (!$result || $result['count'] == 0) {
         header('Location: unauthorized.php'); // Redirect to unauthorized page
         // header('Location: settings.php?error=' . urlencode("You do not have permission to access this page."));
         exit();
     }

 } catch (PDOException $e) {
     error_log("Database error occurred: " . $e->getMessage());
     header('Location: settings.php?error=' . urlencode("Database error: You don't have permission to change the users."));
     exit();
 } catch (Exception $e) {
     error_log("Unexpected error occurred: " . $e->getMessage());
     header('Location: settings.php?error=' . urlencode("Unexpected error occurred. Please try again later."));
     exit();
 }
$fullname = isset($_SESSION['login_session']) ? $_SESSION['login_session'] : '';
// $current_year = 2025;
$current_year = date("Y");


// Fetch the current quota value
$quota = 22; // Default value if no quota is found

try {
    $stmt = $conn->prepare("SELECT quota FROM quota_timeclock WHERE year = :year LIMIT 1");
    $stmt->bindParam(':year', $current_year, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $quota = $result['quota'];
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo "Error: A database error occurred.";
}

$conn = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="uploads/favicon.png" type="image/jpeg">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.1.0/styles/overlayscrollbars.min.css" crossorigin="anonymous"> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.3.0/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- <link rel="stylesheet" href="css/adminlte.css"> -->
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"> 

    <title>Time Clock Settings</title>

    <style>
  

        .section-title {
            font-size: 1.5rem;
            color: #007bff;
            font-weight: 600;
        }

        .section-intro {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .app-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .app-card:hover {
            transform: translateY(-5px);
        }

        .app-card-body {
            padding: 20px;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
        }

        .app-btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            font-size: 1rem;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .app-btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .chosen-container .chosen-single {
            height: 38px;
            line-height: 38px;
            border-radius: 5px;
        }

        .chosen-container .chosen-drop {
            border-radius: 0 0 5px 5px;
        }

        .btn:focus, .form-control:focus, .chosen-container .chosen-drop {
            box-shadow: 0px 0px 10px rgba(0, 123, 255, 0.5);
        }

        .alert {
            border-radius: 5px;
            margin-top: 20px;
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fetch users for `sendTo`
            fetch('fetch_user_ids.php')
                .then(response => response.json())
                .then(data => {
                    const sendToSelect = document.getElementById('sendTo');
                    if (sendToSelect) {
                        sendToSelect.innerHTML = '';

                        data.allUsers.forEach(user => {
                            const option = new Option(user.fullname, user.userid, data.selectedUserIds.includes(user.userid), data.selectedUserIds.includes(user.userid));
                            option.title = user.fullname;
                            sendToSelect.options.add(option);
                        });

                        $(sendToSelect).chosen({
                            no_results_text: "No results found!",
                            width: "100%",
                            search_contains: true
                        }).trigger('chosen:updated');
                    }

                    const approveToSelect = document.getElementById('approveTo');
                    if (approveToSelect) {
                        approveToSelect.innerHTML = '';

                        data.allUsers.forEach(user => {
                            const option = new Option(user.fullname, user.userid, data.selectedUserApproveIds.includes(user.userid), data.selectedUserApproveIds.includes(user.userid));
                            option.title = user.fullname;
                            approveToSelect.options.add(option);
                        });

                        $(approveToSelect).chosen({
                            no_results_text: "No results found!",
                            width: "100%",
                            search_contains: true
                        }).trigger('chosen:updated');
                    }
                })
                .catch(error => console.error('Error fetching user data:', error));
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Get URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const success = urlParams.get('success'); // Capture 'success' parameter

            // Check if the success parameter exists and display the alert
            if (success) {
                showAlert('success', decodeURIComponent(success));
            }

            function showAlert(type, message) {
                // Prevent duplicate alerts
                const existingAlert = document.querySelector(`#alert-container .alert`);
                if (existingAlert) {
                    existingAlert.remove();
                }

                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
                alertDiv.role = 'alert';
                alertDiv.innerHTML = `
                            ${message}
                            <button type="button" class="close" aria-label="Close" onclick="this.parentElement.remove();">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        `;
                const alertContainer = document.getElementById('alert-container');
                alertContainer.appendChild(alertDiv);

                // Automatically close the alert after 5 seconds
                setTimeout(() => {
                    alertDiv.classList.remove('show');
                    alertDiv.classList.add('fade');
                    setTimeout(() => {
                        alertDiv.remove(); // Remove after fade out
                    }, 150); // Wait for the fade transition
                }, 5000);
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
                    <h1>Settings</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="#">Home</a>
                        </li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li>
                            <a class="active" href="settings.php"> Settings</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div id="alert-container"></div>
            <div class="app-wrapper">
                <div class="app-content pt-3 p-md-3 p-lg-4">
                    <div class="container-xl">
                        <div class="row g-4 settings-section">
                            <div class="col-12 col-md-4">
                                <h3 class="section-title">Current Year Total Vacation Days</h3>
                                <div class="section-intro">This section displays the annual quota assigned to employees, which is approximately 20 bussiness days or 4 calendar weeks. </div>
                            </div>
                            <div class="col-12 col-md-8">
                                <div class="app-card app-card-settings shadow-sm p-4">
                                    <div class="app-card-body">
                                        <form class="settings-form" method="POST" action="save_quota.php">
                                            <div class="mb-3">
                                                <label for="setting-input-1" class="form-label">Current Year Total Vacation Days</label>
                                                <input type="text" name="quota" class="form-control" id="setting-input-1" placeholder="Write quota here" value="<?php echo htmlspecialchars($quota); ?>" required>
                                            </div>
                                            <button type="submit" class="btn btn-primary mb-3">Save Changes</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="my-4">
                        <div class="row g-4 settings-section">
                            <div class="col-12 col-md-4">
                                <h3 class="section-title">Who can make requests for others?</h3>
                                <div class="section-intro">Make requests when the employee cannot do it. </div>
                            </div>
                            <div class="col-12 col-md-8">
                                <div class="app-card app-card-settings shadow-sm p-4">
                                    <div class="app-card-body">
                                        <form class="settings-form" method="POST" action="save_permissions.php">
                                            <div class="form-group">
                                                <label for="sendTo" class="form-label">Select users</label>
                                                <select name="selectusers[]" id="sendTo" class="form-control chosen-select" multiple required>
                                                    <!-- Options populated dynamically -->
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-primary mb-3">Save Changes</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr class="my-4">
                        <div class="row g-4 settings-section">
                            <div class="col-12 col-md-4">
                                <h3 class="section-title">Who can approve requests?</h3>
                                <div class="section-intro"> Here are the users that can approve requests. </div>
                            </div>
                            <div class="col-12 col-md-8">
                                <div class="app-card app-card-settings shadow-sm p-4">
                                    <div class="app-card-body">
                                        <form class="settings-form" method="POST" action="save_admin_permissions_approve.php">
                                        <div class="form-group">
                                             <label for="approveTo" class="form-label">Select users to approve</label>
                                             <select name="select_admin_approve[]" id="approveTo" class="form-control chosen-select" multiple required>
                                                 <!-- Options populated dynamically -->
                                             </select>
                                         </div>
                                         
                                            <button type="submit" class="btn btn-primary mb-3">Save Changes</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    
    
        </main>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.1.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
    <script src="js/adminlte.js"></script>
    <script src="script.js"></script>




   

</body>
</html>