-- Create database
CREATE DATABASE IF NOT EXISTS ums CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ums;

-- Users table: admin, students, professors
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    role ENUM('admin', 'student', 'professor') NOT NULL
);

-- Courses offered by the university
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    professor_id INT,
    FOREIGN KEY (professor_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE
);

-- Student course registrations
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    -- This enforces: one section per course per student
    UNIQUE KEY unique_enrollment (student_id, course_id),
    FOREIGN KEY (student_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- Grades per enrollment
CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL UNIQUE,
    grade VARCHAR(5),
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- Sample users
INSERT INTO users (name, email, password, role) VALUES
('System Admin', 'admin@ums.edu', 'admin123', 'admin'),
('Somaya Ahmed ', 'somaya@student.edu', 'student123', 'student'),
('Habiba Sherif', 'habiba@student.edu', 'student123', 'student'),
('Rowaida Emad', 'rowaida@student.edu', 'student123', 'student'),
('Ahmed Sherif', 'ahmed@student.edu', 'student123', 'student'),
('Rawan Hany', 'rawan@student.edu', 'student123', 'student'),
('Dr. Mohamed Hassan El Gazzar', 'Drmohamed@prof.edu', 'prof123', 'professor'),
('Eng. Abdelrahman Salah', 'Engabdelrahman@prof.edu', 'prof123', 'professor'),
('Dr. Mahmoud Khalil', 'drKhalil@prof.edu', 'prof123', 'professor'),
('Dr. Ayman Bahaa', 'drayman@prof.edu', 'prof123', 'professor'),
('Dr. Nabil hamed', 'drnabil@prof.edu', 'prof123', 'professor'),
('Dr. Sherif hamed', 'drSherif@prof.edu', 'prof123', 'professor');

-- Extra columns for courses (core, prerequisites, required level)
ALTER TABLE courses
    ADD COLUMN is_core TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN prerequisites VARCHAR(255),
    ADD COLUMN must_level VARCHAR(10);

-- Sample courses
INSERT INTO courses (code, title, description, professor_id) VALUES
('CS223', 'Agile Software Engineering', 'Software engineering', 7),
('CSE351', 'Computer networks', 'layers of internet', 10),
('CSE211', 'Intro to Embedded', 'ARM microprocessor', 12),
('EMP119', 'Engineering Economy', 'Time value of money and economic comparisons', 11);

-- Sections table
CREATE TABLE sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    section_number TINYINT NOT NULL,
    professor_id INT DEFAULT NULL,
    capacity INT NOT NULL DEFAULT 40,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_course_section (course_id, section_number),
    CONSTRAINT fk_sections_course FOREIGN KEY (course_id) REFERENCES courses(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_sections_prof FOREIGN KEY (professor_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE
);

-- Enforce section_number between 1 and 4
ALTER TABLE sections
    ADD CONSTRAINT chk_section_number CHECK (section_number BETWEEN 1 AND 4);

-- Room column for courses (used by admin_room.php)
ALTER TABLE courses
    ADD COLUMN room VARCHAR(50) DEFAULT NULL;

-- Add section_id to enrollments and link to sections
ALTER TABLE enrollments
    ADD COLUMN section_id INT NOT NULL AFTER course_id,
    ADD CONSTRAINT fk_enroll_section FOREIGN KEY (section_id)
        REFERENCES sections(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    -- This prevents enrolling twice in the same section
    ADD UNIQUE KEY unique_enrollment_section (student_id, section_id);
