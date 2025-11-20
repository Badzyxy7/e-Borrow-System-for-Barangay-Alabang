<?php
session_start();
include "db.php";
require_once 'email_config.php';

header('Content-Type: application/json');

$email = $_SESSION['register_email'] ?? '';

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Session expired.']);
    exit;
}

// Generate new OTP
$otp_code = sprintf("%06d", mt_rand(1, 999999));
$otp_expiry = date('Y-m-d H:i:s', strtotime('+2 minutes'));

// Update database with prepared statement (security fix)
$stmt = $conn->prepare("UPDATE users SET otp_code = ?, otp_expiry = ? WHERE email = ?");
$stmt->bind_param("sss", $otp_code, $otp_expiry, $email);

if ($stmt->execute()) {
    // Get user name with prepared statement (security fix)
    $stmt_name = $conn->prepare("SELECT name FROM users WHERE email = ?");
    $stmt_name->bind_param("s", $email);
    $stmt_name->execute();
    $result = $stmt_name->get_result();
    $user = $result->fetch_assoc();
    
    // Send email
    if (sendOTP($email, $user['name'], $otp_code)) {
        echo json_encode(['success' => true, 'message' => 'New code sent!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send email.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
?>