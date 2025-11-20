<?php
session_start();
include "db.php";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$otp = trim($_POST['otp']);
$email = $_SESSION['register_email'] ?? '';

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please register again.']);
    exit;
}

// Get user from database
$stmt = $conn->prepare("SELECT otp_code, otp_expiry, is_verified FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit;
}

// Check if already verified
if ($user['is_verified'] == 1) {
    echo json_encode(['success' => false, 'message' => 'Email already verified.']);
    exit;
}

// Check if OTP expired
if (strtotime($user['otp_expiry']) < time()) {
    echo json_encode(['success' => false, 'message' => 'Code expired. Please request a new one.']);
    exit;
}

// Verify OTP
if ($otp === $user['otp_code']) {
    // Update user as verified
    $stmt = $conn->prepare("UPDATE users SET is_verified = 1, otp_code = NULL, otp_expiry = NULL WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    unset($_SESSION['register_email']);
    echo json_encode(['success' => true, 'message' => 'Email verified successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid code. Please try again.']);
}
?>