<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
checkAdminAuth();

// Get active classes for dropdown
$classes_query = "SELECT * FROM classes WHERE status = 'active' ORDER BY class_name, section";
$classes_result = $conn->query($classes_query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $roll_number = sanitize($_POST['roll_number']);
    $first_name = sanitize($_POST['first_name']);
    $last_name = sanitize($_POST['last_name']);
    $name = $first_name . ' ' . $last_name; // Combine into single name field
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $class_id = !empty($_POST['class_id']) ? intval($_POST['class_id']) : null;
    $admission_date = sanitize($_POST['admission_date']);
    $date_of_birth = !empty($_POST['date_of_birth']) ? sanitize($_POST['date_of_birth']) : null;
    $address = sanitize($_POST['address']);
    $status = sanitize($_POST['status']);
    
    // Validate required fields
    if (empty($roll_number) || empty($first_name) || empty($last_name) || empty($email)) {
        setMessage('danger', 'Please fill all required fields!');
    } else {
        // Check if roll number already exists
        $check_stmt = $conn->prepare("SELECT id FROM students WHERE roll_number = ?");
        $check_stmt->bind_param("s", $roll_number);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            setMessage('danger', 'Roll number already exists!');
        } else {
            // Check if email already exists
            $check_stmt = $conn->prepare("SELECT id FROM students WHERE email = ?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                setMessage('danger', 'Email already exists!');
            } else {
                // Insert student
                $stmt = $conn->prepare("INSERT INTO students (roll_number, name, email, phone, class_id, admission_date, date_of_birth, address, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssissss", $roll_number, $name, $email, $phone, $class_id, $admission_date, $date_of_birth, $address, $status);
                
                if ($stmt->execute()) {
                    // Update student count for the class
                    if ($class_id) {
                        updateStudentCount($class_id);
                    }
                    setMessage('success', 'Student added successfully!');
                    header('Location: index.php');
                    exit();
                } else {
                    setMessage('danger', 'Error: ' . $conn->error);
                }
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
    <title>Add Student - KPRCAS Admin</title>
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
            <a class="nav-link active" href="index.php">
                <i class="fas fa-user-graduate"></i> Manage Students
            </a>
            <a class="nav-link" href="../teachers/index.php">
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
            <h1><i class="fas fa-user-plus text-success"></i> Add New Student</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php">Students</a></li>
                    <li class="breadcrumb-item active">Add New</li>
                </ol>
            </nav>
        </div>

        <!-- Messages -->
        <?php echo showMessage(); ?>

        <!-- Add Student Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-edit"></i> Student Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <!-- Roll Number -->
                        <div class="col-md-6 mb-3">
                            <label for="roll_number" class="form-label required">Roll Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                <input type="text" class="form-control" id="roll_number" name="roll_number" 
                                       required placeholder="e.g., 2024001">
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label required">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       required placeholder="student@example.com">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- First Name -->
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label required">First Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       required placeholder="First Name">
                            </div>
                        </div>

                        <!-- Last Name -->
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label required">Last Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       required placeholder="Last Name">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Phone -->
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="text" class="form-control" id="phone" name="phone" 
                                       placeholder="e.g., 9876543210">
                            </div>
                        </div>

                        <!-- Date of Birth -->
                        <div class="col-md-6 mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-birthday-cake"></i></span>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Class -->
                        <div class="col-md-6 mb-3">
                            <label for="class_id" class="form-label">Class</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-chalkboard"></i></span>
                                <select class="form-select" id="class_id" name="class_id">
                                    <option value="">-- Select Class (Optional) --</option>
                                    <?php while ($class = $classes_result->fetch_assoc()): ?>
                                        <option value="<?php echo $class['id']; ?>">
                                            <?php echo htmlspecialchars($class['class_name'] . ' - ' . $class['section'] . ' (' . $class['academic_year'] . ')'); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <small class="text-muted">Student count will be updated automatically</small>
                        </div>

                        <!-- Admission Date -->
                        <div class="col-md-6 mb-3">
                            <label for="admission_date" class="form-label required">Admission Date</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                <input type="date" class="form-control" id="admission_date" name="admission_date" 
                                       required value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Address -->
                        <div class="col-md-8 mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3" 
                                      placeholder="Enter full address"></textarea>
                        </div>

                        <!-- Status -->
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label required">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Add Student
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
    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>
