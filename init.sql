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
('System Admin', 'admin@ums.test', 'admin123', 'admin'),
('Alice Student', 'alice@student.test', 'student123', 'student'),
('Bob Student', 'bob@student.test', 'student123', 'student'),
('Dr. Smith', 'smith@prof.test', 'prof123', 'professor'),
('Dr. Jones', 'jones@prof.test', 'prof123', 'professor');

-- Sample courses
INSERT INTO courses (code, title, description, professor_id) VALUES
('CS101', 'Intro to Computer Science', 'Basics of CS.', 4),
('MATH201', 'Linear Algebra', 'Matrices and vectors.', 5);
