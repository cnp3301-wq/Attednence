<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/login_debug.log');

session_start();

echo "<!DOCTYPE html><html><head><title>Login Debug</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#f5f5f5;}";
echo ".success{color:green;}.error{color:red;}.info{color:blue;}";
echo "pre{background:white;padding:10px;border:1px solid #ccc;}</style></head><body>";
echo "<h1>KPRCAS Login Debug Tool</h1>";

// Test 1: Database Connection
echo "<h2>1. Database Connection Test</h2>";
require_once 'config/database.php';
if ($conn->connect_error) {
    echo "<p class='error'>✗ FAILED: " . $conn->connect_error . "</p>";
} else {
    echo "<p class='success'>✓ SUCCESS: Connected to database</p>";
}

// Test 2: Check if database exists
echo "<h2>2. Database & Tables Check</h2>";
$result = $conn->query("SELECT DATABASE()");
$db = $result->fetch_row();
echo "<p class='info'>Current Database: " . $db[0] . "</p>";

$tables = ['users', 'students', 'otp_verification'];
foreach ($tables as $table) {
    $check = $conn->query("SHOW TABLES LIKE '$table'");
    if ($check->num_rows > 0) {
        $count = $conn->query("SELECT COUNT(*) as cnt FROM $table")->fetch_assoc();
        echo "<p class='success'>✓ Table '$table' exists ({$count['cnt']} records)</p>";
    } else {
        echo "<p class='error'>✗ Table '$table' NOT found</p>";
    }
}

// Test 3: Admin User Check
echo "<h2>3. Admin User Test</h2>";
$email = 'admin@kprcas.ac.in';
$password = 'admin123';

$sql = "SELECT * FROM users WHERE email = '$email'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<p class='success'>✓ Admin user found</p>";
    echo "<pre>";
    echo "ID: " . $user['id'] . "\n";
    echo "Name: " . $user['name'] . "\n";
    echo "Email: " . $user['email'] . "\n";
    echo "User Type: " . $user['user_type'] . "\n";
    echo "Status: " . $user['status'] . "\n";
    echo "Password Hash: " . substr($user['password'], 0, 40) . "...\n";
    echo "</pre>";
    
    // Test password verification
    echo "<h3>Password Verification Test</h3>";
    echo "<p class='info'>Testing password: '$password'</p>";
    
    if (password_verify($password, $user['password'])) {
        echo "<p class='success'>✓ Password verification: SUCCESS</p>";
    } else {
        echo "<p class='error'>✗ Password verification: FAILED</p>";
        echo "<p class='error'>The stored hash does not match '$password'</p>";
    }
} else {
    echo "<p class='error'>✗ Admin user NOT found</p>";
}

// Test 4: Functions File
echo "<h2>4. Functions File Test</h2>";
if (file_exists('includes/functions.php')) {
    echo "<p class='success'>✓ functions.php exists</p>";
    require_once 'includes/functions.php';
    
    // Test email validation
    echo "<h3>Email Validation Test</h3>";
    if (function_exists('validateKprcasEmail')) {
        $test_valid = validateKprcasEmail('admin@kprcas.ac.in');
        $test_invalid = validateKprcasEmail('admin@gmail.com');
        echo "<p class='info'>validateKprcasEmail('admin@kprcas.ac.in'): " . ($test_valid ? 'true' : 'false') . "</p>";
        echo "<p class='info'>validateKprcasEmail('admin@gmail.com'): " . ($test_invalid ? 'true' : 'false') . "</p>";
    } else {
        echo "<p class='error'>✗ validateKprcasEmail() function not found</p>";
    }
    
    // Test authentication function
    echo "<h3>Authentication Function Test</h3>";
    if (function_exists('authenticateUser')) {
        echo "<p class='info'>Testing authenticateUser('$email', '$password', 'admin')</p>";
        $auth_result = authenticateUser($email, $password, 'admin');
        if ($auth_result) {
            echo "<p class='success'>✓ Authentication: SUCCESS</p>";
            echo "<pre>" . print_r($auth_result, true) . "</pre>";
        } else {
            echo "<p class='error'>✗ Authentication: FAILED</p>";
            echo "<p class='error'>The authenticateUser() function returned false</p>";
        }
    } else {
        echo "<p class='error'>✗ authenticateUser() function not found</p>";
    }
} else {
    echo "<p class='error'>✗ functions.php NOT found</p>";
}

// Test 5: Login Form POST Simulation
echo "<h2>5. Simulate Login Form POST</h2>";
echo "<form method='POST' action='login.php' style='background:white;padding:20px;border:1px solid #ccc;'>";
echo "<h3>Test Login Form</h3>";
echo "<p><label>Email: <input type='email' name='email' value='admin@kprcas.ac.in' style='width:300px;padding:5px;'></label></p>";
echo "<p><label>Password: <input type='password' name='password' value='admin123' style='width:300px;padding:5px;'></label></p>";
echo "<input type='hidden' name='user_type' value='admin'>";
echo "<input type='hidden' name='action' value='login'>";
echo "<p><button type='submit' style='padding:10px 20px;background:#667eea;color:white;border:none;cursor:pointer;'>Test Login</button></p>";
echo "</form>";

// Test 6: Session Information
echo "<h2>6. Session Information</h2>";
if (isset($_SESSION['user_id'])) {
    echo "<p class='success'>✓ User is logged in</p>";
    echo "<pre>";
    echo "User ID: " . $_SESSION['user_id'] . "\n";
    echo "Email: " . $_SESSION['user_email'] . "\n";
    echo "Type: " . $_SESSION['user_type'] . "\n";
    echo "Name: " . $_SESSION['user_name'] . "\n";
    echo "</pre>";
    echo "<p><a href='logout.php' style='color:red;'>Logout</a></p>";
} else {
    echo "<p class='info'>No active session</p>";
}

// Test 7: PHP Info
echo "<h2>7. PHP Configuration</h2>";
echo "<p class='info'>PHP Version: " . PHP_VERSION . "</p>";
echo "<p class='info'>Session Save Path: " . session_save_path() . "</p>";
echo "<p class='info'>Session ID: " . session_id() . "</p>";

$conn->close();
echo "</body></html>";
?>
