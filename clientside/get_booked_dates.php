<?php
include "../db.php";

$equipment_id = intval($_GET['equipment_id']);

// Get all date ranges where this equipment is booked
$sql = "
    SELECT borrow_date as `from`, return_date as `to`
    FROM borrow_requests
    WHERE equipment_id = $equipment_id
      AND status IN ('pending', 'approved', 'picked_up')
    ORDER BY borrow_date
";

$result = $conn->query($sql);
$booked_ranges = [];

while ($row = $result->fetch_assoc()) {
    $booked_ranges[] = [
        'from' => $row['from'],
        'to' => $row['to']
    ];
}

header('Content-Type: application/json');
echo json_encode($booked_ranges);
?>