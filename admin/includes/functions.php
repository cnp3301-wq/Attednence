<?php
// Common functions for admin dashboard

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generatePassword($name = '', $length = 10) {
    // If name is provided, create password based on name
    if (!empty($name)) {
        // Remove spaces and special characters from name
        $cleanName = preg_replace('/[^a-zA-Z]/', '', $name);
        
        // Take first part of name (up to 6 chars)
        $namePart = substr(strtolower($cleanName), 0, 6);
        
        // Generate random 4-digit number
        $numberPart = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        
        // Capitalize first letter
        $password = ucfirst($namePart) . $numberPart;
        
        return $password;
    }
    
    // Fallback: Generate random password with mix of letters and numbers
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($chars), 0, $length);
}

function setMessage($type, $message) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type; // success, danger, warning, info
}

function showMessage() {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] ?? 'info';
        $message = $_SESSION['message'];
        unset($_SESSION['message'], $_SESSION['message_type']);
        return "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                    {$message}
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                </div>";
    }
    return '';
}

function updateStudentCount($class_id) {
    global $conn;
    $stmt = $conn->prepare("UPDATE classes SET student_count = (SELECT COUNT(*) FROM students WHERE class_id = ?) WHERE id = ?");
    $stmt->bind_param("ii", $class_id, $class_id);
    return $stmt->execute();
}
?>
