<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$rootDir = __DIR__ . '../../../../';
$conn = include($rootDir . 'config/connection.php');

if (!$conn) {
    error_log("Database connection failed.");
    exit;
}

// Dynamically get the current year, you may use 2025 for testing.
$current_year = date("Y");  // Use current year dynamically
// $current_year = date("2025");
$previous_year = $current_year - 1;  // Calculate next year

try {
    // Fetch all employees' balances for the current year, including hire_date and employee name
    $fetch_balances_query = "
        SELECT employee_id, hire_date, employee, quota, used, brought_forward
        FROM employee_balances
        WHERE year = :year
        ORDER BY year DESC";
    $stmt_fetch_balances = $conn->prepare($fetch_balances_query);
    $stmt_fetch_balances->bindParam(':year', $previous_year, PDO::PARAM_INT);
    $stmt_fetch_balances->execute();
    $balances = $stmt_fetch_balances->fetchAll(PDO::FETCH_ASSOC);
    
    if (!$balances) {
        echo "No balances found for the year $previous_year.";
        exit;
    }
    
    foreach ($balances as $balance) {
        $employee_id = $balance['employee_id'];
        $hire_date = new DateTime($balance['hire_date']);
        $employee = $balance['employee'];
        $quota = $balance['quota'];
        $used = $balance['used'];
        $brought_forward = $balance['brought_forward'];
        
        // Calculate days worked since hire date
        $currentDate = new DateTime();
        $daysWorked = $currentDate->diff($hire_date)->days;
        
        // Determine the proportional leave based on the days worked
        if ($daysWorked < 365) {
            $proportionalLeave = $quota * ($daysWorked / 365);
        } else {
            $proportionalLeave = $quota;
        }
        
        // Calculate remaining leave dynamically
        $new_remaining = floor($proportionalLeave + $brought_forward - $used);
        
        // Ensure the remaining balance is not negative
        $new_remaining = max(0, $new_remaining);
        
        $used= "0";
        // Update the current year's balance: set brought_forward to 0 for the new year
        
        
        
        $update_query = "
   UPDATE employee_balances
   SET brought_forward = :brought_forward,
       remaining = :remaining,
       used = :used,
       year = :year
       
   WHERE employee_id = :employee_id";
        
        $stmt_update = $conn->prepare($update_query);
        
        // Debug: Log values to be bound
        echo "<br>new_remaining: $new_remaining<br>";
        echo "used: $used<br>";
        echo "employee_id: $employee_id<br>";
        echo "current_year: $current_year<br>";
        
        // Bind parameters
        $stmt_update->bindParam(':brought_forward', $new_remaining, PDO::PARAM_INT);
        $stmt_update->bindParam(':remaining', $new_remaining, PDO::PARAM_INT);
        $stmt_update->bindParam(':used', $used, PDO::PARAM_INT);
        $stmt_update->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
        $stmt_update->bindParam(':year', $current_year, PDO::PARAM_INT);
        
        // Execute the query and check for errors
        if ($stmt_update->execute()) {
            echo "Update successful.<br>";
        } else {
            $errorInfo = $stmt_update->errorInfo();
            echo "SQL Error: " . print_r($errorInfo, true) . "<br>";
        }
        
    }
    
    echo "Brought forward days updated and new year balances set.";
} catch (PDOException $e) {
    error_log("Error updating brought forward days: " . $e->getMessage());
    echo "Error updating brought forward days.";
}

$conn = null;
?>
