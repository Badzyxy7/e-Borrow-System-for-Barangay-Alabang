<?php
session_start();
include "../db.php";

// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['request_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Request ID required']);
    exit();
}

$request_id = intval($_GET['request_id']);

// Get return details from borrow_logs
$query = "SELECT * FROM borrow_logs WHERE request_id = $request_id";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
    
    // Format dates
    if ($data['actual_pickup_date']) {
        $data['actual_pickup_date'] = date('M d, Y h:i A', strtotime($data['actual_pickup_date']));
    }
    if ($data['actual_return_date']) {
        $data['actual_return_date'] = date('M d, Y h:i A', strtotime($data['actual_return_date']));
    }
    
    // Return JSON
    header('Content-Type: application/json');
    echo json_encode($data);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Return details not found']);
}
?>