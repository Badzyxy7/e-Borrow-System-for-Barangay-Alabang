<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once "users_functions.php";

// Check admin access
checkAdminAccess();

// Handle AJAX requests
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_user':
            $result = addUser($conn, $_POST);
            echo json_encode($result);
            break;
            
        case 'verify_otp':
            $email = $_POST['email'] ?? '';
            $otp_code = $_POST['otp_code'] ?? '';
            $result = verifyOTP($conn, $email, $otp_code);
            echo json_encode($result);
            break;
            
        case 'resend_otp':
            $email = $_POST['email'] ?? '';
            $result = resendOTP($conn, $email);
            echo json_encode($result);
            break;
            
        case 'get_user_details':
            $user_id = intval($_POST['user_id'] ?? 0);
            $user = getUserDetails($conn, $user_id);
            
            if ($user) {
                echo json_encode(['success' => true, 'user' => $user]);
            } else {
                echo json_encode(['success' => false, 'message' => 'User not found']);
            }
            break;
            
        case 'search_users':
            $search = $_POST['search'] ?? '';
            $role_filter = $_POST['role_filter'] ?? '';
            
            $users_result = searchUsers($conn, $search, $role_filter);
            $users = [];
            
            while ($user = $users_result->fetch_assoc()) {
                $users[] = $user;
            }
            
            echo json_encode(['success' => true, 'users' => $users]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>