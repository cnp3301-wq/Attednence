<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
checkAdminAuth();

// Get active classes for dropdown
$classes_query = "SELECT * FROM classes WHERE status = 'active' ORDER BY class_name, section";
$classes_result = $conn->query($classes_query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject_code = strtoupper(sanitize($_POST['subject_code']));
    $subject_name = sanitize($_POST['subject_name']);
    $class_id = !empty($_POST['class_id']) ? intval($_POST['class_id']) : null;
    $credits = intval($_POST['credits']);
    $description = sanitize($_POST['description']);
    $status = sanitize($_POST['status']);
    
    // Validate required fields
    if (empty($subject_code) || empty($subject_name) || empty($credits)) {
        setMessage('danger', 'Please fill all required fields!');
    } else {
        // Check if subject code already exists
        $check_stmt = $conn->prepare("SELECT id FROM subjects WHERE subject_code = ?");
        $check_stmt->bind_param("s", $subject_code);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            setMessage('danger', 'Subject code already exists!');
        } else {
            // Insert subject
            $stmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name, class_id, credits, description, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiiss", $subject_code, $subject_name, $class_id, $credits, $description, $status);
            
            if ($stmt->execute()) {
                setMessage('success', 'Subject added successfully!');
                header('Location: index.php');
                exit();
            } else {
                setMessage('danger', 'Error: ' . $conn->error);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Add Subject - KPRCAS Admin</title>
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
        }
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.2);
            color: white;
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
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 15px 20px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
        }
        .required::after {
            content: " *";
            color: red;
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
            <a class="nav-link active" href="index.php">
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
            <h1><i class="fas fa-book-medical text-success"></i> Add New Subject</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php">Subjects</a></li>
                    <li class="breadcrumb-item active">Add New</li>
                </ol>
            </nav>
        </div>

        <!-- Messages -->
        <?php echo showMessage(); ?>

        <!-- Add Subject Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-book-open"></i> Subject Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <!-- Subject Code -->
                        <div class="col-md-6 mb-3">
                            <label for="subject_code" class="form-label required">Subject Code</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                <input type="text" class="form-control" id="subject_code" name="subject_code" 
                                       required placeholder="e.g., CS101" style="text-transform: uppercase;">
                            </div>
                            <small class="text-muted">Will be converted to uppercase</small>
                        </div>

                        <!-- Subject Name -->
                        <div class="col-md-6 mb-3">
                            <label for="subject_name" class="form-label required">Subject Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-book"></i></span>
                                <input type="text" class="form-control" id="subject_name" name="subject_name" 
                                       required placeholder="e.g., Computer Science Fundamentals">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Class -->
                        <div class="col-md-6 mb-3">
                            <label for="class_id" class="form-label required">Assign to Class</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-chalkboard"></i></span>
                                <select class="form-select" id="class_id" name="class_id" required>
                                    <option value="">-- Select Class --</option>
                                    <?php while ($class = $classes_result->fetch_assoc()): ?>
                                        <option value="<?php echo $class['id']; ?>">
                                            <?php echo htmlspecialchars($class['class_name'] . ' - ' . $class['section'] . ' (' . $class['academic_year'] . ')'); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <small class="text-muted">Subject will be available for this class</small>
                        </div>

                        <!-- Credits -->
                        <div class="col-md-6 mb-3">
                            <label for="credits" class="form-label required">Credits</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-star"></i></span>
                                <input type="number" class="form-control" id="credits" name="credits" 
                                       required min="1" max="10" value="3">
                            </div>
                            <small class="text-muted">Number of credit hours (1-10)</small>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Description -->
                        <div class="col-md-8 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" 
                                      placeholder="Brief description of the subject (optional)"></textarea>
                        </div>

                        <!-- Status -->
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label required">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <div class="mt-3 p-3 bg-light rounded">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    <strong>Note:</strong> Active subjects can be assigned to teachers.
                                </small>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Add Subject
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-uppercase subject code
        document.getElementById('subject_code').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
        });
    </script>
    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>
