<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
checkAdminAuth();

$id = $_GET['id'] ?? 0;

// Get class data
$stmt = $conn->prepare("SELECT * FROM classes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$class = $stmt->get_result()->fetch_assoc();

if (!$class) {
    setMessage('danger', 'Class not found!');
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $class_name = sanitize($_POST['class_name']);
    $section = sanitize($_POST['section']);
    $academic_year = sanitize($_POST['academic_year']);
    $status = sanitize($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE classes SET class_name = ?, section = ?, academic_year = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $class_name, $section, $academic_year, $status, $id);
    
    if ($stmt->execute()) {
        setMessage('success', 'Class updated successfully!');
        header('Location: index.php');
        exit();
    } else {
        setMessage('danger', 'Error: ' . $conn->error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Edit Class - KPRCAS Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/responsive.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; position: fixed; top: 0; left: 0; width: 250px; padding: 20px 0; }
        .sidebar .logo { text-align: center; padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 25px; margin: 5px 15px; border-radius: 8px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: rgba(255,255,255,0.2); color: white; }
        .sidebar .nav-link i { width: 25px; }
        .main-content { margin-left: 250px; padding: 30px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo"><h4><i class="fas fa-graduation-cap"></i> KPRCAS Admin</h4></div>
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

    <div class="main-content">
        <div class="mb-4">
            <h1><i class="fas fa-edit text-warning"></i> Edit Class</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php">Classes</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>

        <?php echo showMessage(); ?>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="class_name" class="form-label">Class Name *</label>
                            <input type="text" class="form-control" id="class_name" name="class_name" required
                                   value="<?php echo htmlspecialchars($class['class_name']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="section" class="form-label">Section *</label>
                            <input type="text" class="form-control" id="section" name="section" required
                                   value="<?php echo htmlspecialchars($class['section']); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="academic_year" class="form-label">Academic Year *</label>
                            <input type="text" class="form-control" id="academic_year" name="academic_year" required
                                   value="<?php echo htmlspecialchars($class['academic_year']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" <?php echo $class['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $class['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <strong>Student Count:</strong> <?php echo $class['student_count']; ?> students currently assigned
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Class</button>
                        <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>
