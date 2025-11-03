<?php
session_start();
require_once '../login/config/database.php';

$session_code = $_GET['code'] ?? '';
$message = '';
$message_type = '';

// Check if OTP was sent
if (!isset($_SESSION['attendance_otp']) || !isset($_SESSION['attendance_email'])) {
    header('Location: mark_attendance.php?code=' . $session_code);
    exit();
}

// Check OTP expiry (5 minutes)
if (time() - $_SESSION['otp_time'] > 300) {
    unset($_SESSION['attendance_otp']);
    $_SESSION['error_message'] = "OTP has expired. Please request a new one.";
    header('Location: mark_attendance.php?code=' . $session_code);
    exit();
}

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_otp'])) {
    $entered_otp = trim($_POST['otp']);
    
    if ($entered_otp == $_SESSION['attendance_otp']) {
        $session_id = $_SESSION['attendance_session_id'];
        $student_id = $_SESSION['attendance_student_id'];
        
        // Get session details
        $session_query = "SELECT teacher_id, subject_id, class_id, session_date FROM attendance_sessions WHERE id = ?";
        $session_stmt = $conn->prepare($session_query);
        $session_stmt->bind_param("i", $session_id);
        $session_stmt->execute();
        $session_data = $session_stmt->get_result()->fetch_assoc();
        
        // Mark attendance as present
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $attendance_time = date('H:i:s');
        
        $insert_query = "INSERT INTO attendance 
            (session_id, student_id, teacher_id, subject_id, class_id, attendance_date, attendance_time, status, marked_via, ip_address) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'present', 'qr_code', ?)";
        
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("iiiissss", 
            $session_id, 
            $student_id, 
            $session_data['teacher_id'], 
            $session_data['subject_id'], 
            $session_data['class_id'], 
            $session_data['session_date'], 
            $attendance_time, 
            $ip_address
        );
        
        if ($insert_stmt->execute()) {
            // Clear session data
            unset($_SESSION['attendance_otp']);
            unset($_SESSION['attendance_email']);
            unset($_SESSION['attendance_student_id']);
            unset($_SESSION['attendance_session_id']);
            unset($_SESSION['otp_time']);
            
            $message = "Attendance marked successfully!";
            $message_type = "success";
            $success = true;
        } else {
            $message = "Error marking attendance. Please try again.";
            $message_type = "danger";
        }
    } else {
        $message = "Invalid OTP. Please try again.";
        $message_type = "danger";
    }
}

// Handle resend OTP
if (isset($_GET['resend'])) {
    // Redirect back to mark_attendance to resend
    unset($_SESSION['attendance_otp']);
    unset($_SESSION['otp_time']);
    header('Location: mark_attendance.php?code=' . $session_code);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Verify OTP - KPRCAS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/responsive.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            max-width: 500px;
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .otp-input {
            font-size: 2rem;
            text-align: center;
            letter-spacing: 10px;
        }
        .countdown {
            font-size: 1.2rem;
            color: #667eea;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <i class="fas fa-shield-alt fa-4x text-primary"></i>
                <h3 class="mt-3">Verify OTP</h3>
                <p class="text-muted">Enter the 6-digit code sent to<br><?php echo $_SESSION['attendance_email']; ?></p>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i> 
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <?php if (!isset($success)): ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <input type="text" name="otp" class="form-control otp-input" 
                           placeholder="000000" maxlength="6" pattern="\d{6}" required autofocus>
                </div>

                <div class="mb-3 text-center">
                    <div class="countdown" id="countdown">Time remaining: 5:00</div>
                </div>

                <button type="submit" name="verify_otp" class="btn btn-primary btn-lg w-100 mb-3">
                    <i class="fas fa-check"></i> Verify & Mark Attendance
                </button>
            </form>

            <div class="text-center">
                <a href="?resend&code=<?php echo $session_code; ?>" class="btn btn-link">
                    <i class="fas fa-redo"></i> Resend OTP
                </a>
                <a href="mark_attendance.php?code=<?php echo $session_code; ?>" class="btn btn-link">
                    <i class="fas fa-arrow-left"></i> Change Email
                </a>
            </div>
            <?php else: ?>
            <div class="text-center">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle fa-3x mb-3"></i>
                    <h5>Attendance Marked!</h5>
                    <p>Your attendance has been recorded successfully.</p>
                </div>
                <a href="../login/login.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Go to Login
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        <?php if (!isset($success)): ?>
        // Countdown timer
        var timeLeft = <?php echo 300 - (time() - $_SESSION['otp_time']); ?>;
        var countdownEl = document.getElementById('countdown');
        
        setInterval(function() {
            timeLeft--;
            if (timeLeft <= 0) {
                window.location.href = 'mark_attendance.php?code=<?php echo $session_code; ?>';
            } else {
                var minutes = Math.floor(timeLeft / 60);
                var seconds = timeLeft % 60;
                countdownEl.innerHTML = 'Time remaining: ' + minutes + ':' + String(seconds).padStart(2, '0');
                
                if (timeLeft < 60) {
                    countdownEl.style.color = '#dc3545';
                }
            }
        }, 1000);
        <?php endif; ?>
    </script>
    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>
