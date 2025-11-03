-- ====================================
-- KPRCAS Attendance System
-- Complete Database Schema
-- Version: 2.0
-- Date: November 2, 2025
-- ====================================

-- Create Database
CREATE DATABASE IF NOT EXISTS kprcas_attendance;
USE kprcas_attendance;

-- Set charset and collation
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ====================================
-- TABLE 1: Users (Admin and Teachers)
-- ====================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(50) NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    plain_password VARCHAR(100) NULL COMMENT 'Stores plain password for communication',
    user_type ENUM('admin', 'teacher') NOT NULL,
    phone VARCHAR(15),
    department VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_user_type (user_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- TABLE 2: Classes
-- ====================================
CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(100) NOT NULL,
    section VARCHAR(10) NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    student_count INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_class (class_name, section, academic_year),
    INDEX idx_class_name (class_name),
    INDEX idx_academic_year (academic_year),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- TABLE 3: Students
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
    class_id INT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
    INDEX idx_email (email),
    INDEX idx_roll_number (roll_number),
    INDEX idx_department (department),
    INDEX idx_class_id (class_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- TABLE 4: Subjects
-- ====================================
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(20) NOT NULL UNIQUE,
    subject_name VARCHAR(150) NOT NULL,
    class_id INT NOT NULL,
    description TEXT,
    credits INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    INDEX idx_subject_code (subject_code),
    INDEX idx_class_id (class_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- TABLE 5: Teacher-Subject Assignments
-- ====================================
CREATE TABLE IF NOT EXISTS teacher_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    assigned_date DATE DEFAULT (CURRENT_DATE),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (teacher_id, subject_id),
    INDEX idx_teacher_id (teacher_id),
    INDEX idx_subject_id (subject_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- TABLE 6: Attendance Sessions (QR Code Sessions)
-- ====================================
CREATE TABLE IF NOT EXISTS attendance_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    class_id INT NOT NULL,
    session_code VARCHAR(50) UNIQUE NOT NULL,
    qr_code_path VARCHAR(255),
    session_date DATE NOT NULL,
    session_time TIME NOT NULL,
    duration_minutes INT DEFAULT 10,
    expires_at DATETIME NOT NULL,
    status ENUM('active', 'expired', 'closed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    INDEX idx_session_code (session_code),
    INDEX idx_teacher_id (teacher_id),
    INDEX idx_subject_id (subject_id),
    INDEX idx_class_id (class_id),
    INDEX idx_session_date (session_date),
    INDEX idx_expires_at (expires_at),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- TABLE 7: Attendance Records
-- ====================================
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    student_id INT NOT NULL,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    class_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    attendance_time TIME NOT NULL,
    status ENUM('present', 'absent', 'late') DEFAULT 'present',
    marked_via ENUM('qr_code', 'manual') DEFAULT 'qr_code',
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES attendance_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (session_id, student_id),
    INDEX idx_session_id (session_id),
    INDEX idx_student_id (student_id),
    INDEX idx_teacher_id (teacher_id),
    INDEX idx_subject_id (subject_id),
    INDEX idx_class_id (class_id),
    INDEX idx_attendance_date (attendance_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- TABLE 8: OTP Verification
-- ====================================
CREATE TABLE IF NOT EXISTS otp_verification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    expiry_time DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_expiry_time (expiry_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- TABLE 9: QR Code Email Logs
-- ====================================
CREATE TABLE IF NOT EXISTS qr_email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    student_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('sent', 'failed') DEFAULT 'sent',
    error_message TEXT,
    FOREIGN KEY (session_id) REFERENCES attendance_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_session_id (session_id),
    INDEX idx_student_id (student_id),
    INDEX idx_sent_at (sent_at),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- TABLE 10: Login Logs (Security & Tracking)
-- ====================================
CREATE TABLE IF NOT EXISTS login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    user_type ENUM('admin', 'teacher', 'student') NOT NULL,
    email VARCHAR(100) NOT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    status ENUM('success', 'failed') DEFAULT 'success',
    failure_reason VARCHAR(255) NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_email (email),
    INDEX idx_login_time (login_time),
    INDEX idx_status (status),
    INDEX idx_user_type (user_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- TABLE 11: System Settings (Optional)
-- ====================================
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- INSERT SAMPLE DATA
-- ====================================

-- Insert Admin User
-- Password: admin123
-- Username: admin
INSERT INTO users (name, email, username, password, plain_password, user_type, department, status) VALUES
('Admin User', 'admin@kprcas.ac.in', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin123', 'admin', 'Administration', 'active')
ON DUPLICATE KEY UPDATE name=name;

-- Insert Sample Teachers
-- Password: teacher123 (will be replaced with name-based passwords)
INSERT INTO users (name, email, username, password, plain_password, user_type, phone, department, status) VALUES
('Dr. Rajesh Kumar', 'rajesh.kumar@kprcas.ac.in', 'rajesh.kumar', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rajesh1234', 'teacher', '9876543210', 'Computer Science', 'active'),
('Prof. Priya Sharma', 'priya.sharma@kprcas.ac.in', 'priya.sharma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Priya1234', 'teacher', '9876543211', 'Electronics', 'active'),
('Dr. Arun Verma', 'arun.verma@kprcas.ac.in', 'arun.verma', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Arun1234', 'teacher', '9876543212', 'Mechanical Engineering', 'active')
ON DUPLICATE KEY UPDATE name=name;

-- Insert Sample Classes
INSERT INTO classes (class_name, section, academic_year, student_count, status) VALUES
('BCA', 'A', '2024-2025', 0, 'active'),
('BCA', 'B', '2024-2025', 0, 'active'),
('MCA', 'A', '2024-2025', 0, 'active'),
('B.Sc Computer Science', 'A', '2024-2025', 0, 'active'),
('B.Sc Computer Science', 'B', '2024-2025', 0, 'active'),
('M.Sc Computer Science', 'A', '2024-2025', 0, 'active')
ON DUPLICATE KEY UPDATE class_name=class_name;

-- Get class IDs for subject insertion
SET @bca_a_id = (SELECT id FROM classes WHERE class_name = 'BCA' AND section = 'A' AND academic_year = '2024-2025' LIMIT 1);
SET @bca_b_id = (SELECT id FROM classes WHERE class_name = 'BCA' AND section = 'B' AND academic_year = '2024-2025' LIMIT 1);
SET @mca_a_id = (SELECT id FROM classes WHERE class_name = 'MCA' AND section = 'A' AND academic_year = '2024-2025' LIMIT 1);
SET @bsc_a_id = (SELECT id FROM classes WHERE class_name = 'B.Sc Computer Science' AND section = 'A' AND academic_year = '2024-2025' LIMIT 1);

-- Insert Sample Subjects
INSERT INTO subjects (subject_code, subject_name, class_id, description, credits, status) VALUES
('CS101', 'Data Structures and Algorithms', @bca_a_id, 'Introduction to data structures, algorithms, and complexity analysis', 4, 'active'),
('CS102', 'Database Management Systems', @bca_a_id, 'Relational database concepts, SQL, normalization, and transactions', 4, 'active'),
('CS103', 'Web Technologies', @bca_a_id, 'HTML5, CSS3, JavaScript, Bootstrap, PHP fundamentals', 3, 'active'),
('CS104', 'Programming in Java', @bca_a_id, 'Object-oriented programming with Java', 4, 'active'),
('CS105', 'Operating Systems', @bca_a_id, 'Process management, memory management, file systems', 3, 'active'),
('CS201', 'Advanced Java Programming', @mca_a_id, 'Advanced Java concepts, frameworks, and enterprise applications', 4, 'active'),
('CS202', 'Machine Learning', @mca_a_id, 'Introduction to ML algorithms and data science', 4, 'active'),
('CS203', 'Cloud Computing', @mca_a_id, 'Cloud platforms, services, and deployment models', 3, 'active'),
('CS204', 'Data Mining', @mca_a_id, 'Data mining techniques and knowledge discovery', 3, 'active')
ON DUPLICATE KEY UPDATE subject_name=subject_name;

-- Insert Sample Students
INSERT INTO students (name, email, roll_number, phone, department, year, section, class_id, status) VALUES
('Amit Singh', 'amit.singh@student.kprcas.ac.in', 'BCA2024001', '9123456781', 'Computer Science', 3, 'A', @bca_a_id, 'active'),
('Neha Patel', 'neha.patel@student.kprcas.ac.in', 'BCA2024002', '9123456782', 'Computer Science', 3, 'A', @bca_a_id, 'active'),
('Rahul Gupta', 'rahul.gupta@student.kprcas.ac.in', 'BCA2024003', '9123456783', 'Computer Science', 3, 'B', @bca_b_id, 'active'),
('Priyanka Reddy', 'priyanka.reddy@student.kprcas.ac.in', 'BSC2024001', '9123456784', 'Computer Science', 2, 'A', @bsc_a_id, 'active'),
('Vijay Kumar', 'vijay.kumar@student.kprcas.ac.in', 'MCA2024001', '9123456785', 'Computer Science', 1, 'A', @mca_a_id, 'active'),
('Sneha Sharma', 'sneha.sharma@student.kprcas.ac.in', 'BCA2024004', '9123456786', 'Computer Science', 3, 'A', @bca_a_id, 'active'),
('Ravi Verma', 'ravi.verma@student.kprcas.ac.in', 'BCA2024005', '9123456787', 'Computer Science', 3, 'A', @bca_a_id, 'active'),
('Anita Desai', 'anita.desai@student.kprcas.ac.in', 'BCA2024006', '9123456788', 'Computer Science', 3, 'B', @bca_b_id, 'active'),
('Kiran Rao', 'kiran.rao@student.kprcas.ac.in', 'MCA2024002', '9123456789', 'Computer Science', 1, 'A', @mca_a_id, 'active'),
('Deepak Joshi', 'deepak.joshi@student.kprcas.ac.in', 'BSC2024002', '9123456790', 'Computer Science', 2, 'A', @bsc_a_id, 'active')
ON DUPLICATE KEY UPDATE name=name;

-- Update student counts
UPDATE classes SET student_count = (SELECT COUNT(*) FROM students WHERE students.class_id = classes.id);

-- Insert Sample Teacher-Subject Assignments
SET @teacher1_id = (SELECT id FROM users WHERE email = 'rajesh.kumar@kprcas.ac.in' LIMIT 1);
SET @teacher2_id = (SELECT id FROM users WHERE email = 'priya.sharma@kprcas.ac.in' LIMIT 1);
SET @teacher3_id = (SELECT id FROM users WHERE email = 'arun.verma@kprcas.ac.in' LIMIT 1);

SET @subject1_id = (SELECT id FROM subjects WHERE subject_code = 'CS101' LIMIT 1);
SET @subject2_id = (SELECT id FROM subjects WHERE subject_code = 'CS102' LIMIT 1);
SET @subject3_id = (SELECT id FROM subjects WHERE subject_code = 'CS103' LIMIT 1);
SET @subject4_id = (SELECT id FROM subjects WHERE subject_code = 'CS201' LIMIT 1);

INSERT INTO teacher_subjects (teacher_id, subject_id, assigned_date, status) VALUES
(@teacher1_id, @subject1_id, CURDATE(), 'active'),
(@teacher1_id, @subject2_id, CURDATE(), 'active'),
(@teacher2_id, @subject3_id, CURDATE(), 'active'),
(@teacher3_id, @subject4_id, CURDATE(), 'active')
ON DUPLICATE KEY UPDATE assigned_date=assigned_date;

-- Insert System Settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('otp_expiry_minutes', '10', 'OTP expiry time in minutes'),
('qr_session_duration', '10', 'Default QR session duration in minutes'),
('attendance_late_threshold', '15', 'Minutes after which attendance is marked late'),
('email_from_name', 'KPRCAS Attendance System', 'Email sender name'),
('email_from_address', 'cloudnetpark@gmail.com', 'Email sender address'),
('system_version', '2.0', 'Current system version'),
('maintenance_mode', '0', 'Maintenance mode flag (0=off, 1=on)')
ON DUPLICATE KEY UPDATE setting_value=setting_value;

-- ====================================
-- USEFUL QUERIES & MAINTENANCE
-- ====================================

-- Verify all tables exist
SHOW TABLES;

-- Check table structures
-- DESCRIBE users;
-- DESCRIBE students;
-- DESCRIBE classes;
-- DESCRIBE subjects;
-- DESCRIBE teacher_subjects;
-- DESCRIBE attendance_sessions;
-- DESCRIBE attendance;

-- Get statistics
-- SELECT 
--     (SELECT COUNT(*) FROM users WHERE user_type = 'admin') as admin_count,
--     (SELECT COUNT(*) FROM users WHERE user_type = 'teacher') as teacher_count,
--     (SELECT COUNT(*) FROM students) as student_count,
--     (SELECT COUNT(*) FROM classes) as class_count,
--     (SELECT COUNT(*) FROM subjects) as subject_count,
--     (SELECT COUNT(*) FROM teacher_subjects) as assignment_count;

-- Clean up expired OTPs (run periodically via cron)
-- DELETE FROM otp_verification WHERE expiry_time < NOW();

-- Clean up old expired attendance sessions (older than 30 days)
-- DELETE FROM attendance_sessions WHERE status = 'expired' AND created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Update student counts for all classes
-- UPDATE classes SET student_count = (SELECT COUNT(*) FROM students WHERE students.class_id = classes.id);

-- Get attendance statistics by class
-- SELECT 
--     c.class_name,
--     c.section,
--     COUNT(DISTINCT a.student_id) as students_present,
--     c.student_count as total_students,
--     ROUND((COUNT(DISTINCT a.student_id) / c.student_count) * 100, 2) as attendance_percentage
-- FROM classes c
-- LEFT JOIN attendance a ON c.id = a.class_id AND a.attendance_date = CURDATE()
-- WHERE c.status = 'active'
-- GROUP BY c.id;

-- Get teacher workload (subjects assigned)
-- SELECT 
--     u.name as teacher_name,
--     u.department,
--     COUNT(ts.id) as subjects_assigned,
--     GROUP_CONCAT(s.subject_name SEPARATOR ', ') as subject_names
-- FROM users u
-- LEFT JOIN teacher_subjects ts ON u.id = ts.teacher_id AND ts.status = 'active'
-- LEFT JOIN subjects s ON ts.subject_id = s.id
-- WHERE u.user_type = 'teacher' AND u.status = 'active'
-- GROUP BY u.id;

-- ====================================
-- BACKUP & RESTORE COMMANDS
-- ====================================

-- Backup database:
-- mysqldump -u root -p kprcas_attendance > backup_$(date +%Y%m%d_%H%M%S).sql

-- Restore database:
-- mysql -u root -p kprcas_attendance < backup_file.sql

-- ====================================
-- SECURITY NOTES
-- ====================================

-- 1. Change default admin password immediately after installation
-- 2. Use strong passwords for all database users
-- 3. Enable SSL for MySQL connections in production
-- 4. Regularly backup the database
-- 5. Monitor login_logs for suspicious activity
-- 6. Keep PHPMailer and other dependencies updated
-- 7. Use prepared statements (already implemented)
-- 8. Sanitize all user inputs (already implemented)
-- 9. Implement rate limiting for OTP requests
-- 10. Enable two-factor authentication for admin users (future enhancement)

-- ====================================
-- PERFORMANCE OPTIMIZATION
-- ====================================

-- Add composite indexes for frequently queried combinations
-- CREATE INDEX idx_student_class_status ON students(class_id, status);
-- CREATE INDEX idx_attendance_date_class ON attendance(attendance_date, class_id);
-- CREATE INDEX idx_session_teacher_date ON attendance_sessions(teacher_id, session_date, status);

-- Analyze tables for query optimization
-- ANALYZE TABLE users, students, classes, subjects, attendance_sessions, attendance;

-- Check table sizes
-- SELECT 
--     table_name AS 'Table',
--     round(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
-- FROM information_schema.TABLES 
-- WHERE table_schema = 'kprcas_attendance'
-- ORDER BY (data_length + index_length) DESC;

-- ====================================
-- END OF SCHEMA
-- ====================================

-- Success message
SELECT 'Database schema created successfully! Total tables: 11' AS Message;
SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'kprcas_attendance' ORDER BY TABLE_NAME;
