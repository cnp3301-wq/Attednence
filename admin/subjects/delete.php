<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
checkAdminAuth();

$id = $_GET['id'] ?? 0;

// Check if subject has teacher assignments
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM teacher_subjects WHERE subject_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if ($result['count'] > 0) {
    setMessage('warning', 'Cannot delete subject with teacher assignments! Please remove teacher assignments first.');
    header('Location: index.php');
    exit();
}

// Delete subject
$stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        setMessage('success', 'Subject deleted successfully!');
    } else {
        setMessage('danger', 'Subject not found!');
    }
} else {
    setMessage('danger', 'Error deleting subject: ' . $conn->error);
}

header('Location: index.php');
exit();
?>
