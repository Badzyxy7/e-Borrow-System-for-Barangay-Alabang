<?php
// ============================================
// CHECK PROFILE COMPLETENESS API
// Returns JSON response indicating if user profile is complete
// ============================================

session_start();
include "../db.php";

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'complete' => false,
        'message' => 'User not authenticated'
    ]);
    exit();
}

$user_id = intval($_SESSION['user_id']);

// Fetch user profile data
$sql = "SELECT phone_number, birthdate, street, landmark FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'complete' => false,
        'message' => 'User not found'
    ]);
    exit();
}

$user = $result->fetch_assoc();

// Check if all required fields are filled
$missing_fields = [];

if (empty($user['phone_number']) || trim($user['phone_number']) === '') {
    $missing_fields[] = 'Phone Number';
}

if (empty($user['birthdate']) || $user['birthdate'] === '0000-00-00' || $user['birthdate'] === null) {
    $missing_fields[] = 'Birthdate';
}

if (empty($user['street']) || trim($user['street']) === '') {
    $missing_fields[] = 'Street';
}

if (empty($user['landmark']) || trim($user['landmark']) === '') {
    $missing_fields[] = 'Landmark';
}

// Determine if profile is complete
$is_complete = count($missing_fields) === 0;

echo json_encode([
    'success' => true,
    'complete' => $is_complete,
    'missing_fields' => $missing_fields,
    'message' => $is_complete 
        ? 'Profile is complete' 
        : 'Profile incomplete. Missing: ' . implode(', ', $missing_fields)
]);

$stmt->close();
$conn->close();
?>