<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'resident') {
    header("Location: login.php");
    exit();
}
include "db.php";
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = intval($_POST['request_id']);

    // verify request belongs to user and is approved/picked_up
    $stmt = $conn->prepare("SELECT * FROM borrow_requests WHERE id = ? AND user_id = ? AND status IN ('approved','picked_up')");
    $stmt->bind_param("ii", $request_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows == 0) {
        $_SESSION['flash_error'] = "Borrow record not found or cannot be returned.";
        header("Location: resident_dashboard.php?tab=borrowings");
        exit();
    }
    $req = $res->fetch_assoc();

    // mark the request status as 'returned' pending staff check OR create a log entry depending on your flow.
    // Here we'll change status to 'returned' and add a row to borrow_logs with expected_return_date.
    $conn->begin_transaction();
    try {
        // update request status to returned (staff will inspect and update logs)
        $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'returned' WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();

        // add a borrow_log entry (staff will update staff_checked_condition when they inspect)
        $stmt = $conn->prepare("INSERT INTO borrow_logs (request_id, user_id, equipment_id, qty, borrow_date, expected_return_date) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("iiiiss", $request_id, $user_id, $req['equipment_id'], $req['qty'], $req['borrow_date'], $req['return_date']);
        $stmt->execute();

        $conn->commit();
        $_SESSION['flash_success'] = "Return request submitted. Staff will inspect the item.";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['flash_error'] = "Error submitting return request: " . $e->getMessage();
    }

    header("Location: resident_dashboard.php?tab=borrowings");
    exit();
}

header("Location: resident_dashboard.php");
exit();
