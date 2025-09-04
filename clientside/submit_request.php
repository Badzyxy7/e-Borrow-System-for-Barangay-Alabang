<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'resident') {
    header("Location: login.php");
    exit();
}
include "db.php";
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipment_id = intval($_POST['equipment_id']);
    $qty = intval($_POST['qty']);
    $borrow_date = $_POST['borrow_date'];
    $return_date = $_POST['return_date'];

    // basic validation
    if ($qty <= 0 || $borrow_date == '' || $return_date == '') {
        $_SESSION['flash_error'] = "Please fill in all fields.";
        header("Location: resident_dashboard.php?tab=browse");
        exit();
    }

    // check equipment availability and quantity
    $stmt = $conn->prepare("SELECT * FROM equipment WHERE id = ?");
    $stmt->bind_param("i",$equipment_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows == 0) {
        $_SESSION['flash_error'] = "Equipment not found.";
        header("Location: resident_dashboard.php?tab=browse");
        exit();
    }
    $equip = $res->fetch_assoc();
    if ($equip['status'] != 'available' || $equip['quantity'] < $qty) {
        $_SESSION['flash_error'] = "Item not available in requested quantity.";
        header("Location: resident_dashboard.php?tab=browse");
        exit();
    }

    // insert request (status pending)
    $stmt = $conn->prepare("INSERT INTO borrow_requests (user_id, equipment_id, qty, borrow_date, return_date, status) VALUES (?,?,?,?,?, 'pending')");
    $stmt->bind_param("iiiis", $user_id, $equipment_id, $qty, $borrow_date, $return_date);
    if ($stmt->execute()) {
        $_SESSION['flash_success'] = "Request submitted successfully.";
    } else {
        $_SESSION['flash_error'] = "Error submitting request: " . $conn->error;
    }
    header("Location: resident_dashboard.php?tab=requests");
    exit();
}
header("Location: resident_dashboard.php");
exit();
