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

// Get teacher's assigned subjects for filter
$subjects_query = "SELECT s.id, s.subject_code, s.subject_name, c.id as class_id, c.class_name, c.section
    FROM teacher_subjects ts
    INNER JOIN subjects s ON ts.subject_id = s.id
    LEFT JOIN classes c ON s.class_id = c.id
    WHERE ts.teacher_id = ? AND ts.status = 'active'
    ORDER BY c.class_name, s.subject_name";

$stmt = $conn->prepare($subjects_query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$assigned_subjects = $stmt->get_result();

// Filter parameters
$filter_subject = $_GET['subject_id'] ?? '';
$filter_class = $_GET['class_id'] ?? '';
$filter_date_from = $_GET['date_from'] ?? date('Y-m-01'); // First day of current month
$filter_date_to = $_GET['date_to'] ?? date('Y-m-d'); // Today
$filter_status = $_GET['status'] ?? '';

// Build attendance query
$attendance_data = [];
$statistics = [
    'total_sessions' => 0,
    'total_students' => 0,
    'present_count' => 0,
    'absent_count' => 0,
    'attendance_percentage' => 0
];

if ($filter_subject && $filter_class) {
    $query = "SELECT 
        st.id as student_id,
        st.roll_number,
        st.name as student_name,
        st.email,
        a.attendance_date,
        a.attendance_time,
        a.status,
        a.marked_via,
        ats.session_time
    FROM students st
    LEFT JOIN attendance a ON st.id = a.student_id 
        AND a.subject_id = ? 
        AND a.attendance_date BETWEEN ? AND ?
        " . ($filter_status ? "AND a.status = ?" : "") . "
    LEFT JOIN attendance_sessions ats ON a.session_id = ats.id
    WHERE st.class_id = ? AND st.status = 'active'
    ORDER BY st.roll_number, a.attendance_date DESC";
    
    $types = "iss";
    $params = [$filter_subject, $filter_date_from, $filter_date_to];
    
    if ($filter_status) {
        $types .= "s";
        $params[] = $filter_status;
    }
    
    $types .= "i";
    $params[] = $filter_class;
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $attendance_data[] = $row;
    }
    
    // Calculate statistics
    $stats_query = "SELECT 
        COUNT(DISTINCT ats.id) as total_sessions,
        COUNT(DISTINCT a.student_id) as unique_students,
        SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
        SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
        COUNT(a.id) as total_records
    FROM attendance_sessions ats
    LEFT JOIN attendance a ON ats.id = a.session_id
    WHERE ats.teacher_id = ? 
        AND ats.subject_id = ? 
        AND ats.class_id = ?
        AND ats.session_date BETWEEN ? AND ?";
    
    $stats_stmt = $conn->prepare($stats_query);
    $stats_stmt->bind_param("iiiss", $teacher_id, $filter_subject, $filter_class, $filter_date_from, $filter_date_to);
    $stats_stmt->execute();
    $statistics = $stats_stmt->get_result()->fetch_assoc();
    
    if ($statistics['total_records'] > 0) {
        $statistics['attendance_percentage'] = round(($statistics['present_count'] / $statistics['total_records']) * 100, 2);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Attendance Reports - KPRCAS</title>
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
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            font-size: 2rem;
            font-weight: bold;
            margin: 10px 0;
        }
        .badge-present {
            background-color: #28a745;
        }
        .badge-absent {
            background-color: #dc3545;
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
            <a class="nav-link active" href="reports.php">
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
            <h1><i class="fas fa-chart-bar text-primary"></i> Attendance Reports</h1>
            <p class="text-muted">View and analyze attendance records</p>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Subject</label>
                            <select name="subject_id" id="subject_id" class="form-select" required>
                                <option value="">-- Select Subject --</option>
                                <?php 
                                $assigned_subjects->data_seek(0);
                                while ($subject = $assigned_subjects->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $subject['id']; ?>" 
                                        data-class-id="<?php echo $subject['class_id']; ?>"
                                        <?php echo ($subject['id'] == $filter_subject) ? 'selected' : ''; ?>>
                                    <?php echo $subject['subject_code'] . ' - ' . $subject['subject_name']; ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="<?php echo $filter_date_from; ?>" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="<?php echo $filter_date_to; ?>" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All</option>
                                <option value="present" <?php echo ($filter_status == 'present') ? 'selected' : ''; ?>>Present</option>
                                <option value="absent" <?php echo ($filter_status == 'absent') ? 'selected' : ''; ?>>Absent</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Generate Report
                                </button>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="class_id" id="class_id" value="<?php echo $filter_class; ?>">
                </form>
            </div>
        </div>

        <?php if ($filter_subject && $filter_class): ?>
        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-calendar-check fa-2x text-primary"></i>
                    <h3><?php echo $statistics['total_sessions'] ?? 0; ?></h3>
                    <p class="text-muted mb-0">Total Sessions</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-check-circle fa-2x text-success"></i>
                    <h3><?php echo $statistics['present_count'] ?? 0; ?></h3>
                    <p class="text-muted mb-0">Present</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-times-circle fa-2x text-danger"></i>
                    <h3><?php echo $statistics['absent_count'] ?? 0; ?></h3>
                    <p class="text-muted mb-0">Absent</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-percentage fa-2x text-info"></i>
                    <h3><?php echo $statistics['attendance_percentage'] ?? 0; ?>%</h3>
                    <p class="text-muted mb-0">Attendance Rate</p>
                </div>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-table"></i> Attendance Records</h5>
                <button onclick="exportToExcel()" class="btn btn-light btn-sm">
                    <i class="fas fa-download"></i> Export to Excel
                </button>
            </div>
            <div class="card-body">
                <?php if (count($attendance_data) > 0): ?>
                <table id="attendanceTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Marked Via</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attendance_data as $record): ?>
                        <tr>
                            <td><?php echo $record['roll_number']; ?></td>
                            <td><?php echo $record['student_name']; ?></td>
                            <td><?php echo $record['email']; ?></td>
                            <td><?php echo $record['attendance_date'] ? date('d M Y', strtotime($record['attendance_date'])) : '-'; ?></td>
                            <td><?php echo $record['attendance_time'] ?? '-'; ?></td>
                            <td>
                                <?php if ($record['status']): ?>
                                <span class="badge badge-<?php echo $record['status']; ?>">
                                    <?php echo strtoupper($record['status']); ?>
                                </span>
                                <?php else: ?>
                                <span class="badge bg-secondary">NO RECORD</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $record['marked_via'] ? ucfirst(str_replace('_', ' ', $record['marked_via'])) : '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-muted text-center py-4">No attendance records found for the selected criteria.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Please select a subject and date range to view attendance reports.
        </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/table2excel@1.0.4/dist/table2excel.min.js"></script>
    
    <script>
        // Auto-set class_id when subject is selected
        document.getElementById('subject_id').addEventListener('change', function() {
            var selected = this.options[this.selectedIndex];
            document.getElementById('class_id').value = selected.getAttribute('data-class-id');
        });
        
        // Initialize DataTable
        <?php if (count($attendance_data) > 0): ?>
        $(document).ready(function() {
            $('#attendanceTable').DataTable({
                pageLength: 25,
                order: [[3, 'desc']]
            });
        });
        <?php endif; ?>
        
        // Export to Excel
        function exportToExcel() {
            var table2excel = new Table2Excel();
            table2excel.export(document.getElementById('attendanceTable'), 'Attendance_Report');
        }
    </script>
    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>
