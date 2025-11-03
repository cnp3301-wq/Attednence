<?php
session_start();
require_once '../login/config/database.php';

// Check if user is logged in as teacher
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'teacher') {
    header('Location: ../login/login.php');
    exit();
}

$teacher_id = $_SESSION['user_id'];
$teacher_name = $_SESSION['user_name'];

// Get teacher's assigned subjects with class information
$query = "SELECT s.id as subject_id, s.subject_code, s.subject_name, 
          c.id as class_id, c.class_name, c.section, c.student_count,
          ts.id as assignment_id
          FROM teacher_subjects ts
          INNER JOIN subjects s ON ts.subject_id = s.id
          LEFT JOIN classes c ON s.class_id = c.id
          WHERE ts.teacher_id = ? AND ts.status = 'active' AND s.status = 'active'
          ORDER BY c.class_name, s.subject_name";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$assigned_subjects = $stmt->get_result();

// Get today's attendance statistics
$today = date('Y-m-d');
$stats_query = "SELECT 
    COUNT(DISTINCT a.class_id) as classes_today,
    COUNT(DISTINCT a.id) as total_present,
    (SELECT COUNT(*) FROM students WHERE status = 'active') as total_students
    FROM attendance a
    WHERE a.teacher_id = ? AND DATE(a.attendance_date) = ?";

$stmt = $conn->prepare($stats_query);
$stmt->bind_param("is", $teacher_id, $today);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Teacher Dashboard - KPRCAS</title>
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
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .stat-card .icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .subject-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        .subject-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .btn-take-attendance {
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
            <p class="mb-0 small"><?php echo htmlspecialchars($teacher_name); ?></p>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link active" href="index.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a class="nav-link" href="take_attendance.php">
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
            <h1><i class="fas fa-tachometer-alt text-primary"></i> Teacher Dashboard</h1>
            <p class="text-muted">Welcome back, <?php echo htmlspecialchars($teacher_name); ?>!</p>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card text-center">
                    <div class="icon text-primary">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3><?php echo $assigned_subjects->num_rows; ?></h3>
                    <p class="text-muted mb-0">Assigned Subjects</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card text-center">
                    <div class="icon text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3><?php echo $stats['total_present']; ?></h3>
                    <p class="text-muted mb-0">Present Today</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card text-center">
                    <div class="icon text-info">
                        <i class="fas fa-chalkboard"></i>
                    </div>
                    <h3><?php echo $stats['classes_today']; ?></h3>
                    <p class="text-muted mb-0">Classes Today</p>
                </div>
            </div>
        </div>

        <!-- Quick Action -->
        <div class="mb-4">
            <a href="take_attendance.php" class="btn btn-take-attendance btn-lg">
                <i class="fas fa-qrcode"></i> Take Attendance Now
            </a>
        </div>

        <!-- Assigned Subjects -->
        <h4 class="mb-3">My Assigned Subjects</h4>
        <div class="row">
            <?php 
            $assigned_subjects->data_seek(0);
            if ($assigned_subjects->num_rows > 0):
                while ($subject = $assigned_subjects->fetch_assoc()): 
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="subject-card">
                    <h5 class="text-primary"><?php echo htmlspecialchars($subject['subject_code']); ?></h5>
                    <h6><?php echo htmlspecialchars($subject['subject_name']); ?></h6>
                    <hr>
                    <p class="mb-2">
                        <i class="fas fa-users text-muted"></i> 
                        <strong>Class:</strong> <?php echo htmlspecialchars($subject['class_name'] . ' - ' . $subject['section']); ?>
                    </p>
                    <p class="mb-3">
                        <i class="fas fa-user-graduate text-muted"></i> 
                        <strong>Students:</strong> <?php echo $subject['student_count']; ?>
                    </p>
                    <a href="take_attendance.php?subject_id=<?php echo $subject['subject_id']; ?>&class_id=<?php echo $subject['class_id']; ?>" 
                       class="btn btn-sm btn-primary w-100">
                        <i class="fas fa-qrcode"></i> Take Attendance
                    </a>
                </div>
            </div>
            <?php 
                endwhile;
            else:
            ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No subjects assigned yet. Please contact the administrator.
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>
