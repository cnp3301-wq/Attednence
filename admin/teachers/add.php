<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
checkAdminAuth();

$generated_password = '';
$show_credentials = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $username = sanitize($_POST['username']);
    $phone = sanitize($_POST['phone']);
    $department = sanitize($_POST['department']);
    $designation = sanitize($_POST['designation']);
    $status = sanitize($_POST['status']);
    
    // Generate password based on teacher's name
    $plain_password = generatePassword($name);
    $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($username)) {
        setMessage('danger', 'Please fill all required fields!');
    } else {
        // Check if username already exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            setMessage('danger', 'Username already exists!');
        } else {
            // Check if email already exists
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                setMessage('danger', 'Email already exists!');
            } else {
                // Insert teacher
                $user_type = 'teacher';
                $stmt = $conn->prepare("INSERT INTO users (username, password, email, name, user_type, phone, department, designation, plain_password, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssssss", $username, $hashed_password, $email, $name, $user_type, $phone, $department, $designation, $plain_password, $status);
                
                if ($stmt->execute()) {
                    $generated_password = $plain_password;
                    $show_credentials = true;
                    setMessage('success', 'Teacher added successfully! Please save the login credentials below.');
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
    <title>Add Teacher - KPRCAS Admin</title>
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
            margin-bottom: 25px;
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
        .credentials-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-top: 20px;
        }
        .credential-item {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-family: 'Courier New', monospace;
        }
        .credential-label {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 5px;
            opacity: 0.9;
        }
        .credential-value {
            font-size: 1.3rem;
            font-weight: bold;
        }
        .copy-btn {
            background: white;
            color: #667eea;
            border: none;
            padding: 5px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.85rem;
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
            <h1><i class="fas fa-user-plus text-success"></i> Add New Teacher</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php">Teachers</a></li>
                    <li class="breadcrumb-item active">Add New</li>
                </ol>
            </nav>
        </div>

        <!-- Messages -->
        <?php echo showMessage(); ?>

        <?php if ($show_credentials): ?>
        <!-- Generated Credentials Box -->
        <div class="credentials-box">
            <h4 class="mb-3"><i class="fas fa-check-circle"></i> Teacher Account Created Successfully!</h4>
            <p class="mb-3"><i class="fas fa-exclamation-triangle"></i> <strong>Important:</strong> Please save these login credentials. The password will not be shown again!</p>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="credential-item">
                        <div class="credential-label">Username</div>
                        <div class="credential-value" id="username-value"><?php echo htmlspecialchars($_POST['username']); ?></div>
                        <button class="copy-btn mt-2" onclick="copyToClipboard('username-value')">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="credential-item">
                        <div class="credential-label">Password</div>
                        <div class="credential-value" id="password-value"><?php echo htmlspecialchars($generated_password); ?></div>
                        <button class="copy-btn mt-2" onclick="copyToClipboard('password-value')">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-light mt-3 mb-0">
                <strong>Login URL:</strong> <a href="../../login/login.php" target="_blank" style="color: white; text-decoration: underline;">http://localhost/attendance/login/login.php</a>
            </div>
            
            <div class="mt-3">
                <a href="add.php" class="btn btn-light"><i class="fas fa-plus"></i> Add Another Teacher</a>
                <a href="index.php" class="btn btn-outline-light"><i class="fas fa-list"></i> View All Teachers</a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Add Teacher Form -->
        <?php if (!$show_credentials): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-edit"></i> Teacher Information</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> <strong>Note:</strong> A random password will be automatically generated for the teacher. You can reset it later if needed.
                </div>
                
                <form method="POST" action="">
                    <div class="row">
                        <!-- Full Name -->
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label required">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       required placeholder="Enter full name">
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label required">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" 
                                       required placeholder="teacher@kprcas.ac.in">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Username -->
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label required">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user-circle"></i></span>
                                <input type="text" class="form-control" id="username" name="username" 
                                       required placeholder="e.g., teacher001">
                            </div>
                            <small class="text-muted">This will be used for login</small>
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="text" class="form-control" id="phone" name="phone" 
                                       placeholder="e.g., 9876543210">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Department -->
                        <div class="col-md-6 mb-3">
                            <label for="department" class="form-label">Department</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-building"></i></span>
                                <input type="text" class="form-control" id="department" name="department" 
                                       placeholder="e.g., Computer Science">
                            </div>
                        </div>

                        <!-- Designation -->
                        <div class="col-md-6 mb-3">
                            <label for="designation" class="form-label">Designation</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                                <input type="text" class="form-control" id="designation" name="designation" 
                                       placeholder="e.g., Assistant Professor">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Status -->
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label required">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-key"></i> <strong>Password:</strong> A random 8-character password will be automatically generated and displayed after creation.
                    </div>

                    <hr class="my-4">

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Create Teacher Account
                        </button>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyToClipboard(elementId) {
            const text = document.getElementById(elementId).textContent;
            navigator.clipboard.writeText(text).then(function() {
                alert('Copied to clipboard: ' + text);
            });
        }
    </script>
    <script src="../assets/js/mobile-menu.js"></script>
</body>
</html>
