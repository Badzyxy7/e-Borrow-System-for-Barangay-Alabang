<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/../db.php";

// PHPMailer files (same folder structure as your OTP file)
require __DIR__ . "/../email/src/Exception.php";
require __DIR__ . "/../email/src/PHPMailer.php";
require __DIR__ . "/../email/src/SMTP.php";

/*
|--------------------------------------------------------------------------
|  Send Reminder Email (Uses SAME Gmail SMTP as your OTP system)
|--------------------------------------------------------------------------
*/
function sendReturnReminder($to_email, $to_name, $subject, $htmlBody) {
    $mail = new PHPMailer(true);

    try {
        // SAME CONFIG AS sendOTP()
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'havenblaze3000@gmail.com';
        $mail->Password = 'sqmzqlsftwxcmzfz'; // Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('havenblaze3000@gmail.com', 'E-Borrow System');
        $mail->addAddress($to_email, $to_name);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;

        return $mail->send();

    } catch (Exception $e) {
        error_log("Reminder Email Failed: {$mail->ErrorInfo}");
        return false;
    }
}

/*
|--------------------------------------------------------------------------
|  Fetch Items Due Tomorrow
|--------------------------------------------------------------------------
*/
$tomorrow = date('Y-m-d', strtotime('+1 day'));

$sql = "SELECT br.*, e.name AS equipment_name, u.name AS user_name, u.email AS user_email
        FROM borrow_requests br
        JOIN equipment e ON br.equipment_id = e.id
        JOIN users u ON br.user_id = u.id
        WHERE br.status = 'delivered'
        AND DATE(br.return_date) = '$tomorrow'
        AND br.reminder_sent = 0";

$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "No reminders to send.\n";
    exit;
}

echo "Sending " . $result->num_rows . " reminder(s)...\n";

while ($row = $result->fetch_assoc()) {

    $htmlMessage = "
    <html>
    <body style='font-family: Arial;'>
        <h2>🔔 Return Reminder</h2>

        <p>Hello <strong>{$row['user_name']}</strong>,</p>
        <p>This is a reminder that your borrowed equipment is due for return <strong>tomorrow</strong>.</p>

        <div style='border-left: 4px solid #1e3a8a; padding: 10px; background: #f4f4f4; margin-top: 10px;'>
            <p><strong>Equipment:</strong> {$row['equipment_name']}</p>
            <p><strong>Quantity:</strong> {$row['qty']}</p>
            <p><strong>Return Date:</strong> " . date('F d, Y', strtotime($row['return_date'])) . "</p>
        </div>

        <p>Please prepare the item for return. Thank you!</p>
    </body>
    </html>";

    $subject = "Return Reminder: {$row['equipment_name']} due tomorrow";

    if (sendReturnReminder($row['user_email'], $row['user_name'], $subject, $htmlMessage)) {
        $conn->query("UPDATE borrow_requests SET reminder_sent = 1 WHERE id = {$row['id']}");
        echo "✓ Sent to {$row['user_email']}\n";
    } else {
        echo "✗ Failed to send to {$row['user_email']}\n";
    }
}

$conn->close();
?>
