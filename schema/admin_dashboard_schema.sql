-- Admin Dashboard Database Schema
-- Tables for Classes, Subjects, and Teacher-Subject Assignment

-- ====================================
-- Classes Table
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
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================================
-- Subjects Table
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
    INDEX idx_class (class_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================================
-- Teacher-Subject Assignment Table
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
    INDEX idx_teacher (teacher_id),
    INDEX idx_subject (subject_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ====================================
-- Update Students Table to include class_id
-- ====================================
ALTER TABLE students 
ADD COLUMN class_id INT NULL AFTER section,
ADD FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
ADD INDEX idx_class (class_id);

-- ====================================
-- Sample Data for Testing
-- ====================================

-- Insert Sample Classes
INSERT INTO classes (class_name, section, academic_year, student_count) VALUES
('BCA', 'A', '2024-2025', 0),
('BCA', 'B', '2024-2025', 0),
('MCA', 'A', '2024-2025', 0),
('B.Sc CS', 'A', '2024-2025', 0);

-- Insert Sample Subjects
INSERT INTO subjects (subject_code, subject_name, class_id, description, credits) VALUES
('CS101', 'Data Structures', 1, 'Introduction to data structures and algorithms', 4),
('CS102', 'Database Management', 1, 'Relational database concepts and SQL', 4),
('CS103', 'Web Technologies', 1, 'HTML, CSS, JavaScript, PHP', 3),
('CS201', 'Advanced Java', 2, 'Advanced Java programming and frameworks', 4),
('CS202', 'Machine Learning', 2, 'Introduction to ML algorithms', 4);

-- ====================================
-- Useful Queries
-- ====================================

-- Get all classes with student count
-- SELECT * FROM classes WHERE status = 'active';

-- Get subjects by class
-- SELECT s.* FROM subjects s WHERE s.class_id = ? AND s.status = 'active';

-- Get teachers assigned to a subject
-- SELECT u.* FROM users u 
-- JOIN teacher_subjects ts ON u.id = ts.teacher_id 
-- WHERE ts.subject_id = ? AND ts.status = 'active';

-- Get subjects assigned to a teacher
-- SELECT s.* FROM subjects s 
-- JOIN teacher_subjects ts ON s.id = ts.subject_id 
-- WHERE ts.teacher_id = ? AND ts.status = 'active';

-- Update student count for a class
-- UPDATE classes SET student_count = (SELECT COUNT(*) FROM students WHERE class_id = classes.id) WHERE id = ?;
