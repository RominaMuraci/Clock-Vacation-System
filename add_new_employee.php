<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Set up root directory and include database connection
$rootDir = __DIR__ . '../../../../';
$conn = include($rootDir . 'config/connection.php');

// Check for user login session
if (!isset($_SESSION['login_session']) || !isset($_SESSION['userid'])) {
    header('Location: ../../../index.php');
    exit();
}

// Fetch the current year and quota
$current_year = date('Y');
$quota_query = "SELECT quota FROM quota_timeclock WHERE year = :year";
$stmt_quota = $conn->prepare($quota_query);

if ($stmt_quota) {
    $stmt_quota->bindParam(':year', $current_year, PDO::PARAM_INT);
    $stmt_quota->execute();
    $stmt_quota->bindColumn(1,$quota);
    if (!$stmt_quota->fetch(PDO::FETCH_BOUND)) {
        error_log("No quota found for the year: $current_year");
        echo json_encode(['status' => 'error', 'message' => 'No quota found for the current year.']);
        exit();
    }
    $stmt_quota->closeCursor();
} else {
    die("Prepare failed: " . $conn->errorInfo()[2]);
}

// Ensure data is being sent via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode JSON input
    $inputData = json_decode(file_get_contents('php://input'), true);
    
    // Retrieve and sanitize data from decoded JSON
    $employee_id = isset($inputData['employee_id']) ? $inputData['employee_id'] : null;
    $employee_name = isset($inputData['employee_name']) ? $inputData['employee_name'] : null;
    $hire_date = isset($inputData['hire_date']) ? $inputData['hire_date'] : null;
    
    // Debugging: Log the received data
    error_log("Received data: employee_id=$employee_id, employee_name=$employee_name, hire_date=$hire_date, quota=$quota");
    
    // Validate required fields
    if (empty($employee_id) || empty($employee_name) || empty($hire_date) || empty($quota)) {
        // Output missing fields for better troubleshooting
        $missingFields = [];
        if (empty($employee_id)) $missingFields[] = 'employee_id';
        if (empty($employee_name)) $missingFields[] = 'employee_name';
        if (empty($hire_date)) $missingFields[] = 'hire_date';
        if (empty($quota)) $missingFields[] = 'quota';
        
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields: ' . implode(', ', $missingFields)]);
        exit();
    }
    
    // Try to create a DateTime object from the hire_date
    try {
        $hireDateObj = new DateTime($hire_date);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid hire date format.']);
        exit();
    }
    // $currentDate = new DateTime('December 28, ' . date('2025'));
    // $currentDate = new DateTime('November 12, 2024');
    // // Set current date and calculate one year ago
    // $currentDate = new DateTime();
    // $oneYearAgo = (new DateTime())->modify('-1 year');
    
    // // Determine if the employee is a new hire (hired within the last year)
    // $isNewEmployee = $hireDateObj > $oneYearAgo;
    
    // // Calculate remaining days based on new or old employee status
    // if ($isNewEmployee) {
    //     $monthsWorked = $hireDateObj->diff($currentDate)->m + ($hireDateObj->diff($currentDate)->y * 12);
    //     $vacationPerMonth = $quota / 12;
    //     $remainingDays = min($quota, ($vacationPerMonth * $monthsWorked));
    // } else {
    //     $remainingDays = $quota;
    // }
    
    // // Format the hire date into 'Y-m-d' format
    $formattedHireDate = $hireDateObj->format('Y-m-d');
    
    // Check if the employee already exists for the current year
    $checkQuery = "SELECT COUNT(*) FROM employee_balances WHERE employee_id = :employee_id AND year = :year";
    $stmt_check = $conn->prepare($checkQuery);
    $stmt_check->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
    $stmt_check->bindParam(':year', $current_year, PDO::PARAM_INT);
    $stmt_check->execute();
    $existingRecord = $stmt_check->fetchColumn();
    
    if ($existingRecord > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Employee already exists for the current year.']);
        exit();
    }
    
    // Insert the data into the database
    try {
        $sql = "INSERT INTO employee_balances (employee_id, employee, hire_date, quota, remaining, year)
                VALUES (:employee_id, :employee, :hire_date, :quota, :remaining, :year)";
        $stmt = $conn->prepare($sql);
        
        $stmt->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
        $stmt->bindParam(':employee', $employee_name, PDO::PARAM_STR);
        $stmt->bindParam(':hire_date', $formattedHireDate, PDO::PARAM_STR);
        $stmt->bindParam(':quota', $quota, PDO::PARAM_INT);
        $stmt->bindParam(':year', $current_year, PDO::PARAM_INT);
        $stmt->bindParam(':remaining', $remainingDays, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Employee data added successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'An error occurred while saving data.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
