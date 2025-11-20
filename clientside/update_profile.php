<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];
$fields = [];
$params = [];
$types = "";

// Name
if (isset($_POST['name']) && trim($_POST['name']) !== '') {
    $fields[] = "name=?";
    $params[] = trim($_POST['name']);
    $types .= "s";
}

// Email (optional)
if (isset($_POST['email']) && trim($_POST['email']) !== '') {
    $email = trim($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit();
    }
    
    // Check if email is already taken by another user
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check_stmt->bind_param("si", $email, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email is already in use']);
        exit();
    }
    $check_stmt->close();
    
    $fields[] = "email=?";
    $params[] = $email;
    $types .= "s";
}

// Barangay
if (isset($_POST['barangay']) && trim($_POST['barangay']) !== '') {
    $fields[] = "barangay=?";
    $params[] = trim($_POST['barangay']);
    $types .= "s";
}

// Street
if (isset($_POST['street'])) {
    $fields[] = "street=?";
    $params[] = trim($_POST['street']);
    $types .= "s";
}

// Landmark
if (isset($_POST['landmark'])) {
    $fields[] = "landmark=?";
    $params[] = trim($_POST['landmark']);
    $types .= "s";
}

// Password (optional)
if (!empty($_POST['password'])) {
    if ($_POST['password'] !== ($_POST['confirm_password'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        exit();
    }
    
    if (strlen($_POST['password']) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
        exit();
    }
    
    $fields[] = "password=?";
    $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $types .= "s";
}

// Check if there is anything to update
if (empty($fields)) {
    echo json_encode(['success' => false, 'message' => 'No fields to update']);
    exit();
}

// Build dynamic query
$sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id=?";
$params[] = $user_id;
$types .= "i";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    // Update session values only for fields that were updated
    if (isset($_POST['name']) && trim($_POST['name']) !== '') {
        $_SESSION['name'] = trim($_POST['name']);
    }
    if (isset($_POST['email']) && trim($_POST['email']) !== '') {
        $_SESSION['email'] = trim($_POST['email']);
    }
    if (isset($_POST['barangay']) && trim($_POST['barangay']) !== '') {
        $_SESSION['barangay'] = trim($_POST['barangay']);
    }
    if (isset($_POST['street'])) {
        $_SESSION['street'] = trim($_POST['street']);
    }
    if (isset($_POST['landmark'])) {
        $_SESSION['landmark'] = trim($_POST['landmark']);
    }

    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
}

$stmt->close();
$conn->close();
?>