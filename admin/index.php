<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';
checkAdminAuth();

$admin = getAdminInfo();

// Get statistics
$stmt = $conn->query("SELECT COUNT(*) as count FROM classes WHERE status = 'active'");
$total_classes = $stmt->fetch_assoc()['count'];

$stmt = $conn->query("SELECT COUNT(*) as count FROM students WHERE status = 'active'");
$total_students = $stmt->fetch_assoc()['count'];

$stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'teacher' AND status = 'active'");
$total_teachers = $stmt->fetch_assoc()['count'];

$stmt = $conn->query("SELECT COUNT(*) as count FROM subjects WHERE status = 'active'");
$total_subjects = $stmt->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Admin Dashboard - KPRCAS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/responsive.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background: var(--primary-gradient);
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
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .sidebar .nav-link i {
            width: 25px;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        .stat-card {
            border-radius: 15px;
            padding: 25px;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card .icon {
            font-size: 3rem;
            opacity: 0.3;
        }
        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: 600;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .page-header {
            margin-bottom: 30px;
        }
        .page-header h1 {
            color: #333;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <h4><i class="fas fa-graduation-cap"></i> KPRCAS Admin</h4>
            <small><?php echo htmlspecialchars($admin['name']); ?></small>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link active" href="index.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a class="nav-link" href="classes/index.php">
                <i class="fas fa-chalkboard"></i> Manage Classes
            </a>
            <a class="nav-link" href="students/index.php">
                <i class="fas fa-user-graduate"></i> Manage Students
            </a>
            <a class="nav-link" href="teachers/index.php">
                <i class="fas fa-chalkboard-teacher"></i> Manage Teachers
            </a>
            <a class="nav-link" href="subjects/index.php">
                <i class="fas fa-book"></i> Manage Subjects
            </a>
            <a class="nav-link" href="assignments/index.php">
                <i class="fas fa-user-tag"></i> Assign Subjects
            </a>
            <hr style="border-color: rgba(255,255,255,0.1);">
            <a class="nav-link" href="../login/logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1>Dashboard Overview</h1>
            <p class="text-muted">Welcome back, <?php echo htmlspecialchars($admin['name']); ?>!</p>
        </div>

        <?php echo showMessage(); ?>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1">Total Classes</p>
                            <h3 class="mb-0"><?php echo $total_classes; ?></h3>
                        </div>
                        <i class="fas fa-chalkboard icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1">Total Students</p>
                            <h3 class="mb-0"><?php echo $total_students; ?></h3>
                        </div>
                        <i class="fas fa-user-graduate icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1">Total Teachers</p>
                            <h3 class="mb-0"><?php echo $total_teachers; ?></h3>
                        </div>
                        <i class="fas fa-chalkboard-teacher icon"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1">Total Subjects</p>
                            <h3 class="mb-0"><?php echo $total_subjects; ?></h3>
                        </div>
                        <i class="fas fa-book icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-plus-circle text-primary"></i> Quick Actions</h5>
                        <div class="list-group list-group-flush">
                            <a href="classes/add.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-chalkboard text-primary"></i> Add New Class
                            </a>
                            <a href="students/add.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-user-graduate text-success"></i> Add New Student
                            </a>
                            <a href="teachers/add.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-chalkboard-teacher text-info"></i> Add New Teacher
                            </a>
                            <a href="subjects/add.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-book text-warning"></i> Add New Subject
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-info-circle text-info"></i> Recent Activity</h5>
                        <p class="text-muted">System is running smoothly</p>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check-circle text-success"></i> Database connected</li>
                            <li><i class="fas fa-check-circle text-success"></i> Email configured</li>
                            <li><i class="fas fa-check-circle text-success"></i> All modules active</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>
