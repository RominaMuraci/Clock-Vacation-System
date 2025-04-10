<?php
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

// Fetch data from the timeclock table
$sql = "SELECT clock_id, day, employee_id, clock_in, clock_out, tot_hours, break_duration, daily_total, regular_hours, overtime, notes FROM timeclock";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Employee Time Clock</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            padding: 20px;
        }
        .table-container {
            margin-top: 50px;
        }
        input[type="text"] {
            width: 100%;
            box-sizing: border-box;
        }
    </style>
</head>

<body>
    <div class="container-xxl position-relative bg-white d-flex p-0">
        <!-- Spinner Start -->
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->

        <!-- Sidebar Start -->
        <div class="sidebar pe-4 pb-3">
            <nav class="navbar bg-light navbar-light">
                <a href="index.html" class="navbar-brand mx-4 mb-3">
                    <h3 class="text-primary"><i class="fa fa-hashtag me-2"></i>TIME CLOCK</h3>
                </a>
                <div class="navbar-nav w-100">
                    <a href="index.html" class="nav-item nav-link"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="fa fa-laptop me-2"></i>Elements</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="button.html" class="dropdown-item">Buttons</a>
                            <a href="typography.html" class="dropdown-item">Typography</a>
                            <a href="element.html" class="dropdown-item">Other Elements</a>
                        </div>
                    </div>
                    <a href="widget.html" class="nav-item nav-link"><i class="fa fa-th me-2"></i>Widgets</a>
                    <a href="form.html" class="nav-item nav-link"><i class="fa fa-keyboard me-2"></i>Forms</a>
                    <a href="table.html" class="nav-item nav-link active"><i class="fa fa-table me-2"></i>Tables</a>
                    <a href="chart.html" class="nav-item nav-link"><i class="fa fa-chart-bar me-2"></i>Charts</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="far fa-file-alt me-2"></i>Pages</a>
                        <div class="dropdown-menu bg-transparent border-0">
                            <a href="signin.html" class="dropdown-item">Sign In</a>
                            <a href="signup.html" class="dropdown-item">Sign Up</a>
                            <a href="404.html" class="dropdown-item">404 Error</a>
                            <a href="blank.html" class="dropdown-item">Blank Page</a>
                        </div>
                    </div>
                </div>
            </nav>
        </div>
        <!-- Sidebar End -->

        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <nav class="navbar navbar-expand bg-light navbar-light sticky-top px-4 py-0">
                <a href="index.html" class="navbar-brand d-flex d-lg-none me-4">
                    <h2 class="text-primary mb-0"><i class="fa fa-hashtag"></i></h2>
                </a>
                <a href="#" class="sidebar-toggler flex-shrink-0">
                    <i class="fa fa-bars"></i>
                </a>
                <form class="d-none d-md-flex ms-4">
                    <input class="form-control border-0" type="search" placeholder="Search">
                </form>
                <div class="navbar-nav align-items-center ms-auto">
                </div>
            </nav>
            <!-- Navbar End -->

            <!-- Table Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-sm-12 col-xl-12">
                        <div class="bg-light rounded h-100 p-4">
                            <h6 class="mb-4">Employee Time Clock</h6>
                            <table class="table table-bordered table-hover" id="timeClockTable">
                                <thead>
                                    <tr>
                                        <th scope="col" onclick="sortTable(0)">#</th>
                                        <th scope="col" onclick="sortTable(1)">Day</th>
                                        <th scope="col" onclick="sortTable(2)">Employee ID</th>
                                        <th scope="col" onclick="sortTable(3)">Clock In</th>
                                        <th scope="col" onclick="sortTable(4)">Clock Out</th>
                                        <th scope="col" onclick="sortTable(5)">Total Hours</th>
                                        <th scope="col" onclick="sortTable(6)">Break Duration</th>
                                        <th scope="col" onclick="sortTable(7)">Daily Total</th>
                                        <th scope="col" onclick="sortTable(8)">Regular Hours</th>
                                        <th scope="col" onclick="sortTable(9)">Overtime</th>
                                        <th scope="col" onclick="sortTable(10)">Notes</th>
                                    </tr>
                                    <tr>
                                        <th><input type="text" id="search0" placeholder="Search #"></th>
                                        <th><input type="text" id="search1" placeholder="Search Day"></th>
                                        <th><input type="text" id="search2" placeholder="Search Employee ID"></th>
                                        <th><input type="text" id="search3" placeholder="Search Clock In"></th>
                                        <th><input type="text" id="search4" placeholder="Search Clock Out"></th>
                                        <th><input type="text" id="search5" placeholder="Search Total Hours"></th>
                                        <th><input type="text" id="search6" placeholder="Search Break Duration"></th>
                                        <th><input type="text" id="search7" placeholder="Search Daily Total"></th>
                                        <th><input type="text" id="search8" placeholder="Search Regular Hours"></th>
                                        <th><input type="text" id="search9" placeholder="Search Overtime"></th>
                                        <th><input type="text" id="search10" placeholder="Search Notes"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo "<tr>";
                                            echo "<th scope='row'>" . $row["clock_id"] . "</th>";
                                            echo "<td>" . $row["day"] . "</td>";
                                            echo "<td>" . $row["employee_id"] . "</td>";
                                            echo "<td>" . $row["clock_in"] . "</td>";
                                            echo "<td>" . $row["clock_out"] . "</td>";
                                            echo "<td>" . $row["tot_hours"] . "</td>";
                                            echo "<td>" . $row["break_duration"] . "</td>";
                                            echo "<td>" . $row["daily_total"] . "</td>";
                                            echo "<td>" . $row["regular_hours"] . "</td>";
                                            echo "<td>" . $row["overtime"] . "</td>";
                                            echo "<td>" . $row["notes"] . "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='11'>No records found</td></tr>";
                                    }
                                    mysqli_close($conn);
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Table End -->

        </div>
        <!-- Content End -->

        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    <script>
        // Search functionality for each column
        $(document).ready(function() {
            $("#search0").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#timeClockTable tbody tr").filter(function() {
                    $(this).toggle($(this).children("th, td").eq(0).text().toLowerCase().indexOf(value) > -1);
                });
            });
            $("#search1").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#timeClockTable tbody tr").filter(function() {
                    $(this).toggle($(this).children("td").eq(0).text().toLowerCase().indexOf(value) > -1);
                });
            });
            $("#search2").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#timeClockTable tbody tr").filter(function() {
                    $(this).toggle($(this).children("td").eq(1).text().toLowerCase().indexOf(value) > -1);
                });
            });
            $("#search3").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#timeClockTable tbody tr").filter(function() {
                    $(this).toggle($(this).children("td").eq(2).text().toLowerCase().indexOf(value) > -1);
                });
            });
            $("#search4").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#timeClockTable tbody tr").filter(function() {
                    $(this).toggle($(this).children("td").eq(3).text().toLowerCase().indexOf(value) > -1);
                });
            });
            $("#search5").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#timeClockTable tbody tr").filter(function() {
                    $(this).toggle($(this).children("td").eq(4).text().toLowerCase().indexOf(value) > -1);
                });
            });
            $("#search6").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#timeClockTable tbody tr").filter(function() {
                    $(this).toggle($(this).children("td").eq(5).text().toLowerCase().indexOf(value) > -1);
                });
            });
            $("#search7").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#timeClockTable tbody tr").filter(function() {
                    $(this).toggle($(this).children("td").eq(6).text().toLowerCase().indexOf(value) > -1);
                });
            });
            $("#search8").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#timeClockTable tbody tr").filter(function() {
                    $(this).toggle($(this).children("td").eq(7).text().toLowerCase().indexOf(value) > -1);
                });
            });
            $("#search9").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#timeClockTable tbody tr").filter(function() {
                    $(this).toggle($(this).children("td").eq(8).text().toLowerCase().indexOf(value) > -1);
                });
            });
            $("#search10").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#timeClockTable tbody tr").filter(function() {
                    $(this).toggle($(this).children("td").eq(9).text().toLowerCase().indexOf(value) > -1);
                });
            });
        });

        // Sort functionality
        function sortTable(n) {
            var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
            table = document.getElementById("timeClockTable");
            switching = true;
            dir = "asc";
            while (switching) {
                switching = false;
                rows = table.rows;
                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("TD")[n];
                    y = rows[i + 1].getElementsByTagName("TD")[n];
                    if (dir == "asc") {
                        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir == "desc") {
                        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    switchcount++;
                } else {
                    if (switchcount == 0 && dir == "asc") {
                        dir = "desc";
                        switching = true;
                    }
                }
            }
        }
    </script>
</body>

</html>
