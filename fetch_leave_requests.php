<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$rootDir = __DIR__ . '../../../../';
$conn = include($rootDir . 'config/connection.php');

try {
    session_start();
    $userIdDb = isset($_SESSION['userid']) ? $_SESSION['userid'] : '';
    
    // Retrieve query parameters
    $filter = isset($_GET['filter']) ? $_GET['filter'] : '';
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $employeeName = isset($_GET['employee_name']) ? $_GET['employee_name'] : '';
    
    // Check if the user has permissions
    $permissionQuery = "SELECT COUNT(*) FROM user_permissions_leave WHERE admin_id = :userid";
    $permissionStmt = $conn->prepare($permissionQuery);
    $permissionStmt->bindValue(':userid', $userIdDb, PDO::PARAM_INT);
    $permissionStmt->execute();
    $hasPermission = $permissionStmt->fetchColumn() > 0;
    
    $approvalQuery = "SELECT COUNT(*) FROM admin_permissions_approve WHERE admin_approve_id = :userid";
    $approvalStmt = $conn->prepare($approvalQuery);
    $approvalStmt->bindValue(':userid', $userIdDb, PDO::PARAM_INT);
    $approvalStmt->execute();
    $hasApprovalPermission = $approvalStmt->fetchColumn() > 0;
    
    // Base SQL query
    $sql = "SELECT id, employee_id, employee, leave_type, start_date, end_date,start_time, end_time, send_to, reason, created_at, status, no_days, requested_by, medical_report, medical_report_url
            FROM leave_requests
            WHERE 1=1";
     
    // Add filter by employee name if provided
    if (!empty($employeeName)) {
        $sql .= " AND employee = :employee_name";
    }
    
    // Add filter by leave type if provided
    if (!empty($filter)) {
        $sql .= " AND leave_type = :filter";
    }
    
    // Add search parameter if provided
    if (!empty($search)) {
        $sql .= " AND (employee LIKE :search OR reason LIKE :search)";
    }
    
    // If the user does not have permissions, filter by their own employee_id
    if (!$hasPermission && !$hasApprovalPermission) {
        $sql .= " AND employee_id = :employee_id";
    }

//    // Order by creation date in descending order
//    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters if they are set
    if (!empty($employeeName)) {
        $stmt->bindParam(':employee_name', $employeeName, PDO::PARAM_STR);
    }
    
    if (!empty($filter)) {
        $stmt->bindParam(':filter', $filter, PDO::PARAM_STR);
    }
    
    if (!empty($search)) {
        $searchParam = "%$search%";
        $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
    }
    
    if (!$hasPermission && !$hasApprovalPermission) {
        $stmt->bindValue(':employee_id', $userIdDb, PDO::PARAM_INT);
    }
    
    $stmt->execute();
    
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check if any requests were found
    if (empty($requests)) {
        $requests = ['error' => 'No leave requests found.'];
    }
    
    $conn = null;
    
} catch (PDOException $e) {
    $requests = ['error' => 'Database query failed: ' . $e->getMessage()];
}

header('Content-Type: application/json');
echo json_encode($requests);
?>