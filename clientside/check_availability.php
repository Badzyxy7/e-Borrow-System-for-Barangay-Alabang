<?php
include "../db.php";

$equipment_id = intval($_GET['equipment_id']);
$borrow_date = $_GET['borrow'];
$return_date = $_GET['return'];
$requested_qty = intval($_GET['qty']);

// Get total quantity
$eq = $conn->query("SELECT quantity FROM equipment WHERE id = $equipment_id")->fetch_assoc();
$total = $eq['quantity'];

// Get borrowed count for date range
$check = $conn->query("
    SELECT COALESCE(SUM(qty), 0) as borrowed
    FROM borrow_requests
    WHERE equipment_id = $equipment_id
      AND status IN ('pending', 'approved', 'picked_up')
      AND borrow_date <= '$return_date'
      AND return_date >= '$borrow_date'
")->fetch_assoc();

$available = $total - $check['borrowed'];

echo json_encode([
    'available' => ($available >= $requested_qty),
    'available_qty' => $available,
    'requested_qty' => $requested_qty
]);
?>