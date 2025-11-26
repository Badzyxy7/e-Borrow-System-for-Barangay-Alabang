<?php
// ============================================
// DEATH CERTIFICATE SECURE UPLOAD HANDLER
// ============================================

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../db.php";

// Set JSON header
header('Content-Type: application/json');

// ============================================
// AUTHENTICATION CHECK
// ============================================
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'resident') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// ============================================
// CHECK IF FILE WAS UPLOADED
// ============================================
if (!isset($_FILES['death_certificate']) || $_FILES['death_certificate']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit();
}

$file = $_FILES['death_certificate'];

// ============================================
// FILE UPLOAD ERROR HANDLING
// ============================================
if ($file['error'] !== UPLOAD_ERR_OK) {
    $error_messages = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive in php.ini',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
    ];
    
    $message = $error_messages[$file['error']] ?? 'Unknown upload error';
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

// ============================================
// SECURITY VALIDATIONS
// ============================================

// 1. Validate file size (20MB max)
$max_size = 20 * 1024 * 1024; // 20MB in bytes
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File size exceeds 20MB limit']);
    exit();
}

// 2. Validate MIME type
$allowed_mime_types = ['image/jpeg', 'image/jpg', 'image/png'];
$finfo = new finfo(FILEINFO_MIME_TYPE);
$detected_mime = $finfo->file($file['tmp_name']);

if (!in_array($detected_mime, $allowed_mime_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, and PNG are allowed']);
    exit();
}

// 3. Validate file extension
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed_extensions = ['jpg', 'jpeg', 'png'];

if (!in_array($file_extension, $allowed_extensions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file extension']);
    exit();
}

// 4. Additional image validation using getimagesize
$image_info = @getimagesize($file['tmp_name']);
if ($image_info === false) {
    echo json_encode(['success' => false, 'message' => 'File is not a valid image']);
    exit();
}

// 5. Verify image type matches allowed types
$allowed_image_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG];
if (!in_array($image_info[2], $allowed_image_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid image format']);
    exit();
}

// ============================================
// MALWARE PREVENTION
// ============================================

// Read first few bytes to check for PHP code injection
$file_content = file_get_contents($file['tmp_name'], false, null, 0, 1024);

// Check for PHP tags
if (preg_match('/<\?php|<\?=|<script/i', $file_content)) {
    echo json_encode(['success' => false, 'message' => 'File contains suspicious code']);
    exit();
}

// Check for null bytes (directory traversal attempt)
if (strpos($file['name'], "\0") !== false) {
    echo json_encode(['success' => false, 'message' => 'Invalid filename']);
    exit();
}

// ============================================
// CREATE UPLOAD DIRECTORY IF NOT EXISTS
// ============================================
$upload_dir = '../death_certificates/';

if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
        exit();
    }
}

// Create .htaccess to prevent direct access
$htaccess_file = $upload_dir . '.htaccess';
if (!file_exists($htaccess_file)) {
    $htaccess_content = "# Prevent direct access to death certificates\n";
    $htaccess_content .= "Options -Indexes\n";
    $htaccess_content .= "deny from all\n";
    file_put_contents($htaccess_file, $htaccess_content);
}

// ============================================
// GENERATE SECURE FILENAME
// ============================================
$user_id = $_SESSION['user_id'];
$timestamp = time();
$random_string = bin2hex(random_bytes(8));
$safe_filename = "death_cert_{$user_id}_{$timestamp}_{$random_string}.{$file_extension}";

// Full path
$destination = $upload_dir . $safe_filename;

// ============================================
// MOVE UPLOADED FILE
// ============================================
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save file']);
    exit();
}

// ============================================
// SET SECURE FILE PERMISSIONS
// ============================================
chmod($destination, 0644);

// ============================================
// SUCCESS RESPONSE
// ============================================
echo json_encode([
    'success' => true,
    'message' => 'File uploaded successfully',
    'filename' => $safe_filename
]);
exit();
?>