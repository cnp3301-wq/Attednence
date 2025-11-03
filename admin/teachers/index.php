<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
checkAdminAuth();

// Get all teachers
$query = "SELECT * FROM users WHERE user_type = 'teacher' ORDER BY name ASC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Manage Teachers - KPRCAS Admin</title>
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
        .sidebar .logo h4 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 25px;
            margin: 5px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.2);
            color: white;
            transform: translateX(5px);
        }
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
            font-weight: 600;
        }
        .sidebar .nav-link i {
            width: 25px;
            margin-right: 10px;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        .page-header {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        .page-header h1 {
            margin: 0;
            font-size: 1.8rem;
            color: #333;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 15px 20px;
        }
        .badge-success {
            background-color: #28a745;
        }
        .badge-danger {
            background-color: #dc3545;
        }
        .btn-action {
            padding: 5px 10px;
            margin: 0 2px;
            font-size: 0.85rem;
        }
        table.dataTable thead th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .password-cell {
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        .password-toggle {
            cursor: pointer;
            color: #007bff;
            text-decoration: underline;
        }
        .credential-box {
            background: #f8f9fa;
            padding: 8px;
            border-radius: 5px;
            border-left: 3px solid #667eea;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <h4><i class="fas fa-graduation-cap"></i> KPRCAS Admin</h4>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link" href="../index.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a class="nav-link" href="../classes/index.php">
                <i class="fas fa-chalkboard"></i> Manage Classes
            </a>
            <a class="nav-link" href="../students/index.php">
                <i class="fas fa-user-graduate"></i> Manage Students
            </a>
            <a class="nav-link active" href="index.php">
                <i class="fas fa-chalkboard-teacher"></i> Manage Teachers
            </a>
            <a class="nav-link" href="../subjects/index.php">
                <i class="fas fa-book"></i> Manage Subjects
            </a>
            <a class="nav-link" href="../assignments/index.php">
                <i class="fas fa-user-tag"></i> Assign Subjects
            </a>
            <hr style="border-color: rgba(255,255,255,0.1); margin: 10px 15px;">
            <a class="nav-link" href="../../login/logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-chalkboard-teacher text-primary"></i> Manage Teachers</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Teachers</li>
                        </ol>
                    </nav>
                </div>
                <a href="add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Teacher
                </a>
            </div>
        </div>

        <!-- Messages -->
        <?php echo showMessage(); ?>

        <!-- Teachers Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list"></i> All Teachers</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> <strong>Note:</strong> Teachers can login using their username and password. Click "Show" to reveal passwords.
                </div>
                <div class="table-responsive">
                    <table id="teachersTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Username</th>
                                <th>Password</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($teacher = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><i class="fas fa-user-tie text-muted"></i> 
                                    <?php echo htmlspecialchars($teacher['name']); ?></strong>
                                </td>
                                <td>
                                    <i class="fas fa-envelope text-muted"></i>
                                    <?php echo htmlspecialchars($teacher['email']); ?>
                                </td>
                                <td>
                                    <div class="credential-box">
                                        <i class="fas fa-user text-primary"></i>
                                        <strong><?php echo htmlspecialchars($teacher['username']); ?></strong>
                                    </div>
                                </td>
                                <td class="password-cell">
                                    <div class="credential-box">
                                        <i class="fas fa-key text-warning"></i>
                                        <span class="password-hidden" data-password="<?php echo htmlspecialchars($teacher['plain_password'] ?? '••••••••'); ?>">
                                            ••••••••
                                        </span>
                                        <span class="password-toggle ms-2" onclick="togglePassword(this)">
                                            <i class="fas fa-eye"></i> Show
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($teacher['status'] == 'active'): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo date('d M Y', strtotime($teacher['created_at'])); ?>
                                </td>
                                <td>
                                    <a href="edit.php?id=<?php echo $teacher['id']; ?>" 
                                       class="btn btn-sm btn-warning btn-action" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="reset_password.php?id=<?php echo $teacher['id']; ?>" 
                                       class="btn btn-sm btn-info btn-action" 
                                       title="Reset Password">
                                        <i class="fas fa-key"></i> Reset
                                    </a>
                                    <a href="delete.php?id=<?php echo $teacher['id']; ?>" 
                                       class="btn btn-sm btn-danger btn-action" 
                                       title="Delete"
                                       onclick="return confirm('Are you sure you want to delete this teacher? This will also remove their subject assignments.');">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#teachersTable').DataTable({
                "pageLength": 25,
                "order": [[0, "asc"]],
                "language": {
                    "search": "Search Teachers:",
                    "lengthMenu": "Show _MENU_ teachers per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ teachers",
                    "infoEmpty": "No teachers found",
                    "infoFiltered": "(filtered from _MAX_ total teachers)",
                    "zeroRecords": "No matching teachers found"
                }
            });
        });

        function togglePassword(element) {
            const passwordSpan = element.previousElementSibling;
            const icon = element.querySelector('i');
            const isHidden = passwordSpan.textContent === '••••••••';
            
            if (isHidden) {
                passwordSpan.textContent = passwordSpan.dataset.password;
                icon.className = 'fas fa-eye-slash';
                element.innerHTML = '<i class="fas fa-eye-slash"></i> Hide';
            } else {
                passwordSpan.textContent = '••••••••';
                icon.className = 'fas fa-eye';
                element.innerHTML = '<i class="fas fa-eye"></i> Show';
            }
        }
    </script>
    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>
