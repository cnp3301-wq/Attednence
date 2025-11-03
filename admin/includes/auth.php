<?php
// Admin Authentication Check
session_start();
require_once __DIR__ . '/../../login/config/database.php';

function checkAdminAuth() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
        header('Location: ../../login/login.php');
        exit();
    }
}

function getAdminInfo() {
    global $conn;
    if (isset($_SESSION['user_id'])) {
        $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE id = ? AND user_type = 'admin'");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    return null;
}
?>
