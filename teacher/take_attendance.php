<?php
session_start();
require_once '../login/config/database.php';
require_once '../login/includes/phpmailer_functions.php';

// Check if user is logged in as teacher
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../login/login.php');
    exit();
}

$teacher_id = $_SESSION['user_id'];
$teacher_name = $_SESSION['user_name'];
$message = '';
$message_type = '';

// Get teacher's assigned subjects
$query = "SELECT s.id as subject_id, s.subject_code, s.subject_name, 
          c.id as class_id, c.class_name, c.section, c.student_count
          FROM teacher_subjects ts
          INNER JOIN subjects s ON ts.subject_id = s.id
          LEFT JOIN classes c ON s.class_id = c.id
          WHERE ts.teacher_id = ? AND ts.status = 'active' AND s.status = 'active'
          ORDER BY c.class_name, s.subject_name";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$assigned_subjects = $stmt->get_result();

// Pre-select if coming from dashboard
$selected_subject_id = $_GET['subject_id'] ?? '';
$selected_class_id = $_GET['class_id'] ?? '';

// Handle QR Code Generation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_qr'])) {
    $subject_id = intval($_POST['subject_id']);
    $class_id = intval($_POST['class_id']);
    $duration = intval($_POST['duration']); // minutes
    
    // Generate unique session code
    $session_code = 'ATT_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
    
    // Calculate expiry time
    $expires_at = date('Y-m-d H:i:s', strtotime("+{$duration} minutes"));
    $session_date = date('Y-m-d');
    $session_time = date('H:i:s');
    
    // Insert session
    $stmt = $conn->prepare("INSERT INTO attendance_sessions 
        (teacher_id, subject_id, class_id, session_code, session_date, session_time, duration_minutes, expires_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisssss", $teacher_id, $subject_id, $class_id, $session_code, $session_date, $session_time, $duration, $expires_at);
    
    if ($stmt->execute()) {
        $session_id = $stmt->insert_id;
        
        // Send QR code to students if requested
        if (isset($_POST['send_email'])) {
            $qr_url = "http://localhost/attendance/student/mark_attendance.php?code=" . $session_code;
            
            // Get all students in the class
            $students_query = "SELECT id, name, email FROM students WHERE class_id = ? AND status = 'active'";
            $students_stmt = $conn->prepare($students_query);
            $students_stmt->bind_param("i", $class_id);
            $students_stmt->execute();
            $students = $students_stmt->get_result();
            
            $sent_count = 0;
            $failed_count = 0;
            
            while ($student = $students->fetch_assoc()) {
                // Send email with QR code link
                $subject = "Attendance Alert - Scan QR Code Now!";
                $email_body = "
                <html>
                <body style='font-family: Arial, sans-serif;'>
                    <h2 style='color: #667eea;'>Attendance Session Started</h2>
                    <p>Dear {$student['name']},</p>
                    <p>Your teacher has started an attendance session.</p>
                    <p><strong>Subject:</strong> " . htmlspecialchars($_POST['subject_name']) . "</p>
                    <p><strong>Time:</strong> " . date('d M Y, h:i A') . "</p>
                    <p><strong>Valid for:</strong> {$duration} minutes</p>
                    <div style='margin: 30px 0; text-align: center;'>
                        <a href='{$qr_url}' style='background: #667eea; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            Mark Your Attendance
                        </a>
                    </div>
                    <p style='color: #666; font-size: 12px;'>Or copy this link: {$qr_url}</p>
                    <p style='color: #999; font-size: 11px;'>This link will expire at " . date('h:i A', strtotime($expires_at)) . "</p>
                    <script src="../assets/js/mobile-menu.js"></script>
</body>
                </html>";
                
                if (sendHTMLEmail($student['email'], $subject, $email_body)) {
                    $sent_count++;
                    // Log email sent
                    $log_stmt = $conn->prepare("INSERT INTO qr_email_logs (session_id, student_id, email, status) VALUES (?, ?, ?, 'sent')");
                    $log_stmt->bind_param("iis", $session_id, $student['id'], $student['email']);
                    $log_stmt->execute();
                } else {
                    $failed_count++;
                }
            }
            
            $message = "QR Code generated successfully! Emails sent: {$sent_count}, Failed: {$failed_count}";
            $message_type = 'success';
        } else {
            $message = "QR Code generated successfully!";
            $message_type = 'success';
        }
        
        // Redirect to QR display page
        header("Location: display_qr.php?session_id={$session_id}");
        exit();
    } else {
        $message = "Error generating QR code: " . $conn->error;
        $message_type = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Take Attendance - KPRCAS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/responsive.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            padding: 20px 0;
            z-index: 1000;
        }
        .sidebar .logo {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 25px;
            margin: 5px 15px;
            border-radius: 8px;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn-generate {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <h4><i class="fas fa-chalkboard-teacher"></i> Teacher Portal</h4>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link" href="index.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a class="nav-link active" href="take_attendance.php">
                <i class="fas fa-qrcode"></i> Take Attendance
            </a>
            <a class="nav-link" href="reports.php">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            <a class="nav-link" href="my_classes.php">
                <i class="fas fa-users"></i> My Classes
            </a>
            <hr style="border-color: rgba(255,255,255,0.1); margin: 10px 15px;">
            <a class="nav-link" href="../login/logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="mb-4">
            <h1><i class="fas fa-qrcode text-primary"></i> Take Attendance</h1>
            <p class="text-muted">Generate QR code for students to mark attendance</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-book"></i> Select Subject *</label>
                            <select name="subject_id" id="subject_id" class="form-select" required>
                                <option value="">-- Choose Subject --</option>
                                <?php 
                                $assigned_subjects->data_seek(0);
                                while ($subject = $assigned_subjects->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $subject['subject_id']; ?>" 
                                        data-class-id="<?php echo $subject['class_id']; ?>"
                                        data-subject-name="<?php echo htmlspecialchars($subject['subject_name']); ?>"
                                        <?php echo ($subject['subject_id'] == $selected_subject_id) ? 'selected' : ''; ?>>
                                    [<?php echo $subject['subject_code']; ?>] <?php echo $subject['subject_name']; ?> 
                                    - (<?php echo $subject['class_name'] . ' ' . $subject['section']; ?>)
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="fas fa-clock"></i> Duration (Minutes) *</label>
                            <select name="duration" class="form-select" required>
                                <option value="5">5 Minutes</option>
                                <option value="10" selected>10 Minutes</option>
                                <option value="15">15 Minutes</option>
                                <option value="20">20 Minutes</option>
                                <option value="30">30 Minutes</option>
                            </select>
                        </div>
                    </div>

                    <input type="hidden" name="class_id" id="class_id" value="">
                    <input type="hidden" name="subject_name" id="subject_name" value="">

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="send_email" id="send_email" checked>
                            <label class="form-check-label" for="send_email">
                                <i class="fas fa-envelope"></i> Send QR code link to all students via email
                            </label>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Note:</strong> Students will receive an email with a link to mark attendance. 
                        They will need to verify via OTP before marking attendance.
                    </div>

                    <button type="submit" name="generate_qr" class="btn btn-generate btn-lg">
                        <i class="fas fa-qrcode"></i> Generate QR Code & Start Session
                    </button>
                </form>
            </div>
        </div>

        <!-- Active Sessions -->
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-list"></i> Active Sessions Today</h5>
            </div>
            <div class="card-body">
                <?php
                $today = date('Y-m-d');
                $active_query = "SELECT ats.*, s.subject_code, s.subject_name, c.class_name, c.section
                    FROM attendance_sessions ats
                    INNER JOIN subjects s ON ats.subject_id = s.id
                    INNER JOIN classes c ON ats.class_id = c.id
                    WHERE ats.teacher_id = ? AND ats.session_date = ? AND ats.status = 'active'
                    ORDER BY ats.created_at DESC";
                
                $stmt = $conn->prepare($active_query);
                $stmt->bind_param("is", $teacher_id, $today);
                $stmt->execute();
                $active_sessions = $stmt->get_result();
                
                if ($active_sessions->num_rows > 0):
                ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Class</th>
                            <th>Started</th>
                            <th>Expires</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($session = $active_sessions->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $session['subject_code'] . ' - ' . $session['subject_name']; ?></td>
                            <td><?php echo $session['class_name'] . ' ' . $session['section']; ?></td>
                            <td><?php echo date('h:i A', strtotime($session['session_time'])); ?></td>
                            <td><?php echo date('h:i A', strtotime($session['expires_at'])); ?></td>
                            <td>
                                <a href="display_qr.php?session_id=<?php echo $session['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-qrcode"></i> View
                                </a>
                                <a href="close_session.php?session_id=<?php echo $session['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Close this session?');">
                                    <i class="fas fa-times"></i> Close
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-muted mb-0">No active sessions today.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('subject_id').addEventListener('change', function() {
            var selected = this.options[this.selectedIndex];
            document.getElementById('class_id').value = selected.getAttribute('data-class-id');
            document.getElementById('subject_name').value = selected.getAttribute('data-subject-name');
        });
        
        // Set initial values if pre-selected
        if (document.getElementById('subject_id').value) {
            var selected = document.getElementById('subject_id').options[document.getElementById('subject_id').selectedIndex];
            document.getElementById('class_id').value = selected.getAttribute('data-class-id');
            document.getElementById('subject_name').value = selected.getAttribute('data-subject-name');
        }
    </script>
    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>
