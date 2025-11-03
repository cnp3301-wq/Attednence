<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
checkAdminAuth();

$id = $_GET['id'] ?? 0;

// Delete assignment
$stmt = $conn->prepare("DELETE FROM teacher_subjects WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        setMessage('success', 'Subject unassigned successfully!');
    } else {
        setMessage('danger', 'Assignment not found!');
    }
} else {
    setMessage('danger', 'Error unassigning subject: ' . $conn->error);
}

header('Location: index.php');
exit();
?>
