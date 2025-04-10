<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$rootDir = __DIR__ . '../../../../';
$conn = include($rootDir . 'config/connection.php');

// Check if connection is successful
if (!$conn) {
    error_log("Database connection failed.");
    exit;
}

// $current_year = date("2025");  // Change this to the year you want to test
$current_year = date("Y");
// $next_year = 2026;     // The next year for testing

// Get current date to check if it's after March 31st
$currentDate = new DateTime();
// $currentDate = new DateTime('2025-04-01');
$isAfterApril = ($currentDate->format('m-d') >= '04-01');  // Check if it's after April 1st

// Only proceed with the reset if it's after March 31st (April 1st)
if ($isAfterApril) {
    try {
        $conn->beginTransaction();
        
        // Fetch all employees' balances for the current year
        $fetch_balances_query = "SELECT employee_id, employee, quota, used, brought_forward, hire_date FROM employee_balances WHERE year = :year";
        $stmt_fetch_balances = $conn->prepare($fetch_balances_query);
        $stmt_fetch_balances->bindParam(':year', $current_year, PDO::PARAM_INT);
        $stmt_fetch_balances->execute();
        $balances = $stmt_fetch_balances->fetchAll(PDO::FETCH_ASSOC);
        
        // Loop through each employee's balance to reset and recalculate
        foreach ($balances as $balance) {
            $employee_id = $balance['employee_id'];
            $employee = $balance['employee'];
            $quota = $balance['quota'];
            $used = $balance['used'];
            $brought_forward = $balance['brought_forward'];
            $hire_date = new DateTime($balance['hire_date']);  // Hire date for new employees
            
            // Debug output
            echo "Processing Employee ID: $employee_id, Used: $used, Brought Forward: $brought_forward, Hire Date: {$hire_date->format('Y-m-d')}<br>";
            
            // Initialize variables
            $used_from_brought_forward = 0;
            $used_from_current_quota = 0;
            $new_remaining = 0;
            // $new_used = 0;
            
            // Calculate remaining days for new employees (pro-rata if hired after the start of the year)
            $daysWorked = $currentDate->diff($hire_date)->days;  // Days worked by employee
            
            if ($brought_forward > 0) {
                // Calculate how much of the used days were taken from the brought forward days
                if ($used <= $brought_forward) {
                    $used_from_brought_forward = $used;
                    $used_from_current_quota = 0;
                } else {
                    $used_from_brought_forward = $brought_forward;
                    $used_from_current_quota = $used - $brought_forward;
                }
                
                // Calculate new remaining days for old employees (full year)
                $new_remaining = $quota - $used_from_current_quota;
                $new_used = 0;  // Reset used to 0 for the next year
                echo "Brought Forward > 0: Resetting used days to 0 and recalculating remaining days.<br>";
            } else {
                // If no brought forward, calculate normal remaining days
                $new_remaining = $quota - $used;
                $new_used = $used;  // Keep used as it is
                echo "No Brought Forward: Keeping used days as is.<br>";
            }
            
            // For new employees who joined during the current year, calculate pro-rata remaining days
            if ($daysWorked < 365) {
                // Proportional leave for new employees based on the number of days worked
                $proportionalLeave = $quota * ($daysWorked / 365);
                $new_remaining = $proportionalLeave - $used_from_current_quota;
                $new_remaining = floor($new_remaining);  // Ensure remaining is not negative
                echo "New Employee (Pro-rata): Remaining leave days adjusted for days worked.<br>";
            }
            
            echo "New Remaining: $new_remaining, New Used: $new_used<br>";
            
            // Update the current year's balance, resetting brought forward to 0 and setting used days accordingly
            $update_query = "UPDATE employee_balances
                             SET brought_forward = 0, used = :used, remaining = :remaining
                             WHERE employee_id = :employee_id AND year = :year";
            $stmt_update = $conn->prepare($update_query);
            $stmt_update->bindParam(':used', $new_used, PDO::PARAM_INT);
            $stmt_update->bindParam(':remaining', $new_remaining, PDO::PARAM_INT);
            $stmt_update->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
            $stmt_update->bindParam(':year', $current_year, PDO::PARAM_INT);
            $stmt_update->execute();
        }
        
        $conn->commit();
        echo "Brought forward days and used days reset for all employees.";
    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("Error resetting brought forward and used days: " . $e->getMessage());
        echo "Error resetting brought forward and used days.";
    }
} else {
    echo "It's not yet April 1st. The brought forward days will be reset later.";
}

$conn = null;
?>






































// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// $rootDir = __DIR__ . '../../../../';
// $conn = include($rootDir . 'config/connection.php');

// if (!$conn) {
//     error_log("Database connection failed.");
//     exit;
// }
// //  $current_year = date("Y");
// $current_year = 2025; // Change this to the year you want to test
// $next_year = 2026;    // Change this to the next year for testing
// // $next_year= $current_year +1;
// try {
//     $conn->beginTransaction();

//     // Fetch all employees' balances for the current year
//     $fetch_balances_query = "SELECT employee_id, employee, quota, used, brought_forward FROM employee_balances WHERE year = :year";
//     $stmt_fetch_balances = $conn->prepare($fetch_balances_query);
//     $stmt_fetch_balances->bindParam(':year', $current_year, PDO::PARAM_INT);
//     $stmt_fetch_balances->execute();
//     $balances = $stmt_fetch_balances->fetchAll(PDO::FETCH_ASSOC);

//     // Loop through each employee's balance to reset and recalculate
//     foreach ($balances as $balance) {
//         $employee_id = $balance['employee_id'];
//         $employee = $balance['employee'];
//         $quota = $balance['quota'];
//         $used = $balance['used'];
//         $brought_forward = $balance['brought_forward'];

//         // Debug output
//         echo "Processing Employee ID: $employee_id, Used: $used, Brought Forward: $brought_forward<br>";

//         // Calculate how much of the used days were taken from the brought forward days
//         if ($brought_forward > 0) {
//             if ($used <= $brought_forward) {
//                 $used_from_brought_forward = $used;
//                 $used_from_current_quota = 0;
//             } else {
//                 $used_from_brought_forward = $brought_forward;
//                 $used_from_current_quota = $used - $brought_forward;
//             }

//             // Reset `used` to 0 for next year and calculate remaining days
//             $new_remaining = $quota - $used_from_current_quota;
//             $new_used = 0; // Reset the used days

//             echo "Brought Forward > 0: Resetting used days to 0 and recalculating remaining days.<br>";
//         } else {
//             // If no brought forward, continue with normal calculation
//             $new_remaining = $quota - $used;
//             $new_used = $used; // Keep used as it is

//             echo "No Brought Forward: Keeping used days as is.<br>";
//         }

//         echo "New Remaining: $new_remaining, New Used: $new_used<br>";

//         // Update the current year's balance, resetting brought forward to 0 and setting used days accordingly
//         $update_query = "UPDATE employee_balances
//                          SET brought_forward = 0, used = :used, remaining = :remaining
//                          WHERE employee_id = :employee_id AND year = :year";
//         $stmt_update = $conn->prepare($update_query);
//         $stmt_update->bindParam(':used', $new_used, PDO::PARAM_INT);
//         $stmt_update->bindParam(':remaining', $new_remaining, PDO::PARAM_INT);
//         $stmt_update->bindParam(':employee_id', $employee_id, PDO::PARAM_INT);
//         $stmt_update->bindParam(':year', $current_year, PDO::PARAM_INT);
//         $stmt_update->execute();
//     }

//     $conn->commit();
//     echo "Brought forward days and used days reset.";
// } catch (PDOException $e) {
//     $conn->rollBack();
//     error_log("Error resetting brought forward and used days: " . $e->getMessage());
//     echo "Error resetting brought forward and used days.";
// }

// $conn = null;
?>
