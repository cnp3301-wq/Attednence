<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
checkAdminAuth();

$id = $_GET['id'] ?? 0;

// Check if teacher has subject assignments
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM teacher_subjects WHERE teacher_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($result['count'] > 0) {
    setMessage('warning', 'Cannot delete teacher with assigned subjects! Please remove subject assignments first.');
    header('Location: index.php');
    exit();
}

// Delete teacher
$stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND user_type = 'teacher'");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        setMessage('success', 'Teacher deleted successfully!');
    } else {
        setMessage('danger', 'Teacher not found!');
    }
} else {
    setMessage('danger', 'Error deleting teacher: ' . $conn->error);
}

header('Location: index.php');
exit();
?>
