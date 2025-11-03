<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
checkAdminAuth();

// Get all current assignments
$query = "SELECT ts.id, u.name as teacher_name, u.id as teacher_id,
          s.subject_code, s.subject_name, s.id as subject_id,
          c.class_name, c.section
          FROM teacher_subjects ts
          INNER JOIN users u ON ts.teacher_id = u.id
          INNER JOIN subjects s ON ts.subject_id = s.id
          LEFT JOIN classes c ON s.class_id = c.id
          ORDER BY u.name, c.class_name, s.subject_name";
$assignments = $conn->query($query);

// Get all active teachers
$teachers_query = "SELECT * FROM users WHERE user_type = 'teacher' AND status = 'active' ORDER BY name";
$teachers = $conn->query($teachers_query);

// Get all active subjects
$subjects_query = "SELECT s.*, c.class_name, c.section 
                   FROM subjects s 
                   LEFT JOIN classes c ON s.class_id = c.id 
                   WHERE s.status = 'active' 
                   ORDER BY c.class_name, s.subject_name";
$subjects = $conn->query($subjects_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Subject Assignments - KPRCAS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/responsive.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
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
        .badge-info {
            background-color: #17a2b8;
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
        .assignment-form {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 25px;
            border-radius: 15px;
            border: 2px solid #667eea;
        }
        .select2-container--bootstrap-5 .select2-selection {
            min-height: 45px;
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
            <a class="nav-link" href="../teachers/index.php">
                <i class="fas fa-chalkboard-teacher"></i> Manage Teachers
            </a>
            <a class="nav-link" href="../subjects/index.php">
                <i class="fas fa-book"></i> Manage Subjects
            </a>
            <a class="nav-link active" href="index.php">
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
            <h1><i class="fas fa-user-tag text-primary"></i> Subject Assignments</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Assign Subjects to Teachers</li>
                </ol>
            </nav>
        </div>

        <!-- Messages -->
        <?php echo showMessage(); ?>

        <!-- Assignment Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Assign Subjects to Teacher</h5>
            </div>
            <div class="card-body">
                <div class="assignment-form">
                    <form method="POST" action="assign.php">
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label for="teacher_id" class="form-label fw-bold">
                                    <i class="fas fa-chalkboard-teacher"></i> Select Teacher *
                                </label>
                                <select class="form-select" id="teacher_id" name="teacher_id" required>
                                    <option value="">-- Choose Teacher --</option>
                                    <?php 
                                    $teachers->data_seek(0); // Reset pointer
                                    while ($teacher = $teachers->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $teacher['id']; ?>">
                                            <?php echo htmlspecialchars($teacher['name']); ?>
                                            <?php if ($teacher['department']): ?>
                                                - <?php echo htmlspecialchars($teacher['department']); ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-7 mb-3">
                                <label for="subject_ids" class="form-label fw-bold">
                                    <i class="fas fa-book"></i> Select Subjects * <small class="text-muted">(Multiple selection allowed)</small>
                                </label>
                                <select class="form-select" id="subject_ids" name="subject_ids[]" multiple required>
                                    <?php 
                                    $subjects->data_seek(0); // Reset pointer
                                    while ($subject = $subjects->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $subject['id']; ?>">
                                            [<?php echo htmlspecialchars($subject['subject_code']); ?>] 
                                            <?php echo htmlspecialchars($subject['subject_name']); ?>
                                            <?php if ($subject['class_name']): ?>
                                                - (<?php echo htmlspecialchars($subject['class_name'] . ' ' . $subject['section']); ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <small class="text-muted">Hold Ctrl/Cmd to select multiple subjects</small>
                            </div>
                        </div>

                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle"></i> <strong>Note:</strong> You can assign multiple subjects to one teacher at once. If a subject is already assigned to this teacher, it will be skipped.
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Assign Selected Subjects
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Current Assignments Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list"></i> Current Subject Assignments</h5>
            </div>
            <div class="card-body">
                <?php if ($assignments->num_rows > 0): ?>
                <div class="table-responsive">
                    <table id="assignmentsTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>Teacher Name</th>
                                <th>Subject Code</th>
                                <th>Subject Name</th>
                                <th>Class</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $assignments->data_seek(0); // Reset pointer
                            while ($assignment = $assignments->fetch_assoc()): 
                            ?>
                            <tr>
                                <td>
                                    <i class="fas fa-user-tie text-primary"></i>
                                    <strong><?php echo htmlspecialchars($assignment['teacher_name']); ?></strong>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        <?php echo htmlspecialchars($assignment['subject_code']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($assignment['subject_name']); ?>
                                </td>
                                <td>
                                    <?php if ($assignment['class_name']): ?>
                                        <span class="badge bg-secondary">
                                            <?php echo htmlspecialchars($assignment['class_name'] . ' - ' . $assignment['section']); ?>
                                        </span>
                                    <?php else: ?>
                                        <em class="text-muted">No Class</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="unassign.php?id=<?php echo $assignment['id']; ?>" 
                                       class="btn btn-sm btn-danger btn-action" 
                                       title="Unassign"
                                       onclick="return confirm('Are you sure you want to unassign this subject from the teacher?');">
                                        <i class="fas fa-times"></i> Unassign
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle"></i> No subject assignments found. Use the form above to assign subjects to teachers.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#assignmentsTable').DataTable({
                "pageLength": 25,
                "order": [[0, "asc"], [1, "asc"]],
                "language": {
                    "search": "Search Assignments:",
                    "lengthMenu": "Show _MENU_ assignments per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ assignments",
                    "infoEmpty": "No assignments found",
                    "infoFiltered": "(filtered from _MAX_ total assignments)",
                    "zeroRecords": "No matching assignments found"
                }
            });

            // Initialize Select2 for better multi-select
            $('#subject_ids').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select one or more subjects',
                allowClear: true,
                width: '100%'
            });

            $('#teacher_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select a teacher',
                allowClear: true,
                width: '100%'
            });
        });
    </script>
    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>
