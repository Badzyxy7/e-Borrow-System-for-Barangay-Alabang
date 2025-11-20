<?php
session_start();
include "../db.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== 0) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit();
}

$file = $_FILES['avatar'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

// Only allow image types
if (!in_array($ext, ['jpg','jpeg','png','gif'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
    exit();
}

// Create a unique file name
$filename = uniqid() . '.' . $ext;
$destination = "../photos/avatars/" . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $destination)) {
    // Update avatar in database
    $conn->query("UPDATE users SET avatar='$filename' WHERE id=$user_id");
    $_SESSION['avatar'] = $filename;

    echo json_encode([
        'success' => true,
        'message' => 'Avatar updated successfully',
        'avatar_url' => "../photos/avatars/$filename"
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to upload avatar']);
}
?>
