<?php
// Database configuration for InfinityFree
define('DB_HOST', 'sql313.infinityfree.com');
define('DB_USER', 'if0_40319236');
define('DB_PASS', 's1Vvgdu1Bmxp');
define('DB_NAME', 'if0_40319236_XXX'); // Replace XXX with your actual database name

// Create database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}
?>
