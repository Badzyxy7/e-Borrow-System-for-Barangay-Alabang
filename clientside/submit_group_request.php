<?php
// ============================================
// SUBMIT GROUP REQUEST - BACKEND HANDLER
// File: submit_group_request.php
// ============================================

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../db.php";

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'resident') {
    header("Location: ../login.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if group request data is received
if (!isset($_POST['group_request_data'])) {
    $_SESSION['error_msg'] = "No group request data received.";
    header("Location: browseequipment.php");
    exit();
}

// Decode the JSON data
$group_data = json_decode($_POST['group_request_data'], true);

if (!$group_data) {
    $_SESSION['error_msg'] = "Invalid group request data.";
    header("Location: browseequipment.php");
    exit();
}

// Extract data
$items = $group_data['items'];
$borrow_date = $conn->real_escape_string($group_data['borrow_date']);
$return_date = $conn->real_escape_string($group_data['return_date']);
$purpose = $conn->real_escape_string($group_data['purpose']);
$description = $conn->real_escape_string($group_data['description']);

// Validation
if (empty($items) || empty($borrow_date) || empty($return_date) || empty($purpose)) {
    $_SESSION['error_msg'] = "All required fields must be filled.";
    header("Location: browseequipment.php");
    exit();
}

// Validate dates
$borrow_datetime = new DateTime($borrow_date);
$return_datetime = new DateTime($return_date);
$now = new DateTime();

if ($borrow_datetime < $now) {
    $_SESSION['error_msg'] = "Borrow date cannot be in the past.";
    header("Location: browseequipment.php");
    exit();
}

if ($return_datetime <= $borrow_datetime) {
    $_SESSION['error_msg'] = "Return date must be after borrow date.";
    header("Location: browseequipment.php");
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Create a group request ID (using timestamp + user_id for uniqueness)
    $group_request_id = time() . '_' . $user_id;
    
    // Validate availability and insert each item
    foreach ($items as $item) {
        $equipment_id = intval($item['id']);
        $qty = intval($item['quantity']);
        
        // Get equipment details
        $eq_result = $conn->query("SELECT quantity, name FROM equipment WHERE id = $equipment_id");
        if (!$eq_result || $eq_result->num_rows === 0) {
            throw new Exception("Equipment not found: " . $item['name']);
        }
        
        $equipment = $eq_result->fetch_assoc();
        $total_qty = $equipment['quantity'];
        $equipment_name = $equipment['name'];
        
        // Check availability
        $check_sql = "
            SELECT COALESCE(SUM(qty), 0) as total_borrowed
            FROM borrow_requests
            WHERE equipment_id = $equipment_id
              AND status IN ('pending', 'approved', 'picked_up')
              AND borrow_date <= '$return_date'
              AND return_date >= '$borrow_date'
        ";
        $check_result = $conn->query($check_sql);
        $row = $check_result->fetch_assoc();
        $total_borrowed = $row['total_borrowed'];
        
        $available_qty = $total_qty - $total_borrowed;
        
        if ($available_qty < $qty) {
            throw new Exception("Insufficient availability for '$equipment_name'. Available: $available_qty, Requested: $qty");
        }
        
        // Insert the request with group_request_id
        $insert_sql = "INSERT INTO borrow_requests 
            (user_id, equipment_id, qty, borrow_date, return_date, description, purpose, 
             group_request_id, status, created_at)
            VALUES 
            ($user_id, $equipment_id, $qty, '$borrow_date', '$return_date', 
             '$description', '$purpose', '$group_request_id', 'pending', NOW())";
        
        if (!$conn->query($insert_sql)) {
            throw new Exception("Failed to insert request for '$equipment_name': " . $conn->error);
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Success message
    $_SESSION['success_msg'] = "Group request submitted successfully! Your request includes " . count($items) . " item(s).";
    header("Location: browseequipment.php");
    exit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    $_SESSION['error_msg'] = $e->getMessage();
    header("Location: browseequipment.php");
    exit();
}
?>