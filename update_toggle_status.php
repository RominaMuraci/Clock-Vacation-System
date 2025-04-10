<?php
$rootDir = __DIR__ . '../../../../';
$conn = include($rootDir . 'config/connection.php'); // Ensure this returns a PDO instance

if (!$conn) {
    die("Database connection failed.");
}

// Get the toggle status from the POST request
$data = json_decode(file_get_contents('php://input'), true);
$yearFlag = $data['year_flag'];

try {
    $stmt = $conn->prepare("UPDATE holiday_toggle SET year_flag = :year_flag");
    $stmt->bindParam(':year_flag', $yearFlag, PDO::PARAM_INT);
    $stmt->execute();
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Close the connection
$conn = null;
?>
