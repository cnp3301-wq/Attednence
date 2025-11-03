-- KPRCAS Attendance System Database Schema
-- Create Database
CREATE DATABASE IF NOT EXISTS kprcas_attendance;
USE kprcas_attendance;

-- ====================================
-- Users Table (Admin and Teachers)
-- ====================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('admin', 'teacher') NOT NULL,
    phone VARCHAR(15),
    department VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_user_type (user_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================================
-- Students Table
-- ====================================
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    roll_number VARCHAR(50) NOT NULL UNIQUE,
    phone VARCHAR(15),
    department VARCHAR(100),
    year INT,
    section VARCHAR(10),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_roll_number (roll_number),
    INDEX idx_department (department),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================================
-- OTP Verification Table
-- ====================================
CREATE TABLE IF NOT EXISTS otp_verification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    expiry_time DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_expiry (expiry_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================================
-- Login Logs Table (Optional - for tracking)
-- ====================================
CREATE TABLE IF NOT EXISTS login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_type ENUM('admin', 'teacher', 'student'),
    email VARCHAR(100),
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    status ENUM('success', 'failed') DEFAULT 'success',
    INDEX idx_user_id (user_id),
    INDEX idx_email (email),
    INDEX idx_login_time (login_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================================
-- Insert Sample Data
-- ====================================

-- Insert Admin User
-- Password: admin123
-- Note: The password hash below is a placeholder. Run the following PHP code to generate a proper hash:
-- password_hash('admin123', PASSWORD_DEFAULT);
-- Or use the generate_password.php script in the login folder
INSERT INTO users (name, email, password, user_type, department, status) VALUES
('Admin User', 'admin@kprcas.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administration', 'active');

-- Insert Sample Teachers
-- Password: teacher123
INSERT INTO users (name, email, password, user_type, phone, department, status) VALUES
('Dr. Rajesh Kumar', 'rajesh.kumar@kprcas.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', '9876543210', 'Computer Science', 'active'),
('Prof. Priya Sharma', 'priya.sharma@kprcas.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', '9876543211', 'Electronics', 'active'),
('Dr. Arun Verma', 'arun.verma@kprcas.ac.in', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', '9876543212', 'Mechanical', 'active');

-- Insert Sample Students
INSERT INTO students (name, email, roll_number, phone, department, year, section, status) VALUES
('Amit Singh', 'amit.singh@student.com', 'CS2021001', '9123456781', 'Computer Science', 3, 'A', 'active'),
('Neha Patel', 'neha.patel@student.com', 'CS2021002', '9123456782', 'Computer Science', 3, 'A', 'active'),
('Rahul Gupta', 'rahul.gupta@student.com', 'CS2021003', '9123456783', 'Computer Science', 3, 'B', 'active'),
('Priyanka Reddy', 'priyanka.reddy@student.com', 'EC2021001', '9123456784', 'Electronics', 2, 'A', 'active'),
('Vijay Kumar', 'vijay.kumar@student.com', 'ME2021001', '9123456785', 'Mechanical', 1, 'A', 'active');

-- ====================================
-- Additional Useful Queries
-- ====================================

-- To manually create a password hash (use this in PHP):
-- password_hash('your_password', PASSWORD_DEFAULT);

-- To verify database setup:
SHOW TABLES;

-- To check user counts:
SELECT user_type, COUNT(*) as count FROM users GROUP BY user_type;
SELECT COUNT(*) as student_count FROM students;

-- Clean up expired OTPs (run this periodically):
-- DELETE FROM otp_verification WHERE expiry_time < NOW();
