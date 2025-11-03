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

// Get teacher's assigned classes with students
$classes_query = "SELECT DISTINCT c.id, c.class_name, c.section, c.student_count, c.academic_year
    FROM teacher_subjects ts
    INNER JOIN subjects s ON ts.subject_id = s.id
    INNER JOIN classes c ON s.class_id = c.id
    WHERE ts.teacher_id = ? AND ts.status = 'active' AND c.status = 'active'
    ORDER BY c.class_name, c.section";

$stmt = $conn->prepare($classes_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$classes = $stmt->get_result();

$selected_class_id = $_GET['class_id'] ?? '';
$students = [];

if ($selected_class_id) {
    $students_query = "SELECT 
        s.*,
        COUNT(DISTINCT a.id) as total_attendance,
        SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count
    FROM students s
    LEFT JOIN attendance a ON s.id = a.student_id AND a.teacher_id = ?
    WHERE s.class_id = ? AND s.status = 'active'
    GROUP BY s.id
    ORDER BY s.roll_number";
    
    $students_stmt = $conn->prepare($students_query);
    $students_stmt->bind_param("ii", $teacher_id, $selected_class_id);
    $students_stmt->execute();
    $students = $students_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>My Classes - KPRCAS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/responsive.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
        .class-card {
            transition: transform 0.3s;
            cursor: pointer;
        }
        .class-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
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
            <a class="nav-link" href="take_attendance.php">
                <i class="fas fa-qrcode"></i> Take Attendance
            </a>
            <a class="nav-link" href="reports.php">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            <a class="nav-link active" href="my_classes.php">
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
            <h1><i class="fas fa-users text-primary"></i> My Classes</h1>
            <p class="text-muted">View students in your assigned classes</p>
        </div>

        <!-- Classes List -->
        <div class="row mb-4">
            <?php 
            $classes->data_seek(0);
            while ($class = $classes->fetch_assoc()): 
            ?>
            <div class="col-md-4 mb-3">
                <div class="card class-card" onclick="window.location.href='?class_id=<?php echo $class['id']; ?>'">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-users-class text-primary"></i>
                            <?php echo $class['class_name'] . ' ' . $class['section']; ?>
                        </h5>
                        <p class="card-text text-muted mb-2">
                            <i class="fas fa-calendar"></i> <?php echo $class['academic_year']; ?>
                        </p>
                        <h3 class="text-center text-primary"><?php echo $class['student_count']; ?></h3>
                        <p class="text-center text-muted mb-0">Students</p>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <?php if ($selected_class_id && count($students) > 0): ?>
        <!-- Selected Class Students -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> Students List
                    <?php 
                    // Get selected class name
                    $classes->data_seek(0);
                    while ($c = $classes->fetch_assoc()) {
                        if ($c['id'] == $selected_class_id) {
                            echo '- ' . $c['class_name'] . ' ' . $c['section'];
                            break;
                        }
                    }
                    ?>
                </h5>
            </div>
            <div class="card-body">
                <table id="studentsTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Date of Birth</th>
                            <th>Total Sessions</th>
                            <th>Present</th>
                            <th>Attendance %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): 
                            $attendance_percentage = $student['total_attendance'] > 0 
                                ? round(($student['present_count'] / $student['total_attendance']) * 100, 2) 
                                : 0;
                        ?>
                        <tr>
                            <td><?php echo $student['roll_number']; ?></td>
                            <td><?php echo $student['name']; ?></td>
                            <td><?php echo $student['email']; ?></td>
                            <td><?php echo $student['phone'] ?? '-'; ?></td>
                            <td><?php echo $student['date_of_birth'] ? date('d M Y', strtotime($student['date_of_birth'])) : '-'; ?></td>
                            <td class="text-center"><?php echo $student['total_attendance']; ?></td>
                            <td class="text-center text-success"><?php echo $student['present_count']; ?></td>
                            <td>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar <?php echo $attendance_percentage >= 75 ? 'bg-success' : ($attendance_percentage >= 50 ? 'bg-warning' : 'bg-danger'); ?>" 
                                         role="progressbar" 
                                         style="width: <?php echo $attendance_percentage; ?>%"
                                         aria-valuenow="<?php echo $attendance_percentage; ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        <?php echo $attendance_percentage; ?>%
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php elseif ($selected_class_id): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> No students found in this class.
        </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        <?php if ($selected_class_id && count($students) > 0): ?>
        $(document).ready(function() {
            $('#studentsTable').DataTable({
                pageLength: 25,
                order: [[0, 'asc']]
            });
        });
        <?php endif; ?>
    </script>
    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>
