<?php
/**
 * Interactive Email Configuration Script
 * Run this to quickly set up email for OTP
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "\n";
echo "================================================\n";
echo "   KPRCAS Email Configuration Helper\n";
echo "================================================\n\n";

echo "Current Status:\n";
echo "- FROM Email: noreply@kprcas.ac.in\n";
echo "- Gmail: cmp3301@gmail.com\n";
echo "- Problem: Gmail authentication failing\n\n";

echo "================================================\n";
echo "Choose Email Provider:\n";
echo "================================================\n";
echo "1. Gmail (requires App Password)\n";
echo "2. Outlook (simpler - no app password needed)\n";
echo "3. Custom SMTP server\n\n";

$choice = readline("Enter choice (1/2/3): ");

switch($choice) {
    case '1':
        setupGmail();
        break;
    case '2':
        setupOutlook();
        break;
    case '3':
        setupCustom();
        break;
    default:
        echo "Invalid choice!\n";
        exit(1);
}

function setupGmail() {
    echo "\n=== Gmail Setup ===\n\n";
    echo "You need a Gmail App Password.\n";
    echo "1. Go to: https://myaccount.google.com/apppasswords\n";
    echo "2. Create new app password for 'Mail'\n";
    echo "3. Copy the 16-character password (remove spaces)\n\n";
    
    $email = readline("Gmail address [cmp3301@gmail.com]: ");
    if (empty($email)) $email = 'cmp3301@gmail.com';
    
    $password = readline("App Password (16 chars, no spaces): ");
    
    if (strlen($password) !== 16) {
        echo "\nERROR: App password must be exactly 16 characters!\n";
        echo "You entered: " . strlen($password) . " characters\n";
        exit(1);
    }
    
    $config = [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'username' => $email,
        'password' => $password
    ];
    
    saveAndTest($config);
}

function setupOutlook() {
    echo "\n=== Outlook Setup ===\n\n";
    echo "Outlook is simpler - uses your regular password (no app password needed).\n\n";
    
    $email = readline("Outlook email (e.g., kprcas.attendance@outlook.com): ");
    $password = readline("Outlook password: ");
    
    $config = [
        'host' => 'smtp-mail.outlook.com',
        'port' => 587,
        'username' => $email,
        'password' => $password
    ];
    
    saveAndTest($config);
}

function setupCustom() {
    echo "\n=== Custom SMTP Server ===\n\n";
    
    $host = readline("SMTP Host (e.g., smtp.kprcas.ac.in): ");
    $port = readline("SMTP Port [587]: ");
    if (empty($port)) $port = 587;
    
    $username = readline("Email address: ");
    $password = readline("Password: ");
    
    $config = [
        'host' => $host,
        'port' => $port,
        'username' => $username,
        'password' => $password
    ];
    
    saveAndTest($config);
}

    require_once __DIR__ . '/../vendor/autoload.php';
    
    $mail = new PHPMailer(true);
    use PHPMailer\PHPMailer\Exception;
    
    $mail = new PHPMailer(true);
    
    try {
        $mail->SMTPDebug = 2;
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['port'];
        $mail->Timeout = 10;
        
        $mail->setFrom($config['username'], 'KPRCAS Attendance System');
        $mail->addAddress($config['username']);
        $mail->Subject = 'KPRCAS Email Test';
        $mail->Body = 'Configuration successful!';
        
        $mail->send();
        
        echo "\n✓ SUCCESS! Email sent successfully.\n";
        echo "Saving configuration...\n";
        
        // Save configuration
        $configContent = "<?php\n";
        $configContent .= "// Email Configuration - Auto-generated on " . date('Y-m-d H:i:s') . "\n";
        $configContent .= "define('SMTP_HOST', '" . addslashes($config['host']) . "');\n";
        $configContent .= "define('SMTP_PORT', " . $config['port'] . ");\n";
        $configContent .= "define('SMTP_USERNAME', '" . addslashes($config['username']) . "');\n";
        $configContent .= "define('SMTP_PASSWORD', '" . addslashes($config['password']) . "');\n";
        $configContent .= "define('SMTP_FROM_EMAIL', 'noreply@kprcas.ac.in');\n";
        $configContent .= "define('SMTP_FROM_NAME', 'KPRCAS Attendance System');\n";
        $configContent .= "define('OTP_LENGTH', 6);\n";
        $configContent .= "define('OTP_EXPIRY_MINUTES', 10);\n";
        $configContent .= "?>\n";
        
        file_put_contents(__DIR__ . '/config/email_config.php', $configContent);
        
        echo "✓ Configuration saved to: login/config/email_config.php\n";
        echo "\nOTP emails will now work!\n";
        echo "Test the login page: http://localhost/attendance/login/login.php\n";
        
    } catch (Exception $e) {
        echo "\n✗ FAILED: " . $mail->ErrorInfo . "\n";
        echo "\nPlease check:\n";
        echo "- Email and password are correct\n";
        echo "- 2-Step Verification is enabled (for Gmail)\n";
        echo "- App Password is valid (for Gmail)\n";
        exit(1);
    }
}

?>
