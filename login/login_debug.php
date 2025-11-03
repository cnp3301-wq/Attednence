<?php
// Enhanced login.php with detailed debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/login_debug.log');

session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Log function
function logDebug($message) {
    $timestamp = date('Y-m-d H:i:s');
    $log = "[$timestamp] $message\n";
    file_put_contents(__DIR__ . '/login_debug.log', $log, FILE_APPEND);
}

$error_message = '';
$success_message = '';

logDebug("=== LOGIN ATTEMPT STARTED ===");
logDebug("Request Method: " . $_SERVER['REQUEST_METHOD']);
logDebug("Request URI: " . $_SERVER['REQUEST_URI']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    logDebug("POST data received");
    logDebug("POST data: " . print_r($_POST, true));
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user_type = $_POST['user_type'] ?? '';
    $otp = $_POST['otp'] ?? '';
    $action = $_POST['action'] ?? 'login';
    
    logDebug("Email: $email");
    logDebug("User Type: $user_type");
    logDebug("Action: $action");
    logDebug("Password length: " . strlen($password));
    
    if ($action == 'send_otp' && $user_type == 'student') {
        logDebug("Processing student OTP request");
        // Send OTP for student login
        if (validateStudentEmail($email)) {
            $otp_code = generateOTP();
            if (saveOTP($email, $otp_code) && sendOTPEmail($email, $otp_code)) {
                $success_message = "OTP has been sent to your email address.";
                $_SESSION['otp_email'] = $email;
                logDebug("OTP sent successfully");
            } else {
                $error_message = "Failed to send OTP. Please try again.";
                logDebug("Failed to send OTP");
            }
        } else {
            $error_message = "Invalid student email address.";
            logDebug("Invalid student email");
        }
    } elseif ($action == 'verify_otp' && $user_type == 'student') {
        logDebug("Processing student OTP verification");
        // Verify OTP for student
        $stored_email = $_SESSION['otp_email'] ?? '';
        if ($stored_email == $email && verifyOTP($email, $otp)) {
            $user = getStudentByEmail($email);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_type'] = 'student';
                $_SESSION['user_name'] = $user['name'];
                logDebug("Student login successful: " . $user['name']);
                header('Location: ../dashboard/student_dashboard.php');
                exit();
            } else {
                $error_message = "Student account not found.";
                logDebug("Student not found");
            }
        } else {
            $error_message = "Invalid OTP or email.";
            logDebug("Invalid OTP or email mismatch");
        }
    } else {
        logDebug("Processing admin/teacher login");
        // Regular login for admin/teacher
        if ($user_type == 'admin' || $user_type == 'teacher') {
            logDebug("Validating KPRCAS email");
            if (validateKprcasEmail($email)) {
                logDebug("Email validation passed, attempting authentication");
                $user = authenticateUser($email, $password, $user_type);
                if ($user) {
                    logDebug("Authentication successful!");
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['user_name'] = $user['name'];
                    
                    logDebug("Session set: ID=" . $user['id'] . ", Type=" . $user['user_type']);
                    
                    if ($user_type == 'admin') {
                        logDebug("Redirecting to admin dashboard");
                        header('Location: ../dashboard/admin_dashboard.php');
                    } else {
                        logDebug("Redirecting to teacher dashboard");
                        header('Location: ../dashboard/teacher_dashboard.php');
                    }
                    exit();
                } else {
                    $error_message = "Invalid email or password.";
                    logDebug("Authentication failed - invalid credentials");
                }
            } else {
                $error_message = "Please use your KPRCAS email address (@kprcas.ac.in).";
                logDebug("Email validation failed");
            }
        } else {
            logDebug("Invalid user type: $user_type");
        }
    }
}

logDebug("=== LOGIN ATTEMPT ENDED ===");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPRCAS Attendance System - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 40px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h2 {
            color: #333;
            font-weight: 600;
        }
        .user-type-tabs {
            margin-bottom: 20px;
        }
        .nav-pills .nav-link {
            border-radius: 25px;
            font-weight: 500;
        }
        .nav-pills .nav-link.active {
            background-color: #667eea;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            margin-top: 10px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .otp-section {
            display: none;
            margin-top: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
        }
        .alert {
            border-radius: 10px;
        }
        .debug-link {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
        }
        .debug-link a {
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <i class="fas fa-graduation-cap fa-3x text-primary mb-3"></i>
                <h2>KPRCAS Attendance System</h2>
                <p class="text-muted">Please login to continue</p>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <!-- User Type Tabs -->
            <div class="user-type-tabs">
                <ul class="nav nav-pills nav-justified" id="userTypeTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="admin-tab" data-bs-toggle="pill" data-bs-target="#admin" type="button" role="tab">Admin</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="teacher-tab" data-bs-toggle="pill" data-bs-target="#teacher" type="button" role="tab">Teacher</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="student-tab" data-bs-toggle="pill" data-bs-target="#student" type="button" role="tab">Student</button>
                    </li>
                </ul>
            </div>

            <div class="tab-content" id="userTypeTabsContent">
                <!-- Admin Login -->
                <div class="tab-pane fade show active" id="admin" role="tabpanel">
                    <form method="POST" action="">
                        <input type="hidden" name="user_type" value="admin">
                        <input type="hidden" name="action" value="login">
                        
                        <div class="mb-3">
                            <label for="admin_email" class="form-label">
                                <i class="fas fa-envelope"></i> Email Address
                            </label>
                            <input type="email" class="form-control" id="admin_email" name="email" 
                                   placeholder="admin@kprcas.ac.in" value="admin@kprcas.ac.in" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="admin_password" class="form-label">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <input type="password" class="form-control" id="admin_password" name="password" 
                                   placeholder="Enter your password" value="admin123" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-login">
                            <i class="fas fa-sign-in-alt"></i> Login as Admin
                        </button>
                    </form>
                </div>

                <!-- Teacher Login -->
                <div class="tab-pane fade" id="teacher" role="tabpanel">
                    <form method="POST" action="">
                        <input type="hidden" name="user_type" value="teacher">
                        <input type="hidden" name="action" value="login">
                        
                        <div class="mb-3">
                            <label for="teacher_email" class="form-label">
                                <i class="fas fa-envelope"></i> Email Address
                            </label>
                            <input type="email" class="form-control" id="teacher_email" name="email" 
                                   placeholder="teacher@kprcas.ac.in" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="teacher_password" class="form-label">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <input type="password" class="form-control" id="teacher_password" name="password" 
                                   placeholder="Enter your password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-login">
                            <i class="fas fa-sign-in-alt"></i> Login as Teacher
                        </button>
                    </form>
                </div>

                <!-- Student Login -->
                <div class="tab-pane fade" id="student" role="tabpanel">
                    <form method="POST" action="" id="studentLoginForm">
                        <input type="hidden" name="user_type" value="student">
                        <input type="hidden" name="action" value="send_otp" id="student_action">
                        
                        <div class="mb-3">
                            <label for="student_email" class="form-label">
                                <i class="fas fa-envelope"></i> Email Address
                            </label>
                            <input type="email" class="form-control" id="student_email" name="email" 
                                   placeholder="student@example.com" required
                                   value="<?php echo isset($_SESSION['otp_email']) ? $_SESSION['otp_email'] : ''; ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-login" id="sendOtpBtn">
                            <i class="fas fa-paper-plane"></i> Send OTP
                        </button>
                    </form>

                    <!-- OTP Verification Section -->
                    <div class="otp-section" id="otpSection" <?php echo isset($_SESSION['otp_email']) ? 'style="display: block;"' : ''; ?>>
                        <form method="POST" action="">
                            <input type="hidden" name="user_type" value="student">
                            <input type="hidden" name="action" value="verify_otp">
                            <input type="hidden" name="email" value="<?php echo isset($_SESSION['otp_email']) ? $_SESSION['otp_email'] : ''; ?>">
                            
                            <div class="mb-3">
                                <label for="otp" class="form-label">
                                    <i class="fas fa-key"></i> Enter OTP
                                </label>
                                <input type="text" class="form-control" id="otp" name="otp" 
                                       placeholder="Enter 6-digit OTP" maxlength="6" required>
                                <small class="form-text text-muted">
                                    OTP has been sent to your email address
                                </small>
                            </div>
                            
                            <button type="submit" class="btn btn-success btn-login">
                                <i class="fas fa-check"></i> Verify OTP & Login
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="debug-link">
                <a href="debug_login.php" target="_blank">
                    <i class="fas fa-bug"></i> Debug Login Issues
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Log form submission
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                console.log('Form submitted:', new FormData(form));
            });
        });
        
        document.getElementById('studentLoginForm').addEventListener('submit', function(e) {
            if (document.getElementById('student_action').value === 'send_otp') {
                setTimeout(function() {
                    document.getElementById('otpSection').style.display = 'block';
                    document.getElementById('sendOtpBtn').innerHTML = '<i class="fas fa-sync-alt"></i> Resend OTP';
                }, 1000);
            }
        });

        // Tab switching functionality
        document.querySelectorAll('[data-bs-toggle="pill"]').forEach(function(tab) {
            tab.addEventListener('shown.bs.tab', function(e) {
                console.log('Tab switched to:', e.target.id);
                // Reset forms when switching tabs
                document.querySelectorAll('form').forEach(function(form) {
                    form.reset();
                });
                document.getElementById('otpSection').style.display = 'none';
            });
        });
    </script>
</body>
</html>
