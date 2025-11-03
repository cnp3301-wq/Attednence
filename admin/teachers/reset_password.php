<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
checkAdminAuth();

$id = $_GET['id'] ?? 0;

// Get teacher data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND user_type = 'teacher'");
$stmt->bind_param("i", $id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();

if (!$teacher) {
    setMessage('danger', 'Teacher not found!');
    header('Location: index.php');
    exit();
}

$new_password = '';
$password_reset = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Generate new password based on teacher's name
    $new_password = generatePassword($teacher['name']);
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update password
    $stmt = $conn->prepare("UPDATE users SET password = ?, plain_password = ? WHERE id = ?");
    $stmt->bind_param("ssi", $hashed_password, $new_password, $id);
    
    if ($stmt->execute()) {
        $password_reset = true;
        setMessage('success', 'Password reset successfully!');
    } else {
        setMessage('danger', 'Error resetting password: ' . $conn->error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Reset Teacher Password - KPRCAS Admin</title>
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
        .teacher-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        .credentials-box {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
            color: #28a745;
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
            <h1><i class="fas fa-key text-info"></i> Reset Teacher Password</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php">Teachers</a></li>
                    <li class="breadcrumb-item active">Reset Password</li>
                </ol>
            </nav>
        </div>

        <!-- Messages -->
        <?php echo showMessage(); ?>

        <?php if ($password_reset): ?>
        <!-- New Password Display -->
        <div class="credentials-box">
            <h4 class="mb-3"><i class="fas fa-check-circle"></i> Password Reset Successfully!</h4>
            <p class="mb-3"><i class="fas fa-exclamation-triangle"></i> <strong>Important:</strong> Please save this new password and share it with the teacher securely.</p>
            
            <div class="teacher-info mb-3" style="background: rgba(255,255,255,0.2); color: white; border-color: white;">
                <strong>Teacher:</strong> <?php echo htmlspecialchars($teacher['name']); ?><br>
                <strong>Username:</strong> <?php echo htmlspecialchars($teacher['username']); ?>
            </div>
            
            <div class="credential-item">
                <div class="credential-label">New Password</div>
                <div class="credential-value" id="password-value"><?php echo htmlspecialchars($new_password); ?></div>
                <button class="copy-btn mt-2" onclick="copyToClipboard('password-value')">
                    <i class="fas fa-copy"></i> Copy Password
                </button>
            </div>
            
            <div class="alert alert-light mt-3 mb-0">
                <strong>Login URL:</strong> <a href="../../login/login.php" target="_blank" style="color: white; text-decoration: underline;">http://localhost/attendance/login/login.php</a>
            </div>
            
            <div class="mt-3">
                <a href="index.php" class="btn btn-light"><i class="fas fa-list"></i> Back to Teachers List</a>
                <a href="edit.php?id=<?php echo $teacher['id']; ?>" class="btn btn-outline-light"><i class="fas fa-edit"></i> Edit Teacher</a>
            </div>
        </div>
        <?php else: ?>
        <!-- Confirmation Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-key"></i> Confirm Password Reset</h5>
            </div>
            <div class="card-body">
                <div class="teacher-info mb-4">
                    <h6><i class="fas fa-user-tie"></i> Teacher Information</h6>
                    <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($teacher['name']); ?></p>
                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($teacher['email']); ?></p>
                    <p class="mb-1"><strong>Username:</strong> <?php echo htmlspecialchars($teacher['username']); ?></p>
                    <?php if (!empty($teacher['plain_password'])): ?>
                    <p class="mb-0"><strong>Current Password:</strong> <code><?php echo htmlspecialchars($teacher['plain_password']); ?></code></p>
                    <?php endif; ?>
                </div>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Warning:</strong> This will generate a new random password for this teacher. The old password will no longer work.
                </div>

                <form method="POST" action="">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-info" onclick="return confirm('Are you sure you want to reset this teacher\'s password?');">
                            <i class="fas fa-key"></i> Generate New Password
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
