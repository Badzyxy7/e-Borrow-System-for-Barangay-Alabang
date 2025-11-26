<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

function sendOTP($to_email, $to_name, $otp_code) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'havenblaze3000@gmail.com';
        $mail->Password = 'sqmzqlsftwxcmzfz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        $mail->setFrom('havenblaze3000@gmail.com', 'eBorrow System for Barangay Alabang');
        $mail->addAddress($to_email, $to_name);
        
        // **ADD YOUR LOGO - Change this path to your actual logo location**
        $logo_path = 'photos/logo.png'; // Example: 'assets/logo.png' or 'img/eborrow-logo.png'
        
        // Check if logo exists before attaching
        if (file_exists($logo_path)) {
            $mail->addEmbeddedImage($logo_path, 'logo_cid', 'logo.png');
        }
        
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Verification Code';
        $mail->Body = "
        <html>
        <body style='font-family: Arial, sans-serif; margin: 0; padding: 0;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5;'>
                <div style='background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);'>
                    
                    <!-- Logo Section -->
                    <div style='text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #e5e7eb;'>
                        <img src='cid:logo_cid' alt='eBorrow System Logo' style='max-width: 150px; height: auto;'>
                    </div>
                    
                    <h2 style='color: #1e3a8a; text-align: center; margin-bottom: 20px;'>Welcome to eBorrow System!</h2>
                    <p style='font-size: 16px; color: #333;'>Hi <strong>$to_name</strong>,</p>
                    <p style='font-size: 14px; color: #666;'>Your verification code is:</p>
                    
                    <!-- OTP Code Box -->
                    <div style='background: #1e3a8a; color: white; padding: 20px; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 8px; border-radius: 8px; margin: 20px 0;'>
                        $otp_code
                    </div>
                    
                    <p style='color: #666; font-size: 14px;'>This code will expire in <strong>2 minutes</strong>.</p>
                    <p style='color: #999; font-size: 12px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;'>
                        If you didn't request this, please ignore this email.
                    </p>
                    
                    <!-- Footer -->
                    <div style='text-align: center; margin-top: 20px; color: #999; font-size: 11px;'>
                        <p>© 2024 eBorrow System for Barangay Alabang. All rights reserved.</p>
                    </div>
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