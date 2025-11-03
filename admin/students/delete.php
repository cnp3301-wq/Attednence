<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
checkAdminAuth();

$id = $_GET['id'] ?? 0;

// Get student's class_id before deletion
$stmt = $conn->prepare("SELECT class_id FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
    $class_id = $student['class_id'];
    
    // Delete student
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Update student count if student was assigned to a class
        if ($class_id) {
            updateStudentCount($class_id);
        }
        setMessage('success', 'Student deleted successfully!');
    } else {
        setMessage('danger', 'Error deleting student: ' . $conn->error);
    }
} else {
    setMessage('danger', 'Student not found!');
}

header('Location: index.php');
exit();
?>
