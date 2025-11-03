<?php
/**
 * Password Hash Generator
 * Use this script to generate password hashes for users
 */

echo "<h2>Password Hash Generator</h2>";
echo "<p>Use this tool to generate password hashes for your database.</p>";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['password'])) {
    $password = $_POST['password'];
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<strong>Password:</strong> " . htmlspecialchars($password) . "<br>";
    echo "<strong>Hash:</strong> <code>" . $hash . "</code><br>";
    echo "<small>Copy this hash and use it in your SQL INSERT statement.</small>";
    echo "</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Password Hash Generator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #0056b3;
        }
        code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Password Hash Generator</h1>
        <form method="POST">
            <label for="password">Enter Password:</label>
            <input type="text" id="password" name="password" placeholder="Enter password to hash" required>
            <button type="submit">Generate Hash</button>
        </form>
        
        <hr style="margin: 30px 0;">
        
        <h3>Default Credentials:</h3>
        <p><strong>Admin:</strong> admin@kprcas.ac.in / admin123</p>
        <p><strong>Teacher:</strong> teacher@kprcas.ac.in / teacher123</p>
        <p><strong>Note:</strong> All default passwords use hash: <code>$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi</code></p>
    </div>
</body>
</html>
