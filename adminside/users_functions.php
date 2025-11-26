<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database and email configuration
require_once "../db.php";
require_once "../email_config.php";

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin
function checkAdminAccess() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
        header("Location: ../login.php");
        exit();
    }
}

// Generate 6-digit OTP
function generateOTP() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Add new user with OTP verification
function addUser($conn, $data) {
    $response = ['success' => false, 'message' => '', 'email' => ''];
    
    // Sanitize and prepare data
    $name = trim($data['name']);
    $email = trim(strtolower($data['email']));
    $password = $data['password'];
    $role = $data['role'];
    
    // Optional fields
    $phone_number = !empty($data['phone_number']) ? trim($data['phone_number']) : null;
    $birthdate = !empty($data['birthdate']) ? $data['birthdate'] : null;
    $barangay = !empty($data['barangay']) ? trim($data['barangay']) : null;
    $street = !empty($data['street']) ? trim($data['street']) : null;
    $landmark = !empty($data['landmark']) ? trim($data['landmark']) : null;
    
    // Check if email already exists
    $stmt_check = $conn->prepare("SELECT id FROM users WHERE LOWER(email) = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        $response['message'] = 'Email already exists in the system.';
        $stmt_check->close();
        return $response;
    }
    $stmt_check->close();
    
    // Generate OTP
    $otp_code = generateOTP();
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+2 minutes'));
    
    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user with OTP
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone_number, birthdate, barangay, street, landmark, otp_code, otp_expiry, is_verified, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 'active', NOW())");
    
    $stmt->bind_param("sssssssssss", $name, $email, $password_hash, $role, $phone_number, $birthdate, $barangay, $street, $landmark, $otp_code, $otp_expiry);
    
    if ($stmt->execute()) {
        // Send OTP email
        if (sendOTP($email, $name, $otp_code)) {
            $response['success'] = true;
            $response['message'] = 'User created successfully. OTP sent to email.';
            $response['email'] = $email;
        } else {
            $response['success'] = true;
            $response['message'] = 'User created but failed to send OTP email. Please resend OTP.';
            $response['email'] = $email;
        }
    } else {
        $response['message'] = 'Error creating user: ' . $conn->error;
    }
    
    $stmt->close();
    return $response;
}

// Verify OTP
function verifyOTP($conn, $email, $otp_code) {
    $response = ['success' => false, 'message' => ''];
    
    $email = trim(strtolower($email));
    $otp_code = trim($otp_code);
    
    // Get user with OTP
    $stmt = $conn->prepare("SELECT id, otp_code, otp_expiry, is_verified FROM users WHERE LOWER(email) = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $response['message'] = 'User not found.';
        $stmt->close();
        return $response;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Check if already verified
    if ($user['is_verified'] == 1) {
        $response['message'] = 'User already verified.';
        return $response;
    }
    
    // Check if OTP expired
    if (strtotime($user['otp_expiry']) < time()) {
        $response['message'] = 'OTP has expired. Please request a new one.';
        return $response;
    }
    
    // Verify OTP
    if ($user['otp_code'] === $otp_code) {
        // Update user as verified
        $stmt_update = $conn->prepare("UPDATE users SET is_verified = 1, otp_code = NULL, otp_expiry = NULL WHERE id = ?");
        $stmt_update->bind_param("i", $user['id']);
        
        if ($stmt_update->execute()) {
            $response['success'] = true;
            $response['message'] = 'User verified successfully!';
        } else {
            $response['message'] = 'Error verifying user.';
        }
        $stmt_update->close();
    } else {
        $response['message'] = 'Invalid OTP code.';
    }
    
    return $response;
}

// Resend OTP
function resendOTP($conn, $email) {
    $response = ['success' => false, 'message' => ''];
    
    $email = trim(strtolower($email));
    
    // Get user
    $stmt = $conn->prepare("SELECT id, name, is_verified FROM users WHERE LOWER(email) = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $response['message'] = 'User not found.';
        $stmt->close();
        return $response;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if ($user['is_verified'] == 1) {
        $response['message'] = 'User already verified.';
        return $response;
    }
    
    // Generate new OTP
    $otp_code = generateOTP();
    $otp_expiry = date('Y-m-d H:i:s', strtotime('+2 minutes'));
    
    // Update OTP
    $stmt_update = $conn->prepare("UPDATE users SET otp_code = ?, otp_expiry = ? WHERE id = ?");
    $stmt_update->bind_param("ssi", $otp_code, $otp_expiry, $user['id']);
    
    if ($stmt_update->execute()) {
        // Send OTP email
        if (sendOTP($email, $user['name'], $otp_code)) {
            $response['success'] = true;
            $response['message'] = 'New OTP sent to email.';
        } else {
            $response['message'] = 'Failed to send OTP email.';
        }
    } else {
        $response['message'] = 'Error updating OTP.';
    }
    
    $stmt_update->close();
    return $response;
}

// Get user details by ID
function getUserDetails($conn, $user_id) {
    $stmt = $conn->prepare("SELECT id, name, email, phone_number, birthdate, barangay, street, landmark, role, status, avatar, is_verified, created_at, last_login FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }
    
    $stmt->close();
    return null;
}

// Get user statistics
function getUserStatistics($conn) {
    $stats = [];
    
    $total_result = $conn->query("SELECT COUNT(*) as total FROM users");
    $stats['total'] = $total_result->fetch_assoc()['total'];
    
    $admin_result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $stats['admin'] = $admin_result->fetch_assoc()['count'];
    
    $staff_result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'staff'");
    $stats['staff'] = $staff_result->fetch_assoc()['count'];
    
    $resident_result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'resident'");
    $stats['resident'] = $resident_result->fetch_assoc()['count'];
    
    return $stats;
}

// Search and filter users
function searchUsers($conn, $search = '', $role_filter = '') {
    $search = $conn->real_escape_string(trim($search));
    $role_filter = $conn->real_escape_string($role_filter);
    
    $sql = "SELECT id, name, email, phone_number, role, status, avatar, is_verified, created_at FROM users WHERE 1=1";
    
    if ($search) {
        $sql .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR phone_number LIKE '%$search%' OR barangay LIKE '%$search%' OR street LIKE '%$search%')";
    }
    
    if ($role_filter) {
        $sql .= " AND role='$role_filter'";
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    return $conn->query($sql);
}
?>