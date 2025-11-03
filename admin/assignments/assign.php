<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
checkAdminAuth();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $teacher_id = intval($_POST['teacher_id']);
    $subject_ids = $_POST['subject_ids'] ?? [];
    
    if (empty($teacher_id) || empty($subject_ids)) {
        setMessage('danger', 'Please select a teacher and at least one subject!');
        header('Location: index.php');
        exit();
    }
    
    $assigned_count = 0;
    $skipped_count = 0;
    
    foreach ($subject_ids as $subject_id) {
        $subject_id = intval($subject_id);
        
        // Check if assignment already exists
        $check_stmt = $conn->prepare("SELECT id FROM teacher_subjects WHERE teacher_id = ? AND subject_id = ?");
        $check_stmt->bind_param("ii", $teacher_id, $subject_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $skipped_count++;
            continue;
        }
        
        // Insert assignment
        $stmt = $conn->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $teacher_id, $subject_id);
        
        if ($stmt->execute()) {
            $assigned_count++;
        }
    }
    
    // Build message
    $message = '';
    if ($assigned_count > 0) {
        $message .= "$assigned_count subject(s) assigned successfully!";
    }
    if ($skipped_count > 0) {
        $message .= ($message ? ' ' : '') . "$skipped_count subject(s) already assigned (skipped).";
    }
    
    if ($assigned_count > 0) {
        setMessage('success', $message);
    } else {
        setMessage('warning', $message);
    }
}

header('Location: index.php');
exit();
?>
