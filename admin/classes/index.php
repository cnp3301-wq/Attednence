<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
checkAdminAuth();

// Get all classes
$stmt = $conn->query("SELECT * FROM classes ORDER BY academic_year DESC, class_name, section");
$classes = $stmt->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Manage Classes - KPRCAS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/responsive.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            padding: 20px 0;
        }
        .sidebar .logo { text-align: center; padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 25px; margin: 5px 15px; border-radius: 8px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: rgba(255,255,255,0.2); color: white; }
        .sidebar .nav-link i { width: 25px; }
        .main-content { margin-left: 250px; padding: 30px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <h4><i class="fas fa-graduation-cap"></i> KPRCAS Admin</h4>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link" href="../index.php"><i class="fas fa-home"></i> Dashboard</a>
            <a class="nav-link active" href="index.php"><i class="fas fa-chalkboard"></i> Manage Classes</a>
            <a class="nav-link" href="../students/index.php"><i class="fas fa-user-graduate"></i> Manage Students</a>
            <a class="nav-link" href="../teachers/index.php"><i class="fas fa-chalkboard-teacher"></i> Manage Teachers</a>
            <a class="nav-link" href="../subjects/index.php"><i class="fas fa-book"></i> Manage Subjects</a>
            <a class="nav-link" href="../assignments/index.php"><i class="fas fa-user-tag"></i> Assign Subjects</a>
            <hr style="border-color: rgba(255,255,255,0.1);">
            <a class="nav-link" href="../../login/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-chalkboard text-primary"></i> Manage Classes</h1>
            <a href="add.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add New Class</a>
        </div>

        <?php echo showMessage(); ?>

        <div class="card">
            <div class="card-body">
                <table id="classesTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Class Name</th>
                            <th>Section</th>
                            <th>Academic Year</th>
                            <th>Student Count</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $class): ?>
                        <tr>
                            <td><?php echo $class['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($class['class_name']); ?></strong></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($class['section']); ?></span></td>
                            <td><?php echo htmlspecialchars($class['academic_year']); ?></td>
                            <td><span class="badge bg-info"><?php echo $class['student_count']; ?> Students</span></td>
                            <td>
                                <?php if ($class['status'] == 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit.php?id=<?php echo $class['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete.php?id=<?php echo $class['id']; ?>" class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this class?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#classesTable').DataTable({
                order: [[0, 'desc']]
            });
        });
    </script>
    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>
