<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/email_config.php';

/**
 * Validate KPRCAS email address
 * @param string $email
 * @return bool
 */
function validateKprcasEmail($email) {
    return preg_match('/@kprcas\.ac\.in$/', $email);
}

/**
 * Validate student email address
 * @param string $email
 * @return bool
 */
function validateStudentEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Authenticate user (admin/teacher) with email and password
 * @param string $email
 * @param string $password
 * @param string $user_type
 * @return array|false
 */
function authenticateUser($email, $password, $user_type) {
    global $conn;
    
    $email = $conn->real_escape_string($email);
    $user_type = $conn->real_escape_string($user_type);
    
    $sql = "SELECT id, name, email, password, user_type, status 
            FROM users 
            WHERE email = '$email' AND user_type = '$user_type' AND status = 'active'";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            return $user;
        }
    }
    
    return false;
}

/**
 * Get student by email
 * @param string $email
 * @return array|false
 */
function getStudentByEmail($email) {
    global $conn;
    
    $email = $conn->real_escape_string($email);
    
    $sql = "SELECT id, name, email, roll_number, status 
            FROM students 
            WHERE email = '$email' AND status = 'active'";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}

/**
 * Generate random OTP
 * @return string
 */
function generateOTP() {
    return str_pad(rand(0, 999999), OTP_LENGTH, '0', STR_PAD_LEFT);
}

/**
 * Save OTP to database
 * @param string $email
 * @param string $otp
 * @return bool
 */
function saveOTP($email, $otp) {
    global $conn;
    
    $email = $conn->real_escape_string($email);
    $otp_hash = password_hash($otp, PASSWORD_DEFAULT);
    $expiry = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
    
    // Delete old OTPs for this email
    $delete_sql = "DELETE FROM otp_verification WHERE email = '$email'";
    $conn->query($delete_sql);
    
    // Insert new OTP
    $sql = "INSERT INTO otp_verification (email, otp_hash, expiry_time, created_at) 
            VALUES ('$email', '$otp_hash', '$expiry', NOW())";
    
    return $conn->query($sql);
}

/**
 * Verify OTP
 * @param string $email
 * @param string $otp
 * @return bool
 */
function verifyOTP($email, $otp) {
    global $conn;
    
    $email = $conn->real_escape_string($email);
    
    $sql = "SELECT otp_hash, expiry_time 
            FROM otp_verification 
            WHERE email = '$email' AND expiry_time > NOW() 
            ORDER BY created_at DESC LIMIT 1";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if (password_verify($otp, $row['otp_hash'])) {
            // Delete used OTP
            $delete_sql = "DELETE FROM otp_verification WHERE email = '$email'";
            $conn->query($delete_sql);
            return true;
        }
    }
    
    return false;
}

/**
 * Send OTP email
 * @param string $email
 * @param string $otp
 * @return bool
 */
function sendOTPEmail($email, $otp) {
    // Check if PHPMailer is available
    if (file_exists(__DIR__ . '/phpmailer_functions.php')) {
        require_once __DIR__ . '/phpmailer_functions.php';
        return sendOTPEmailWithPHPMailer($email, $otp);
    }
    
    // Fallback to PHP mail() function
    $subject = "Your OTP for KPRCAS Attendance System";
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #667eea; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f8f9fa; }
            .otp-code { font-size: 32px; font-weight: bold; color: #667eea; text-align: center; padding: 20px; background: white; margin: 20px 0; border-radius: 10px; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>KPRCAS Attendance System</h1>
            </div>
            <div class='content'>
                <h2>Your OTP Code</h2>
                <p>Hello,</p>
                <p>You have requested to login to the KPRCAS Attendance System. Please use the following OTP to complete your login:</p>
                <div class='otp-code'>$otp</div>
                <p><strong>This OTP will expire in " . OTP_EXPIRY_MINUTES . " minutes.</strong></p>
                <p>If you did not request this OTP, please ignore this email.</p>
            </div>
            <div class='footer'>
                <p>&copy; 2025 KPRCAS. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">" . "\r\n";
    
    // For production, configure your PHP mail settings or use PHPMailer
    return mail($email, $subject, $message, $headers);
}

/**
 * Sanitize input data
 * @param string $data
 * @return string
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

/**
 * Check user type
 * @param string $type
 * @return bool
 */
function checkUserType($type) {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === $type;
}

/**
 * Logout user
 */
function logout() {
    session_start();
    session_unset();
    session_destroy();
    header('Location: ../login/login.php');
    exit();
}
?>
