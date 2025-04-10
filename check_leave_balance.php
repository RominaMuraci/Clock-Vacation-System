<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$rootDir = __DIR__ . '../../../../../';
$conn = include($rootDir . 'config/connection.php');

// Check if the connection was successful
if (!$conn) {
    echo "Database connection failed.";
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : '';
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action !== 'approve') {
    echo "Invalid request.";
    $conn = null;
    exit;
}

try {
    // Fetch the leave request details
    $query = "SELECT employee_id, employee, no_days, leave_type, medical_report_url FROM leave_requests WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $leaveRequest = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$leaveRequest) {
        error_log("No leave request found for ID: $id");
        echo "No such leave request.";
        $stmt->closeCursor();
        $conn = null;
        exit;
    }
    $stmt->closeCursor();

    // Determine the current year and the previous year
    // $current_year = 2025;
    $current_year = date("Y");
    $previous_year = $current_year - 1;

    // Fetch the current year's leave balance
    $balance_query = "SELECT quota, used, brought_forward, remaining FROM employee_balances WHERE employee_id = :employee_id AND year = :year";
    $stmt_balance = $conn->prepare($balance_query);
    $stmt_balance->bindParam(':employee_id', $leaveRequest['employee_id'], PDO::PARAM_INT);
    $stmt_balance->bindParam(':year', $current_year, PDO::PARAM_INT);
    $stmt_balance->execute();
    $balance = $stmt_balance->fetch(PDO::FETCH_ASSOC);
    $stmt_balance->closeCursor();

    if (!$balance) {
        // Check if there are any carried over days from the previous year
        $balance_query_previous = "SELECT quota, used, brought_forward, remaining FROM employee_balances WHERE employee_id = :employee_id AND year = :year";
        $stmt_balance_previous = $conn->prepare($balance_query_previous);
        $stmt_balance_previous->bindParam(':employee_id', $leaveRequest['employee_id'], PDO::PARAM_INT);
        $stmt_balance_previous->bindParam(':year', $previous_year, PDO::PARAM_INT);
        $stmt_balance_previous->execute();
        $balance_previous = $stmt_balance_previous->fetch(PDO::FETCH_ASSOC);
        $stmt_balance_previous->closeCursor();

        if ($balance_previous) {
            // Calculate carried forward days
            $carried_forward = $balance_previous['remaining'];
        } else {
            $carried_forward = 0;
        }

        // Set initial quota and create a new balance record
        $initial_quota = 20; // Set the initial quota as needed
        $remaining_balance = $initial_quota + $carried_forward;

        $insert_balance_query = "INSERT INTO employee_balances (employee_id, employee, year, quota, used, brought_forward, remaining) VALUES (:employee_id, :employee, :year, :quota, 0, :carried_forward, :remaining)";
        $stmt_insert = $conn->prepare($insert_balance_query);
        $stmt_insert->bindParam(':employee_id', $leaveRequest['employee_id'], PDO::PARAM_INT);
        $stmt_insert->bindParam(':employee', $leaveRequest['employee'], PDO::PARAM_STR);
        $stmt_insert->bindParam(':year', $current_year, PDO::PARAM_INT);
        $stmt_insert->bindParam(':quota', $initial_quota, PDO::PARAM_INT);
        $stmt_insert->bindParam(':carried_forward', $carried_forward, PDO::PARAM_INT);
        $stmt_insert->bindParam(':remaining', $remaining_balance, PDO::PARAM_INT);
        if (!$stmt_insert->execute()) {
            error_log("Failed to insert leave balance: " . implode(" ", $stmt_insert->errorInfo()));
            echo "Failed to insert leave balance.";
            $stmt_insert->closeCursor();
            $conn = null;
            exit;
        }
        $stmt_insert->closeCursor();
        $balance = [
            'quota' => $initial_quota,
            'used' => 0,
            'brought_forward' => $carried_forward,
            'remaining' => $remaining_balance
        ];
    }

    // Check if the leave request is within the allowed period for using carried forward days
    $current_month = date("m");
    $deduct_days = false;

    if ($current_month <= 3) {
        // Allow deduction of carried forward days if within January to March
        $deduct_days = true;
    } else {
        // Only deduct from current year's quota if after March
        if (strtolower($leaveRequest['leave_type']) === 'paid off' || 
            strtolower($leaveRequest['leave_type']) === 'sick leaves' && empty($leaveRequest['medical_report_url']) || 
            strtolower($leaveRequest['leave_type']) === 'others') {
            $deduct_days = true;
        }
    }

    // If days should be deducted, update the remaining balance using no_days
    if ($deduct_days) {
        $updated_remaining_balance = $balance['remaining'] - $leaveRequest['no_days'];

        // Debugging output
        error_log("Updated Remaining Balance Calculation - No Days: " . $leaveRequest['no_days'] . ", Remaining Balance Before Request: " . $balance['remaining'] . ", Updated Remaining Balance: " . $updated_remaining_balance);

        if ($updated_remaining_balance < 0) {
            echo "Insufficient leave balance.";
            $conn = null;
            exit;
        }

        $update_balance_query = "UPDATE employee_balances SET used = used + :no_days, remaining = remaining - :no_days WHERE employee_id = :employee_id AND year = YEAR(CURDATE())";
        $stmt_update = $conn->prepare($update_balance_query);
        $stmt_update->bindParam(':no_days', $leaveRequest['no_days'], PDO::PARAM_INT);
        $stmt_update->bindParam(':employee_id', $leaveRequest['employee_id'], PDO::PARAM_INT);
        if ($stmt_update->execute()) {
            echo "success";
        } else {
            error_log("Failed to update leave balance: " . implode(" ", $stmt_update->errorInfo()));
            echo "Failed to update leave balance.";
        }
        $stmt_update->closeCursor();
    } else {
        echo "success";
    }

    // After March, reset the carried forward days to zero
    if ($current_month > 3) {
        $reset_brought_forward_query = "UPDATE employee_balances SET brought_forward = 0 WHERE employee_id = :employee_id AND year = :year";
        $stmt_reset = $conn->prepare($reset_brought_forward_query);
        $stmt_reset->bindParam(':employee_id', $leaveRequest['employee_id'], PDO::PARAM_INT);
        $stmt_reset->bindParam(':year', $current_year, PDO::PARAM_INT);
        $stmt_reset->execute();
        $stmt_reset->closeCursor();
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo "Database error.";
}

$conn = null;
?>
