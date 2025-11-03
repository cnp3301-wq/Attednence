<?php
/**
 * PHPMailer Email Sending Function
 * This file uses PHPMailer library for sending OTP emails
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/email_config.php';

/**
 * Send OTP email using PHPMailer
 * @param string $email Recipient email address
 * @param string $otp The OTP code to send
 * @return bool True if email sent successfully, false otherwise
 */
function sendOTPEmailWithPHPMailer($email, $otp) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        
        // Sender and recipient
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for KPRCAS Attendance System';
        $mail->Body    = getOTPEmailTemplate($otp);
        $mail->AltBody = "Your OTP is: $otp. This OTP will expire in " . OTP_EXPIRY_MINUTES . " minutes.";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Get HTML email template for OTP
 * @param string $otp The OTP code
 * @return string HTML email template
 */
function getOTPEmailTemplate($otp) {
    $template = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body {
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
            }
            .email-container {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
            }
            .header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 30px;
                text-align: center;
            }
            .header h1 {
                margin: 0;
                font-size: 28px;
            }
            .content {
                padding: 40px 30px;
            }
            .otp-box {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                font-size: 36px;
                font-weight: bold;
                text-align: center;
                padding: 25px;
                margin: 30px 0;
                border-radius: 10px;
                letter-spacing: 8px;
            }
            .info-box {
                background-color: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 15px;
                margin: 20px 0;
                border-radius: 5px;
            }
            .info-box strong {
                color: #856404;
            }
            .footer {
                background-color: #f8f9fa;
                padding: 20px;
                text-align: center;
                color: #666;
                font-size: 12px;
                border-top: 1px solid #e9ecef;
            }
            .btn {
                display: inline-block;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 12px 30px;
                text-decoration: none;
                border-radius: 5px;
                margin: 20px 0;
            }
            ul {
                padding-left: 20px;
            }
            ul li {
                margin: 10px 0;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="header">
                <h1>🎓 KPRCAS Attendance System</h1>
            </div>
            
            <div class="content">
                <h2 style="color: #333;">Your Login OTP</h2>
                <p>Hello,</p>
                <p>You have requested to login to the KPRCAS Attendance System. Please use the following One-Time Password (OTP) to complete your authentication:</p>
                
                <div class="otp-box">' . $otp . '</div>
                
                <div class="info-box">
                    <strong>⏱️ Important:</strong> This OTP will expire in <strong>' . OTP_EXPIRY_MINUTES . ' minutes</strong>.
                </div>
                
                <h3 style="color: #333;">Security Tips:</h3>
                <ul>
                    <li>Never share your OTP with anyone</li>
                    <li>KPRCAS staff will never ask for your OTP</li>
                    <li>If you did not request this OTP, please ignore this email</li>
                    <li>Contact your administrator if you suspect unauthorized access</li>
                </ul>
                
                <p>If you have any questions or need assistance, please contact the system administrator.</p>
                
                <p style="margin-top: 30px;">
                    Best regards,<br>
                    <strong>KPRCAS Attendance System</strong>
                </p>
            </div>
            
            <div class="footer">
                <p><strong>KPRCAS - KPR College of Arts Science and Research</strong></p>
                <p>This is an automated email. Please do not reply to this message.</p>
                <p>&copy; 2025 KPRCAS. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    return $template;
}

/**
 * Send welcome email to new user
 * @param string $email Recipient email
 * @param string $name User name
 * @param string $userType User type (admin/teacher/student)
 * @return bool
 */
function sendWelcomeEmail($email, $name, $userType) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email, $name);
        
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to KPRCAS Attendance System';
        $mail->Body    = "
        <h2>Welcome to KPRCAS Attendance System</h2>
        <p>Dear $name,</p>
        <p>Your account has been created successfully as a <strong>$userType</strong>.</p>
        <p>You can now login to the system using your credentials.</p>
        <p>Best regards,<br>KPRCAS Team</p>
        ";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Welcome email failed: {$mail->ErrorInfo}");
        return false;
    }
}
?>
