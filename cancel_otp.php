<?php
session_start();
include "db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['register_email'])) {
    echo json_encode(['success' => false, 'message' => 'No active registration found']);
    exit;
}

$email = $_SESSION['register_email'];

// Delete unverified user from database
$sql = "DELETE FROM users WHERE email='$email' AND is_verified=0";

if ($conn->query($sql) === TRUE) {
    // Clear session variables
    unset($_SESSION['register_email']);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Registration cancelled successfully'
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Error cancelling registration: ' . $conn->error
    ]);
}

$conn->close();
?>