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

// Get session details
$query = "SELECT ats.*, s.subject_code, s.subject_name, c.class_name, c.section, c.student_count
    FROM attendance_sessions ats
    INNER JOIN subjects s ON ats.subject_id = s.id
    INNER JOIN classes c ON ats.class_id = c.id
    WHERE ats.id = ? AND ats.teacher_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $session_id, $teacher_id);
$stmt->execute();
$session = $stmt->get_result()->fetch_assoc();

if (!$session) {
    header('Location: take_attendance.php');
    exit();
}

// Get attendance count
$count_query = "SELECT 
    COUNT(*) as total_marked,
    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count
    FROM attendance WHERE session_id = ?";
$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param("i", $session_id);
$count_stmt->execute();
$counts = $count_stmt->get_result()->fetch_assoc();

$qr_url = "http://localhost/attendance/student/mark_attendance.php?code=" . $session['session_code'];
$time_remaining = strtotime($session['expires_at']) - time();
$is_expired = $time_remaining <= 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Attendance QR Code - KPRCAS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/responsive.css" rel="stylesheet">
    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px;
        }
        .qr-container {
            max-width: 900px;
            margin: 0 auto;
        }
        .card {
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        #qrcode {
            text-align: center;
            padding: 30px;
        }
        #qrcode img {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .timer {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
        }
        .expired {
            color: #dc3545;
        }
        .stat-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="qr-container">
        <div class="card">
            <div class="card-header bg-primary text-white text-center">
                <h3><i class="fas fa-qrcode"></i> Attendance QR Code</h3>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5><i class="fas fa-book"></i> Subject Details</h5>
                        <p class="mb-1"><strong>Code:</strong> <?php echo $session['subject_code']; ?></p>
                        <p class="mb-1"><strong>Name:</strong> <?php echo $session['subject_name']; ?></p>
                        <p class="mb-1"><strong>Class:</strong> <?php echo $session['class_name'] . ' ' . $session['section']; ?></p>
                        <p class="mb-1"><strong>Students:</strong> <?php echo $session['student_count']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fas fa-clock"></i> Session Info</h5>
                        <p class="mb-1"><strong>Started:</strong> <?php echo date('h:i A', strtotime($session['session_time'])); ?></p>
                        <p class="mb-1"><strong>Duration:</strong> <?php echo $session['duration_minutes']; ?> minutes</p>
                        <p class="mb-1"><strong>Expires:</strong> <?php echo date('h:i A', strtotime($session['expires_at'])); ?></p>
                        <?php if (!$is_expired): ?>
                        <div class="timer" id="timer"><?php echo gmdate('i:s', $time_remaining); ?></div>
                        <?php else: ?>
                        <div class="timer expired">EXPIRED</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <h2 class="text-primary"><?php echo $counts['total_marked']; ?></h2>
                            <p class="mb-0">Total Marked</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <h2 class="text-success"><?php echo $counts['present_count']; ?></h2>
                            <p class="mb-0">Present</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <h2 class="text-danger"><?php echo $session['student_count'] - $counts['total_marked']; ?></h2>
                            <p class="mb-0">Not Marked</p>
                        </div>
                    </div>
                </div>

                <div id="qrcode"></div>

                <div class="text-center mt-4">
                    <p class="text-muted">Scan this QR code or use the link below:</p>
                    <input type="text" class="form-control text-center mb-3" value="<?php echo $qr_url; ?>" readonly>
                    <button class="btn btn-primary" onclick="copyLink()">
                        <i class="fas fa-copy"></i> Copy Link
                    </button>
                    <a href="take_attendance.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <?php if (!$is_expired): ?>
                    <a href="close_session.php?session_id=<?php echo $session_id; ?>" 
                       class="btn btn-danger"
                       onclick="return confirm('Are you sure you want to close this session?');">
                        <i class="fas fa-times"></i> Close Session
                    </a>
                    <?php endif; ?>
                    <button class="btn btn-success" onclick="location.reload()">
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Generate QR Code
        var qrcode = new QRCode(document.getElementById("qrcode"), {
            text: "<?php echo $qr_url; ?>",
            width: 300,
            height: 300,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        function copyLink() {
            var copyText = document.querySelector('input[type="text"]');
            copyText.select();
            document.execCommand("copy");
            alert("Link copied to clipboard!");
        }

        <?php if (!$is_expired): ?>
        // Countdown timer
        var timeRemaining = <?php echo $time_remaining; ?>;
        setInterval(function() {
            timeRemaining--;
            if (timeRemaining <= 0) {
                document.getElementById('timer').innerHTML = "EXPIRED";
                document.getElementById('timer').classList.add('expired');
                clearInterval();
                setTimeout(() => location.reload(), 2000);
            } else {
                var minutes = Math.floor(timeRemaining / 60);
                var seconds = timeRemaining % 60;
                document.getElementById('timer').innerHTML = 
                    String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
            }
        }, 1000);

        // Auto refresh attendance count every 5 seconds
        setInterval(function() {
            fetch('get_attendance_count.php?session_id=<?php echo $session_id; ?>')
                .then(response => response.json())
                .then(data => {
                    // Update counts without full page reload
                    if (data.success) {
                        location.reload();
                    }
                });
        }, 5000);
        <?php endif; ?>
    </script>
    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>
