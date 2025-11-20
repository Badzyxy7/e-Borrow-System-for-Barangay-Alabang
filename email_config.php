<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


// If manual download, use this instead:
    require 'src/Exception.php';
    require 'src/PHPMailer.php';
    require 'src/SMTP.php';

function sendOTP($to_email, $to_name, $otp_code) {
    // Configure with YOUR Gmail
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'havenblaze3000@gmail.com'; // ← CHANGE THIS
        $mail->Password = 'sqmzqlsftwxcmzfz';     // ← CHANGE THIS (see Step 4)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        $mail->setFrom('havenblaze3000@gmail.com', 'eBorrow System for Barangay Alabang yarn?');
        $mail->addAddress($to_email, $to_name);
        
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Verification Code';
        $mail->Body = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5;'>
                <div style='background: white; padding: 30px; border-radius: 10px;'>
                    <h2 style='color: #1e3a8a;'>Welcome to eBorrow System for Barangay Alabang!</h2>
                    <p>Hi <strong>$to_name</strong>,</p>
                    <p>Your verification code is:</p>
                    <div style='background: #1e3a8a; color: white; padding: 20px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 8px; border-radius: 8px; margin: 20px 0;'>
                        $otp_code
                    </div>
                    <p style='color: #666;'>This code will expire in <strong>2 minutes</strong>.</p>
                    <p style='color: #999; font-size: 12px; margin-top: 30px;'>If you didn't request this, please ignore this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>