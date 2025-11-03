<?php
/**
 * Email Testing Script
 * Use this to test your email configuration
 */

// Display errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Test - KPRCAS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 50px 0;
        }
        .test-container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .result {
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .code-block {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-family: monospace;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1 class="text-center mb-4">📧 Email Configuration Test</h1>
        
        <?php
        // Test 1: Check if PHPMailer is installed
        echo "<div class='result info'>";
        echo "<h4>Test 1: PHPMailer Installation</h4>";
        
        if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
            echo "<p>✅ <strong>vendor/autoload.php</strong> found!</p>";
            require_once __DIR__ . '/../vendor/autoload.php';
            
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                echo "<p>✅ PHPMailer class loaded successfully!</p>";
                $phpmailerInstalled = true;
            } else {
                echo "<p>❌ PHPMailer class not found!</p>";
                $phpmailerInstalled = false;
            }
        } else {
            echo "<p>❌ <strong>vendor/autoload.php</strong> not found!</p>";
            echo "<p>Please run: <code>composer install</code></p>";
            $phpmailerInstalled = false;
        }
        echo "</div>";
        
        // Test 2: Check configuration files
        echo "<div class='result info'>";
        echo "<h4>Test 2: Configuration Files</h4>";
        
        if (file_exists(__DIR__ . '/config/email_config.php')) {
            echo "<p>✅ Email config file found!</p>";
            require_once __DIR__ . '/config/email_config.php';
            
            echo "<div class='code-block'>";
            echo "SMTP Host: " . SMTP_HOST . "<br>";
            echo "SMTP Port: " . SMTP_PORT . "<br>";
            echo "SMTP Username: " . SMTP_USERNAME . "<br>";
            echo "SMTP Password: " . (SMTP_PASSWORD ? str_repeat('*', strlen(SMTP_PASSWORD)) : 'Not set') . "<br>";
            echo "</div>";
            
            if (SMTP_USERNAME === 'your-email@gmail.com') {
                echo "<p>⚠️ Warning: Default email configuration detected. Please update <strong>config/email_config.php</strong></p>";
            }
        } else {
            echo "<p>❌ Email config file not found!</p>";
        }
        echo "</div>";
        
        // Test 3: Send test email
        if ($phpmailerInstalled && isset($_POST['test_email'])) {
            echo "<div class='result'>";
            echo "<h4>Test 3: Sending Email</h4>";
            
            $testEmail = $_POST['test_email'];
            $testOTP = '123456';
            
            try {
                require_once __DIR__ . '/includes/phpmailer_functions.php';
                
                if (sendOTPEmailWithPHPMailer($testEmail, $testOTP)) {
                    echo "<div class='success'>";
                    echo "<p>✅ <strong>Email sent successfully!</strong></p>";
                    echo "<p>Check your inbox at: <strong>$testEmail</strong></p>";
                    echo "<p>OTP Code sent: <strong>$testOTP</strong></p>";
                    echo "</div>";
                } else {
                    echo "<div class='error'>";
                    echo "<p>❌ <strong>Email sending failed!</strong></p>";
                    echo "<p>Check your SMTP credentials and configuration.</p>";
                    echo "</div>";
                }
            } catch (Exception $e) {
                echo "<div class='error'>";
                echo "<p>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
                echo "</div>";
            }
            
            echo "</div>";
        }
        ?>
        
        <!-- Test Email Form -->
        <?php if ($phpmailerInstalled): ?>
        <div class="mt-4">
            <h4>Send Test Email</h4>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="test_email" class="form-label">Enter your email address:</label>
                    <input type="email" class="form-control" id="test_email" name="test_email" 
                           placeholder="your-email@example.com" required>
                    <small class="form-text text-muted">A test OTP email will be sent to this address</small>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Send Test Email
                </button>
            </form>
        </div>
        <?php endif; ?>
        
        <!-- Troubleshooting Tips -->
        <div class="mt-4 p-3" style="background: #fff3cd; border-radius: 10px;">
            <h5>🔧 Troubleshooting Tips:</h5>
            <ul>
                <li><strong>PHPMailer not found?</strong> Run: <code>composer install</code></li>
                <li><strong>SMTP Error?</strong> Check credentials in <code>config/email_config.php</code></li>
                <li><strong>Gmail not working?</strong> Use App Password, not regular password</li>
                <li><strong>Connection refused?</strong> Check firewall and port settings</li>
                <li><strong>Email in spam?</strong> Normal for testing, configure SPF/DKIM for production</li>
            </ul>
        </div>
        
        <div class="text-center mt-4">
            <a href="login.php" class="btn btn-secondary">Back to Login</a>
            <a href="install_check.php" class="btn btn-info">Installation Check</a>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
