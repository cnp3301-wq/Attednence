<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'] ?? '';
    
    // Single login - check if email belongs to admin or teacher
    if (validateKprcasEmail($email)) {
        // Try to authenticate user (will work for both admin and teacher)
        $query = "SELECT * FROM users WHERE email = ? AND (user_type = 'admin' OR user_type = 'teacher') AND status = 'active'";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['user_name'] = $user['name'];
                
                // Redirect based on user type (role-based routing)
                if ($user['user_type'] == 'admin') {
                    header('Location: ../admin/index.php');
                } else if ($user['user_type'] == 'teacher') {
                    header('Location: ../teacher/index.php');
                }
                exit();
            } else {
                $error_message = "Invalid email or password.";
            }
        } else {
            $error_message = "No account found with this email address.";
        }
    } else {
        $error_message = "Please use your KPRCAS email address (@kprcas.ac.in).";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>KPRCAS Attendance System - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .login-container {
            max-width: 450px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            padding: 50px 40px;
            animation: slideUp 0.5s ease;
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .logo-circle {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .logo-circle i {
            font-size: 3rem;
            color: white;
        }
        .login-header h2 {
            color: #333;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 1.8rem;
        }
        .login-header p {
            color: #666;
            font-size: 0.95rem;
        }
        .form-floating {
            margin-bottom: 20px;
        }
        .form-control {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 15px 20px;
            height: 60px;
            font-size: 1rem;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
        }
        .form-floating label {
            padding: 18px 20px;
            color: #999;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 15px;
            font-weight: 600;
            font-size: 1.1rem;
            width: 100%;
            color: white;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 25px;
            padding: 15px 20px;
        }
        .role-badge {
            display: inline-block;
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 25px;
        }
        
        /* Mobile Responsive */
        @media (max-width: 576px) {
            body {
                padding: 10px;
            }
            .login-container {
                padding: 30px 25px;
            }
            .logo-circle {
                width: 80px;
                height: 80px;
            }
            .logo-circle i {
                font-size: 2.5rem;
            }
            .login-header h2 {
                font-size: 1.5rem;
            }
            .login-header p {
                font-size: 0.85rem;
            }
            .role-badge {
                font-size: 0.8rem;
                padding: 6px 15px;
            }
            .form-control {
                height: 50px;
                font-size: 0.9rem;
                padding: 12px 15px;
            }
            .form-floating label {
                font-size: 0.9rem;
                padding: 14px 15px;
            }
            .btn-login {
                padding: 12px;
                font-size: 1rem;
            }
        }
        
        @media (max-width: 375px) {
            .login-container {
                padding: 25px 20px;
            }
            .login-header h2 {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-circle">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h2>KPRCAS Attendance</h2>
            <p>Admin & Teacher Portal</p>
            <!-- <div class="role-badge">
                <i class="fas fa-shield-alt"></i> Role-Based Login
            </div> -->
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

        <form method="POST" action="">
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email" 
                       placeholder="name@kprcas.ac.in" required autofocus>
                <label for="email">
                    <i class="fas fa-envelope"></i> Email Address
                </label>
            </div>
            
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password" 
                       placeholder="Password" required>
                <label for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
            </div>
            
            <button type="submit" class="btn btn-login">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
