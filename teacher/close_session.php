<?php
session_start();
require_once '../login/config/database.php';

// Check if user is logged in as teacher
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../login/login.php');
    exit();
}

$teacher_id = $_SESSION['user_id'];
$session_id = intval($_GET['session_id'] ?? 0);

// Close the session and mark absent students
if ($session_id > 0) {
    // First, verify this session belongs to the teacher
    $verify_query = "SELECT id, class_id FROM attendance_sessions WHERE id = ? AND teacher_id = ?";
    $verify_stmt = $conn->prepare($verify_query);
    $verify_stmt->bind_param("ii", $session_id, $teacher_id);
    $verify_stmt->execute();
    $session_result = $verify_stmt->get_result();
    
    if ($session_result->num_rows > 0) {
        $session = $session_result->fetch_assoc();
        $class_id = $session['class_id'];
        
        // Mark all students who haven't marked attendance as absent
        $absent_query = "INSERT INTO attendance (session_id, student_id, teacher_id, subject_id, class_id, attendance_date, status, marked_via)
            SELECT 
                ?, 
                s.id, 
                ats.teacher_id, 
                ats.subject_id, 
                ats.class_id, 
                ats.session_date, 
                'absent', 
                'auto'
            FROM students s
            INNER JOIN attendance_sessions ats ON ats.id = ?
            WHERE s.class_id = ? 
            AND s.status = 'active'
            AND NOT EXISTS (
                SELECT 1 FROM attendance a 
                WHERE a.session_id = ? AND a.student_id = s.id
            )";
        
        $absent_stmt = $conn->prepare($absent_query);
        $absent_stmt->bind_param("iiii", $session_id, $session_id, $class_id, $session_id);
        $absent_stmt->execute();
        
        // Update session status to closed
        $close_query = "UPDATE attendance_sessions SET status = 'closed' WHERE id = ?";
        $close_stmt = $conn->prepare($close_query);
        $close_stmt->bind_param("i", $session_id);
        $close_stmt->execute();
        
        $_SESSION['message'] = "Session closed successfully. Absent students marked automatically.";
        $_SESSION['message_type'] = "success";
    }
}

header('Location: take_attendance.php');
exit();
?>
