<?php
session_start();
require_once '../login/config/database.php';
require_once '../login/includes/phpmailer_functions.php';

$session_code = $_GET['code'] ?? '';
$message = '';
$message_type = '';
$session_data = null;

// Validate session code
if ($session_code) {
    $query = "SELECT ats.*, s.subject_name, c.class_name, c.section
        FROM attendance_sessions ats
        INNER JOIN subjects s ON ats.subject_id = s.id
        INNER JOIN classes c ON ats.class_id = c.id
        WHERE ats.session_code = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $session_code);
    $stmt->execute();
    $session_data = $stmt->get_result()->fetch_assoc();
    
    if (!$session_data) {
        $message = "Invalid session code!";
        $message_type = "danger";
    } elseif ($session_data['status'] == 'expired' || $session_data['status'] == 'closed') {
        $message = "This attendance session has been closed.";
        $message_type = "warning";
    } elseif (strtotime($session_data['expires_at']) < time()) {
        $message = "This attendance session has expired!";
        $message_type = "warning";
        
        // Auto-update status
        $update_stmt = $conn->prepare("UPDATE attendance_sessions SET status = 'expired' WHERE id = ?");
        $update_stmt->bind_param("i", $session_data['id']);
        $update_stmt->execute();
    }
}

// Handle email submission and OTP sending
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_otp'])) {
    $email = trim($_POST['email']);
    
    // Verify student exists and belongs to the class
    $student_query = "SELECT id, name, email FROM students WHERE email = ? AND class_id = ? AND status = 'active'";
    $student_stmt = $conn->prepare($student_query);
    $student_stmt->bind_param("si", $email, $session_data['id']);
    $student_stmt->execute();
    $student = $student_stmt->get_result()->fetch_assoc();
    
    if (!$student) {
        $message = "Email not found or you're not enrolled in this class!";
        $message_type = "danger";
    } else {
        // Check if already marked
        $check_query = "SELECT id FROM attendance WHERE session_id = ? AND student_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ii", $session_data['id'], $student['id']);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $message = "You have already marked attendance for this session!";
            $message_type = "warning";
        } else {
            // Generate OTP
            $otp = sprintf("%06d", mt_rand(0, 999999));
            $_SESSION['attendance_otp'] = $otp;
            $_SESSION['attendance_email'] = $email;
            $_SESSION['attendance_student_id'] = $student['id'];
            $_SESSION['attendance_session_id'] = $session_data['id'];
            $_SESSION['otp_time'] = time();
            
            // Send OTP email
            $subject = "Attendance Verification OTP";
            $email_body = "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2 style='color: #667eea;'>Attendance Verification</h2>
                <p>Dear {$student['name']},</p>
                <p>Your One-Time Password (OTP) for marking attendance is:</p>
                <div style='background: #f8f9fa; padding: 20px; text-align: center; margin: 20px 0;'>
                    <h1 style='color: #667eea; letter-spacing: 5px;'>{$otp}</h1>
                </div>
                <p><strong>Subject:</strong> {$session_data['subject_name']}</p>
                <p><strong>Class:</strong> {$session_data['class_name']} {$session_data['section']}</p>
                <p>This OTP is valid for 5 minutes.</p>
                <p style='color: #999; font-size: 11px;'>If you didn't request this, please ignore this email.</p>
                <script src="../assets/js/mobile-menu.js"></script>
</body>
            </html>";
            
            if (sendHTMLEmail($email, $subject, $email_body)) {
                header('Location: verify_attendance.php?code=' . $session_code);
                exit();
            } else {
                $message = "Failed to send OTP. Please try again.";
                $message_type = "danger";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Mark Attendance - KPRCAS</title>
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
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo img {
            width: 100px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-body p-5">
            <div class="logo">
                <i class="fas fa-graduation-cap fa-4x text-primary"></i>
                <h3 class="mt-3">KPRCAS Attendance System</h3>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <i class="fas fa-exclamation-circle"></i> <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <?php if ($session_data && $message_type != 'warning'): ?>
            <div class="mb-4">
                <h5><i class="fas fa-info-circle"></i> Session Details</h5>
                <p class="mb-1"><strong>Subject:</strong> <?php echo $session_data['subject_name']; ?></p>
                <p class="mb-1"><strong>Class:</strong> <?php echo $session_data['class_name'] . ' ' . $session_data['section']; ?></p>
                <p class="mb-1"><strong>Date:</strong> <?php echo date('d M Y'); ?></p>
            </div>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label"><i class="fas fa-envelope"></i> Your Email *</label>
                    <input type="email" name="email" class="form-control form-control-lg" 
                           placeholder="Enter your registered email" required>
                    <small class="text-muted">We'll send you an OTP to verify your identity</small>
                </div>

                <button type="submit" name="send_otp" class="btn btn-primary btn-lg w-100">
                    <i class="fas fa-paper-plane"></i> Send OTP
                </button>
            </form>
            <?php else: ?>
            <div class="text-center">
                <a href="../login/login.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>
