<?php
// Simple test script to manually test the login flow
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Manual Login Flow Test</h1>";
echo "<pre>";

// Step 1: Start session
session_start();
echo "✓ Session started\n\n";

// Step 2: Include database
require_once 'config/database.php';
echo "✓ Database included\n";
echo "  Connection status: " . ($conn->connect_error ? "FAILED" : "SUCCESS") . "\n\n";

// Step 3: Include functions
require_once 'includes/functions.php';
echo "✓ Functions included\n\n";

// Step 4: Simulate login
$email = 'admin@kprcas.ac.in';
$password = 'admin123';
$user_type = 'admin';

echo "==Testing Login Credentials===\n";
echo "Email: $email\n";
echo "Password: $password\n";
echo "User Type: $user_type\n\n";

// Step 5: Validate email
echo "Step 1: Validate Email\n";
$isValid = validateKprcasEmail($email);
echo "Result: " . ($isValid ? "VALID" : "INVALID") . "\n\n";

if (!$isValid) {
    echo "ERROR: Email validation failed!\n";
    exit;
}

// Step 6: Authenticate
echo "Step 2: Authenticate User\n";
$user = authenticateUser($email, $password, $user_type);

if ($user) {
    echo "Result: SUCCESS\n";
    echo "User Data:\n";
    print_r($user);
    echo "\n";
    
    // Step 7: Set session
    echo "Step 3: Set Session Variables\n";
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['user_name'] = $user['name'];
    echo "✓ Session variables set\n\n";
    
    echo "Step 4: Check Session\n";
    echo "user_id: " . $_SESSION['user_id'] . "\n";
    echo "user_email: " . $_SESSION['user_email'] . "\n";
    echo "user_type: " . $_SESSION['user_type'] . "\n";
    echo "user_name: " . $_SESSION['user_name'] . "\n\n";
    
    echo "Step 5: Test Authentication Functions\n";
    echo "isLoggedIn(): " . (isLoggedIn() ? "TRUE" : "FALSE") . "\n";
    echo "checkUserType('admin'): " . (checkUserType('admin') ? "TRUE" : "FALSE") . "\n\n";
    
    echo "✅ LOGIN SUCCESSFUL!\n\n";
    echo "Dashboard URL: http://localhost/attendance/dashboard/admin_dashboard.php\n";
    echo "<a href='../dashboard/admin_dashboard.php' style='font-size:20px; padding:10px 20px; background:#667eea; color:white; text-decoration:none; border-radius:5px; display:inline-block; margin-top:20px;'>Go to Dashboard</a>";
    
} else {
    echo "Result: FAILED\n";
    echo "❌ Authentication failed!\n\n";
    
    // Debug: Check password hash
    echo "Debug Information:\n";
    $sql = "SELECT password FROM users WHERE email = '$email'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "Stored hash: " . substr($row['password'], 0, 40) . "...\n";
        echo "Password verify test: " . (password_verify($password, $row['password']) ? "PASS" : "FAIL") . "\n";
    }
}

echo "</pre>";
?>
