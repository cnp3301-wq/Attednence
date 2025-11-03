<?php
// Teacher authentication check
function checkTeacherAuth() {
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'teacher') {
        header('Location: ../login/login.php');
        exit();
    }
}
?>
