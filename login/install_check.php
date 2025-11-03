<?php
/**
 * Installation Check Script
 * Run this file to verify your installation
 */

$checks = [];

// Check 1: PHP Version
$checks['PHP Version'] = [
    'status' => version_compare(PHP_VERSION, '7.4.0', '>='),
    'message' => PHP_VERSION . (version_compare(PHP_VERSION, '7.4.0', '>=') ? ' ✓' : ' (Required: 7.4+)')
];

// Check 2: Required PHP Extensions
$required_extensions = ['mysqli', 'session', 'json'];
foreach ($required_extensions as $ext) {
    $checks["Extension: $ext"] = [
        'status' => extension_loaded($ext),
        'message' => extension_loaded($ext) ? 'Loaded ✓' : 'Not loaded ✗'
    ];
}

// Check 3: Database Connection
require_once 'config/database.php';
$checks['Database Connection'] = [
    'status' => isset($conn) && $conn->connect_error === null,
    'message' => isset($conn) && $conn->connect_error === null ? 'Connected ✓' : 'Failed to connect ✗'
];

// Check 4: Check if tables exist
if (isset($conn) && $conn->connect_error === null) {
    $tables = ['users', 'students', 'otp_verification', 'login_logs'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        $checks["Table: $table"] = [
            'status' => $result && $result->num_rows > 0,
            'message' => $result && $result->num_rows > 0 ? 'Exists ✓' : 'Not found ✗'
        ];
    }
}

// Check 5: Session
session_start();
$checks['Session Support'] = [
    'status' => isset($_SESSION) || session_status() === PHP_SESSION_ACTIVE,
    'message' => isset($_SESSION) || session_status() === PHP_SESSION_ACTIVE ? 'Working ✓' : 'Not working ✗'
];

// Check 6: File Permissions
$checks['Config Directory'] = [
    'status' => is_readable('config/database.php'),
    'message' => is_readable('config/database.php') ? 'Readable ✓' : 'Not readable ✗'
];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Installation Check - KPRCAS</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .check-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .check-item.success {
            background: #d4edda;
            border-left: 4px solid #28a745;
        }
        .check-item.error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        .check-name {
            font-weight: 600;
            color: #333;
        }
        .check-message {
            color: #666;
        }
        .summary {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
        }
        .summary.all-good {
            background: #d4edda;
            color: #155724;
        }
        .summary.has-errors {
            background: #f8d7da;
            color: #721c24;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 20px;
            font-weight: 600;
        }
        .btn:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 KPRCAS Installation Check</h1>
        
        <?php
        $all_passed = true;
        foreach ($checks as $name => $check) {
            $class = $check['status'] ? 'success' : 'error';
            if (!$check['status']) $all_passed = false;
            
            echo "<div class='check-item $class'>";
            echo "<span class='check-name'>$name</span>";
            echo "<span class='check-message'>{$check['message']}</span>";
            echo "</div>";
        }
        ?>
        
        <div class="summary <?php echo $all_passed ? 'all-good' : 'has-errors'; ?>">
            <?php if ($all_passed): ?>
                <h2>✅ All Checks Passed!</h2>
                <p>Your installation is complete and ready to use.</p>
                <a href="login.php" class="btn">Go to Login Page</a>
            <?php else: ?>
                <h2>⚠️ Some Checks Failed</h2>
                <p>Please fix the issues above before proceeding.</p>
                <p><strong>Common solutions:</strong></p>
                <ul style="text-align: left; margin-left: 50px;">
                    <li>Import database_schema.sql in phpMyAdmin</li>
                    <li>Update database credentials in config/database.php</li>
                    <li>Ensure PHP extensions are enabled</li>
                    <li>Check file permissions</li>
                </ul>
            <?php endif; ?>
        </div>
        
        <div style="margin-top: 30px; padding: 20px; background: #e7f3ff; border-radius: 8px;">
            <h3>📚 Quick Links:</h3>
            <ul>
                <li><a href="generate_password.php">Generate Password Hash</a></li>
                <li><a href="login.php">Login Page</a></li>
                <li><a href="../README.md">Documentation</a></li>
            </ul>
        </div>
        
        <div style="margin-top: 20px; text-align: center; color: #666; font-size: 12px;">
            <p>KPRCAS Attendance System © 2025</p>
        </div>
    </div>
</body>
</html>
