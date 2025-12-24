-- =====================================================
-- Create Database
-- =====================================================
CREATE DATABASE IF NOT EXISTS ums_eav
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE ums_eav;

-- =====================================================
-- 1. ENTITY TABLE
-- =====================================================
CREATE TABLE entities (
    entity_id INT AUTO_INCREMENT PRIMARY KEY,
    entity_type ENUM('user', 'course', 'section', 'enrollment') NOT NULL
);

-- =====================================================
-- 2. ATTRIBUTE DEFINITIONS
-- =====================================================
CREATE TABLE attributes (
    attribute_id INT AUTO_INCREMENT PRIMARY KEY,
    entity_type ENUM('user', 'course', 'section', 'enrollment') NOT NULL,
    attribute_name VARCHAR(50) NOT NULL,
    data_type ENUM('string', 'int', 'boolean') NOT NULL,
    UNIQUE KEY uq_entity_attr_name (entity_type, attribute_name)
);

-- =====================================================
-- 3. ATTRIBUTE VALUES (EAV CORE)
-- =====================================================
CREATE TABLE values_eav (
    value_id INT AUTO_INCREMENT PRIMARY KEY,
    entity_id INT NOT NULL,
    attribute_id INT NOT NULL,
    value_string VARCHAR(255),
    value_int INT,
    value_boolean TINYINT(1),
    FOREIGN KEY (entity_id) REFERENCES entities(entity_id) ON DELETE CASCADE,
    FOREIGN KEY (attribute_id) REFERENCES attributes(attribute_id) ON DELETE CASCADE,
    UNIQUE KEY uq_entity_attribute (entity_id, attribute_id)
);

-- =====================================================
-- 4. RELATIONSHIP TABLES
-- =====================================================

-- Sections belong to Courses
CREATE TABLE course_sections (
    section_entity_id INT PRIMARY KEY,
    course_entity_id INT NOT NULL,
    FOREIGN KEY (section_entity_id) REFERENCES entities(entity_id),
    FOREIGN KEY (course_entity_id) REFERENCES entities(entity_id)
);

-- Enrollments (Student ↔ Section)
CREATE TABLE enrollments (
    enrollment_entity_id INT PRIMARY KEY,
    student_entity_id INT NOT NULL,
    section_entity_id INT NOT NULL,
    UNIQUE KEY uq_student_section (student_entity_id, section_entity_id),
    FOREIGN KEY (enrollment_entity_id) REFERENCES entities(entity_id),
    FOREIGN KEY (student_entity_id) REFERENCES entities(entity_id),
    FOREIGN KEY (section_entity_id) REFERENCES entities(entity_id)
);

-- =====================================================
-- 5. ATTRIBUTE SETUP
-- =====================================================

-- USER ATTRIBUTES
INSERT INTO attributes (entity_type, attribute_name, data_type) VALUES
('user', 'name', 'string'),
('user', 'email', 'string'),
('user', 'password', 'string'),
('user', 'role', 'string');

-- COURSE ATTRIBUTES
INSERT INTO attributes (entity_type, attribute_name, data_type) VALUES
('course', 'code', 'string'),
('course', 'title', 'string'),
('course', 'description', 'string'),
('course', 'is_core', 'boolean'),
('course', 'room', 'string'),
('course', 'must_level', 'string'),
('course', 'professor_id', 'int');

-- SECTION ATTRIBUTES
INSERT INTO attributes (entity_type, attribute_name, data_type) VALUES
('section', 'section_number', 'int'),
('section', 'capacity', 'int'),
('section', 'professor_id', 'int');

-- ENROLLMENT ATTRIBUTES
INSERT INTO attributes (entity_type, attribute_name, data_type) VALUES
('enrollment', 'grade', 'string');

