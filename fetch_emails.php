<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$rootDir = __DIR__ . '../../../../';

// Include the PDO connection script
$conn = include($rootDir . 'config/connection.php'); // Ensure this returns a PDO instance

// Adjusted SQL query to use correct column names
$sql = "
    SELECT u.userid, u.firstname, u.lastname, u.email
    FROM users u
    INNER JOIN admin_permissions_approve apa ON u.userid = apa.admin_approve_id
    WHERE u.accesslevel IN ('admin', 'noc', 'other')
";

$users = [];
try {
    // Prepare and execute the SQL statement
    $stmt = $conn->query($sql);

    // Fetch all rows
    if ($stmt) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = [
                'fullname' => htmlspecialchars($row["firstname"] . " " . $row["lastname"]),
                'email' => htmlspecialchars($row["email"])
            ];
        }
    } else {
        $errorInfo = $conn->errorInfo();
        error_log("Query failed: " . $errorInfo[2]); // Updated to access the error message directly
        echo "Error: " . $errorInfo[2];
    }
} catch (PDOException $e) {
    error_log("Query failed: " . $e->getMessage());
    echo "Error: " . $e->getMessage();
}

// Return data as JSON
header('Content-Type: application/json');
echo json_encode($users);
?>
