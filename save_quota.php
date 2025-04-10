<?php
session_start();

// Enable error reporting for debugging purposes
error_reporting(E_ALL);
ini_set('display_errors', 1);

$rootDir = __DIR__ . '../../../../';


// Include database connection
$conn = include($rootDir . 'config/connection.php');

// Check if the user is logged in
if (!isset($_SESSION['login_session']) || !isset($_SESSION['userid'])) {
    // Print error message if not logged in
    echo "Error: You are not logged in.";
    exit();
}

// Retrieve the user ID, current year, and admin status from the session
$userIdDb = $_SESSION['userid'];
$current_year = date("Y");
// $current_year = "2025";
$isAdmin = isset($_SESSION['isadmin']) ? $_SESSION['isadmin'] : false; // Assuming `is_admin` is set in session



// Check if quota is posted
if (isset($_POST['quota'])) {
    $quota = $_POST['quota'];

    // Validate quota input
    if (!is_numeric($quota) || $quota < 0) {
        // Print error message if the quota value is invalid
        header('Location: settings.php?error=invalid_quota');
        exit();
    }
    try {
        if ($isAdmin) {
            // Update the quota in quota_timeclock table
            $update_query = "INSERT INTO quota_timeclock (year, quota) 
                             VALUES (:year, :quota) 
                             ON DUPLICATE KEY UPDATE quota = VALUES(quota)";

            $stmt = $conn->prepare($update_query);
            $stmt->bindParam(':quota', $quota, PDO::PARAM_INT);
            $stmt->bindParam(':year', $current_year, PDO::PARAM_INT);

            if ($stmt->execute()) {
                // Update the quota in employee_balances table to match the updated quota
                $update_balance_query = "UPDATE employee_balances
                                         JOIN quota_timeclock ON employee_balances.year = quota_timeclock.year
                                         SET employee_balances.quota = quota_timeclock.quota
                                         WHERE employee_balances.year = :year";

                $stmt_update_balance = $conn->prepare($update_balance_query);
                $stmt_update_balance->bindParam(':year', $current_year, PDO::PARAM_INT);

                if ($stmt_update_balance->execute()) {
                    // Recalculate the remaining balance based on the updated quota
                    $recalculate_query = "UPDATE employee_balances
                                          JOIN quota_timeclock ON employee_balances.year = quota_timeclock.year
                                          SET employee_balances.remaining = quota_timeclock.quota - employee_balances.used
                                          WHERE employee_balances.year = :year";

                    $stmt_recalculate = $conn->prepare($recalculate_query);
                    $stmt_recalculate->bindParam(':year', $current_year, PDO::PARAM_INT);

                    if ($stmt_recalculate->execute()) {
                        header('Location: settings.php?status=quota_updated');
                    } else {
                        error_log("Failed to recalculate employee balances: " . implode(" ", $stmt_recalculate->errorInfo()));
                        header('Location: settings.php?error=recalculation_failed');
                    }
                } else {
                    error_log("Failed to update employee balances: " . implode(" ", $stmt_update_balance->errorInfo()));
                    header('Location: settings.php?error=update_employee_balances_failed');
                }
            } else {
                error_log("Failed to update quota_timeclock: " . implode(" ", $stmt->errorInfo()));
                header('Location: settings.php?error=quota_update_failed');
            }
            exit();
        } else {
            header('Location: settings.php?error=not_authorized');
            exit();
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        header('Location: settings.php?error=database_error');
        exit();
    }
} else {
    header('Location: settings.php?error=quota_not_set');
    exit();
}

$conn = null;
?>