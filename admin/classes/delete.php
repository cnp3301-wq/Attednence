<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
checkAdminAuth();

$id = $_GET['id'] ?? 0;

// Check if class has students
$stmt = $conn->prepare("SELECT student_count FROM classes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$class = $stmt->get_result()->fetch_assoc();

if ($class && $class['student_count'] > 0) {
    setMessage('warning', 'Cannot delete class with assigned students! Please reassign students first.');
    header('Location: index.php');
    exit();
}

// Delete class
$stmt = $conn->prepare("DELETE FROM classes WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    setMessage('success', 'Class deleted successfully!');
} else {
    setMessage('danger', 'Error deleting class: ' . $conn->error);
}

header('Location: index.php');
exit();
?>
