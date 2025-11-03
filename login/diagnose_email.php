<?php
use PHPMailer\PHPMailer\PHPMailer;

// Diagnose email configuration issues
require_once 'config/email_config.php';

echo "=== Email Configuration Diagnostics ===\n\n";

echo "1. Gmail Address: " . SMTP_USERNAME . "\n";
echo "   ✓ Valid email format: " . (filter_var(SMTP_USERNAME, FILTER_VALIDATE_EMAIL) ? "Yes" : "No") . "\n\n";

echo "2. App Password: " . SMTP_PASSWORD . "\n";
$pwd_len = strlen(SMTP_PASSWORD);
echo "   Length: $pwd_len characters\n";
echo "   Expected: 16 characters\n";

if ($pwd_len !== 16) {
    echo "   ✗ ERROR: App password must be EXACTLY 16 characters!\n";
    echo "   \n";
    echo "   COMMON ISSUES:\n";
    echo "   • Did you copy spaces from Google? (Remove all spaces)\n";
    echo "   • Did you copy the full password? (Should be 16 chars)\n";
    echo "   • Example format: abcdefghijklmnop\n";
    echo "   \n";
    echo "   YOUR PASSWORD HAS: $pwd_len chars (needs " . (16 - $pwd_len) . " more)\n";
} else {
    echo "   ✓ Length correct\n";
}

echo "\n";
echo "3. How to fix:\n";
echo "   a) Go back to: https://myaccount.google.com/apppasswords\n";
echo "   b) DELETE the old app password\n";
echo "   c) CREATE A NEW app password\n";
echo "   d) Copy it CAREFULLY (remove all spaces)\n";
echo "   e) Should look like: abcd efgh ijkl mnop\n";
echo "   f) Remove spaces: abcdefghijklmnop (exactly 16 chars)\n";
echo "   g) Paste into email_config.php\n";
echo "\n";

echo "4. Your FROM email: " . SMTP_FROM_EMAIL . "\n";
echo "   Note: Gmail may override this with your actual Gmail address\n";
echo "\n";

if ($pwd_len === 16) {
    echo "=== Attempting SMTP Connection ===\n";
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $mail = new PHPMailer(true);
    
    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = 0; // Quiet mode
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->Timeout = 10;
        
        $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
        $mail->addAddress(SMTP_USERNAME);
        $mail->Subject = 'KPRCAS Test Email';
        $mail->Body = 'Test successful!';
        
        $mail->send();
        echo "✓ SUCCESS! Email sent to " . SMTP_USERNAME . "\n";
        echo "  Check your inbox to confirm.\n";
    } catch (Exception $e) {
        echo "✗ FAILED: " . $mail->ErrorInfo . "\n";
        echo "\n";
        echo "TROUBLESHOOTING:\n";
        echo "• Is 2-Step Verification enabled? https://myaccount.google.com/security\n";
        echo "• Did you create an APP PASSWORD (not regular password)?\n";
        echo "• Did you copy the FULL 16-character password?\n";
        echo "• Try deleting and creating a NEW app password\n";
    }
}

echo "\n=== End Diagnostics ===\n";
?>
