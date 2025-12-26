-- STRICT / PURE EAV schema (no relational helper/index tables)
-- Preserves project behavior via READ-ONLY views that mimic the old relational tables.

CREATE DATABASE IF NOT EXISTS ums_eav CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ums_eav;

-- -----------------------------
-- Core EAV tables
-- -----------------------------

CREATE TABLE entities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_type ENUM('user','course','section','enrollment','parent_link','request') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE eav_attributes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_type ENUM('user','course','section','enrollment','parent_link','request') NOT NULL,
    name VARCHAR(64) NOT NULL,
    data_type ENUM('string','text','int','bool') NOT NULL,
    UNIQUE KEY uq_attr (entity_type, name)
);

CREATE TABLE eav_values (
    entity_id INT NOT NULL,
    attribute_id INT NOT NULL,
    value_string VARCHAR(255) NULL,
    value_text TEXT NULL,
    value_int INT NULL,
    value_bool TINYINT(1) NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (entity_id, attribute_id),
    CONSTRAINT fk_eav_entity FOREIGN KEY (entity_id)
        REFERENCES entities(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_eav_attr FOREIGN KEY (attribute_id)
        REFERENCES eav_attributes(id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- -----------------------------
-- Attribute definitions
-- -----------------------------

INSERT INTO eav_attributes (entity_type, name, data_type) VALUES
-- user
('user','name','string'),
('user','email','string'),
('user','password','string'),
('user','role','string'),
('user','program','string'),
('user','level','string'),
-- course
('course','code','string'),
('course','title','string'),
('course','description','text'),
('course','professor_id','int'),
('course','is_core','bool'),
('course','prerequisites','string'),
('course','must_level','string'),
('course','room','string'),
-- section
('section','course_id','int'),
('section','section_number','int'),
('section','professor_id','int'),
('section','capacity','int'),
-- enrollment
('enrollment','student_id','int'),
('enrollment','course_id','int'),
('enrollment','section_id','int'),
('enrollment','grade','string'),
-- parent_link
('parent_link','parent_id','int'),
('parent_link','student_id','int'),
-- request
('request','parent_id','int'),
('request','student_id','int'),
('request','request_type','string'),
('request','status','string'),
('request','message','text'),
('request','reply_note','text');


INSERT INTO eav_attributes (entity_type, name, data_type)
VALUES ('course', 'credit_hours', 'int');


INSERT INTO eav_attributes (entity_type, name, data_type) VALUES
('enrollment', 'midterm', 'int'),
('enrollment', 'activities', 'int'),
('enrollment', 'final', 'int'),
('enrollment', 'total', 'int'),
('enrollment', 'gpa', 'decimal');

INSERT INTO eav_attributes (entity_type, name, data_type)
VALUES ('user', 'credit_hour_price', 'int');


-- -----------------------------
-- Seed data (matches original project)
-- -----------------------------

-- Users
INSERT INTO entities (entity_type) VALUES
('user'),('user'),('user'),('user'),('user'),('user'),('user'),('user'),('user'),('user'),('user'),('user');

-- Helper: set user attributes by entity_id
-- Admin (id=1)
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 1, id, 'System Admin' FROM eav_attributes WHERE entity_type='user' AND name='name';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 1, id, 'admin@ums.edu' FROM eav_attributes WHERE entity_type='user' AND name='email';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 1, id, 'admin123' FROM eav_attributes WHERE entity_type='user' AND name='password';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 1, id, 'admin' FROM eav_attributes WHERE entity_type='user' AND name='role';

-- Students (ids 2..6)
-- (kept plaintext passwords to preserve original behavior)
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 2, id, 'Somaya Ahmed ' FROM eav_attributes WHERE entity_type='user' AND name='name';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 2, id, 'somaya@student.edu' FROM eav_attributes WHERE entity_type='user' AND name='email';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 2, id, 'student123' FROM eav_attributes WHERE entity_type='user' AND name='password';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 2, id, 'student' FROM eav_attributes WHERE entity_type='user' AND name='role';

INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 3, id, 'Habiba Sherif' FROM eav_attributes WHERE entity_type='user' AND name='name';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 3, id, 'habiba@student.edu' FROM eav_attributes WHERE entity_type='user' AND name='email';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 3, id, 'student123' FROM eav_attributes WHERE entity_type='user' AND name='password';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 3, id, 'student' FROM eav_attributes WHERE entity_type='user' AND name='role';

INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 4, id, 'Rowaida Emad' FROM eav_attributes WHERE entity_type='user' AND name='name';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 4, id, 'rowaida@student.edu' FROM eav_attributes WHERE entity_type='user' AND name='email';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 4, id, 'student123' FROM eav_attributes WHERE entity_type='user' AND name='password';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 4, id, 'student' FROM eav_attributes WHERE entity_type='user' AND name='role';

INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 5, id, 'Ahmed Sherif' FROM eav_attributes WHERE entity_type='user' AND name='name';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 5, id, 'ahmed@student.edu' FROM eav_attributes WHERE entity_type='user' AND name='email';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 5, id, 'student123' FROM eav_attributes WHERE entity_type='user' AND name='password';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 5, id, 'student' FROM eav_attributes WHERE entity_type='user' AND name='role';

INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 6, id, 'Rawan Hany' FROM eav_attributes WHERE entity_type='user' AND name='name';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 6, id, 'rawan@student.edu' FROM eav_attributes WHERE entity_type='user' AND name='email';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 6, id, 'student123' FROM eav_attributes WHERE entity_type='user' AND name='password';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 6, id, 'student' FROM eav_attributes WHERE entity_type='user' AND name='role';

-- Professors (ids 7..12)
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 7, id, 'Dr. Mohamed Hassan El Gazzar' FROM eav_attributes WHERE entity_type='user' AND name='name';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 7, id, 'Drmohamed@prof.edu' FROM eav_attributes WHERE entity_type='user' AND name='email';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 7, id, 'prof123' FROM eav_attributes WHERE entity_type='user' AND name='password';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 7, id, 'professor' FROM eav_attributes WHERE entity_type='user' AND name='role';

INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 8, id, 'Eng. Abdelrahman Salah' FROM eav_attributes WHERE entity_type='user' AND name='name';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 8, id, 'Engabdelrahman@prof.edu' FROM eav_attributes WHERE entity_type='user' AND name='email';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 8, id, 'prof123' FROM eav_attributes WHERE entity_type='user' AND name='password';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 8, id, 'professor' FROM eav_attributes WHERE entity_type='user' AND name='role';

INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 9, id, 'Dr. Mahmoud Khalil' FROM eav_attributes WHERE entity_type='user' AND name='name';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 9, id, 'drKhalil@prof.edu' FROM eav_attributes WHERE entity_type='user' AND name='email';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 9, id, 'prof123' FROM eav_attributes WHERE entity_type='user' AND name='password';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 9, id, 'professor' FROM eav_attributes WHERE entity_type='user' AND name='role';

INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 10, id, 'Dr. Ayman Bahaa' FROM eav_attributes WHERE entity_type='user' AND name='name';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 10, id, 'drayman@prof.edu' FROM eav_attributes WHERE entity_type='user' AND name='email';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 10, id, 'prof123' FROM eav_attributes WHERE entity_type='user' AND name='password';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 10, id, 'professor' FROM eav_attributes WHERE entity_type='user' AND name='role';

INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 11, id, 'Dr. Nabil hamed' FROM eav_attributes WHERE entity_type='user' AND name='name';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 11, id, 'drnabil@prof.edu' FROM eav_attributes WHERE entity_type='user' AND name='email';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 11, id, 'prof123' FROM eav_attributes WHERE entity_type='user' AND name='password';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 11, id, 'professor' FROM eav_attributes WHERE entity_type='user' AND name='role';

INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 12, id, 'Dr. Sherif hamed' FROM eav_attributes WHERE entity_type='user' AND name='name';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 12, id, 'drSherif@prof.edu' FROM eav_attributes WHERE entity_type='user' AND name='email';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 12, id, 'prof123' FROM eav_attributes WHERE entity_type='user' AND name='password';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 12, id, 'professor' FROM eav_attributes WHERE entity_type='user' AND name='role';

-- Student extra attributes (program/level)
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 2, id, 'Computer Science' FROM eav_attributes WHERE entity_type='user' AND name='program';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 2, id, '2' FROM eav_attributes WHERE entity_type='user' AND name='level';

INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 3, id, 'Computer Science' FROM eav_attributes WHERE entity_type='user' AND name='program';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 3, id, '3' FROM eav_attributes WHERE entity_type='user' AND name='level';

INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 4, id, 'Information Systems' FROM eav_attributes WHERE entity_type='user' AND name='program';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 4, id, '1' FROM eav_attributes WHERE entity_type='user' AND name='level';

INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 5, id, 'Software Engineering' FROM eav_attributes WHERE entity_type='user' AND name='program';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 5, id, '4' FROM eav_attributes WHERE entity_type='user' AND name='level';

INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 6, id, 'Cyber Security' FROM eav_attributes WHERE entity_type='user' AND name='program';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 6, id, '2' FROM eav_attributes WHERE entity_type='user' AND name='level';



-- Courses
INSERT INTO entities (entity_type) VALUES ('course'),('course'),('course'),('course');
-- course ids start at 13
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 13, id, 'CS223' FROM eav_attributes WHERE entity_type='course' AND name='code';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 13, id, 'Agile Software Engineering' FROM eav_attributes WHERE entity_type='course' AND name='title';
INSERT INTO eav_values (entity_id, attribute_id, value_text)
SELECT 13, id, 'Software engineering' FROM eav_attributes WHERE entity_type='course' AND name='description';
INSERT INTO eav_values (entity_id, attribute_id, value_int)
SELECT 13, id, 7 FROM eav_attributes WHERE entity_type='course' AND name='professor_id';
INSERT INTO eav_values (entity_id, attribute_id, value_bool)
SELECT 13, id, 0 FROM eav_attributes WHERE entity_type='course' AND name='is_core';

INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 14, id, 'CSE351' FROM eav_attributes WHERE entity_type='course' AND name='code';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 14, id, 'Computer networks' FROM eav_attributes WHERE entity_type='course' AND name='title';
INSERT INTO eav_values (entity_id, attribute_id, value_text)
SELECT 14, id, 'layers of internet' FROM eav_attributes WHERE entity_type='course' AND name='description';
INSERT INTO eav_values (entity_id, attribute_id, value_int)
SELECT 14, id, 10 FROM eav_attributes WHERE entity_type='course' AND name='professor_id';
INSERT INTO eav_values (entity_id, attribute_id, value_bool)
SELECT 14, id, 0 FROM eav_attributes WHERE entity_type='course' AND name='is_core';

INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 15, id, 'CSE211' FROM eav_attributes WHERE entity_type='course' AND name='code';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 15, id, 'Intro to Embedded' FROM eav_attributes WHERE entity_type='course' AND name='title';
INSERT INTO eav_values (entity_id, attribute_id, value_text)
SELECT 15, id, 'ARM microprocessor' FROM eav_attributes WHERE entity_type='course' AND name='description';
INSERT INTO eav_values (entity_id, attribute_id, value_int)
SELECT 15, id, 12 FROM eav_attributes WHERE entity_type='course' AND name='professor_id';
INSERT INTO eav_values (entity_id, attribute_id, value_bool)
SELECT 15, id, 0 FROM eav_attributes WHERE entity_type='course' AND name='is_core';

INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 16, id, 'EMP119' FROM eav_attributes WHERE entity_type='course' AND name='code';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT 16, id, 'Engineering Economy' FROM eav_attributes WHERE entity_type='course' AND name='title';
INSERT INTO eav_values (entity_id, attribute_id, value_text)
SELECT 16, id, 'Time value of money and economic comparisons' FROM eav_attributes WHERE entity_type='course' AND name='description';
INSERT INTO eav_values (entity_id, attribute_id, value_int)
SELECT 16, id, 11 FROM eav_attributes WHERE entity_type='course' AND name='professor_id';
INSERT INTO eav_values (entity_id, attribute_id, value_bool)
SELECT 16, id, 0 FROM eav_attributes WHERE entity_type='course' AND name='is_core';


-- Parent user seed (for testing parent features)
INSERT INTO entities (entity_type) VALUES ('user');
SET @parent_id := LAST_INSERT_ID();
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT @parent_id, id, 'Parent One' FROM eav_attributes WHERE entity_type='user' AND name='name';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT @parent_id, id, 'parent@ums.edu' FROM eav_attributes WHERE entity_type='user' AND name='email';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT @parent_id, id, 'parent123' FROM eav_attributes WHERE entity_type='user' AND name='password';
INSERT INTO eav_values (entity_id, attribute_id, value_string)
SELECT @parent_id, id, 'parent' FROM eav_attributes WHERE entity_type='user' AND name='role';

-- CS223 – Agile Software Engineering (3 credits)
INSERT INTO eav_values (entity_id, attribute_id, value_int)
SELECT 13, id, 3 FROM eav_attributes WHERE entity_type='course' AND name='credit_hours';

-- CSE351 – Computer Networks (4 credits)
INSERT INTO eav_values (entity_id, attribute_id, value_int)
SELECT 14, id, 4 FROM eav_attributes WHERE entity_type='course' AND name='credit_hours';

-- CSE211 – Intro to Embedded (3 credits)
INSERT INTO eav_values (entity_id, attribute_id, value_int)
SELECT 15, id, 3 FROM eav_attributes WHERE entity_type='course' AND name='credit_hours';

-- EMP119 – Engineering Economy (2 credits)
INSERT INTO eav_values (entity_id, attribute_id, value_int)
SELECT 16, id, 2 FROM eav_attributes WHERE entity_type='course' AND name='credit_hours';

-- -----------------------------
-- READ-ONLY views (compatibility)
-- -----------------------------

-- Users view (matches original `users` table)
CREATE OR REPLACE VIEW users AS
SELECT
    e.id,
    MAX(CASE WHEN a.name='name' THEN v.value_string END) AS name,
    MAX(CASE WHEN a.name='email' THEN v.value_string END) AS email,
    MAX(CASE WHEN a.name='password' THEN v.value_string END) AS password,
    MAX(CASE WHEN a.name='role' THEN v.value_string END) AS role,
    MAX(CASE WHEN a.name='program' THEN v.value_string END) AS program,
    MAX(CASE WHEN a.name='level' THEN v.value_string END) AS level
FROM entities e
LEFT JOIN eav_values v ON v.entity_id = e.id
LEFT JOIN eav_attributes a ON a.id = v.attribute_id AND a.entity_type='user'
WHERE e.entity_type='user'
GROUP BY e.id;

-- Courses view (matches original `courses` table)
CREATE OR REPLACE VIEW courses AS
SELECT
    e.id,
    MAX(CASE WHEN a.name='code' THEN v.value_string END) AS code,
    MAX(CASE WHEN a.name='title' THEN v.value_string END) AS title,
    MAX(CASE WHEN a.name='description' THEN v.value_text END) AS description,
    MAX(CASE WHEN a.name='professor_id' THEN v.value_int END) AS professor_id,
    COALESCE(MAX(CASE WHEN a.name='is_core' THEN v.value_bool END), 0) AS is_core,
    MAX(CASE WHEN a.name='prerequisites' THEN v.value_string END) AS prerequisites,
    MAX(CASE WHEN a.name='must_level' THEN v.value_string END) AS must_level,
    MAX(CASE WHEN a.name='room' THEN v.value_string END) AS room,
    MAX(CASE WHEN a.name='credit_hours' THEN v.value_int END) AS credit_hours
FROM entities e
LEFT JOIN eav_values v ON v.entity_id = e.id
LEFT JOIN eav_attributes a ON a.id = v.attribute_id
WHERE e.entity_type='course'
GROUP BY e.id;



-- Sections view (matches original `sections` table)
CREATE OR REPLACE VIEW sections AS
SELECT
    e.id,
    MAX(CASE WHEN a.name='course_id' THEN v.value_int END) AS course_id,
    MAX(CASE WHEN a.name='section_number' THEN v.value_int END) AS section_number,
    MAX(CASE WHEN a.name='professor_id' THEN v.value_int END) AS professor_id,
    COALESCE(MAX(CASE WHEN a.name='capacity' THEN v.value_int END), 40) AS capacity,
    e.created_at,
    e.updated_at
FROM entities e
LEFT JOIN eav_values v ON v.entity_id = e.id
LEFT JOIN eav_attributes a ON a.id = v.attribute_id AND a.entity_type='section'
WHERE e.entity_type='section'
GROUP BY e.id;

-- Enrollments view (matches original `enrollments` table)
CREATE OR REPLACE VIEW enrollments AS
SELECT
    e.id,
    MAX(CASE WHEN a.name='student_id' THEN v.value_int END) AS student_id,
    MAX(CASE WHEN a.name='course_id' THEN v.value_int END) AS course_id,
    MAX(CASE WHEN a.name='section_id' THEN v.value_int END) AS section_id
FROM entities e
LEFT JOIN eav_values v ON v.entity_id = e.id
LEFT JOIN eav_attributes a ON a.id = v.attribute_id AND a.entity_type='enrollment'
WHERE e.entity_type='enrollment'
GROUP BY e.id;

-- Grades view (legacy-compatible, READ-ONLY)
-- We expose a synthetic `id` equal to `enrollment_id`.
CREATE OR REPLACE VIEW grades AS
SELECT
    e.id AS id,
    e.id AS enrollment_id,
    MAX(CASE WHEN a.name='grade' THEN v.value_string END) AS grade
FROM entities e
LEFT JOIN eav_values v ON v.entity_id = e.id
LEFT JOIN eav_attributes a ON a.id = v.attribute_id AND a.entity_type='enrollment'
WHERE e.entity_type='enrollment'
GROUP BY e.id;

/* ============================================================
   SCHEDULING (Manual bookings)
   ============================================================ */

CREATE TABLE IF NOT EXISTS room_schedule (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,

  -- When this booking happens
  slot_date    DATE NOT NULL,             -- e.g., 2025-01-03
  start_time   TIME NOT NULL,             -- whole hour: 08:00:00..19:00:00
  end_time     TIME NOT NULL,             -- always start_time + 1 hour
  -- Which room (numeric attribute; no separate rooms table needed)
  room_number  INT  NOT NULL,             -- e.g., 101
  -- What is booked
  course_id    INT  NOT NULL,             -- matches courses.id (view-backed)
  section_id   INT  NULL,                 -- optional if you schedule per section
  -- CONFLICT GUARDS:
  UNIQUE KEY uq_room_slot   (room_number, slot_date, start_time),
  UNIQUE KEY uq_course_slot (course_id,  slot_date, start_time),

  CONSTRAINT chk_one_hour
    CHECK (TIME_TO_SEC(end_time) - TIME_TO_SEC(start_time) = 3600)
);
CREATE INDEX idx_schedule_week ON room_schedule (slot_date, start_time);

CREATE OR REPLACE VIEW room_schedule_v AS
SELECT
  s.id,
  s.slot_date,
  s.start_time,
  s.end_time,
  s.room_number,
  s.course_id,
  c.code  AS course_code,
  c.title AS course_title,
  s.section_id
FROM room_schedule s
LEFT JOIN courses c ON c.id = s.course_id
ORDER BY s.slot_date, s.start_time, s.room_number;


-- ===== Announcements (EAV attributes) =====

/* 1) Allow 'announcement' in the entity_type ENUMs */
ALTER TABLE entities
  MODIFY entity_type ENUM('user','course','section','enrollment','parent_link','request','announcement') NOT NULL;

ALTER TABLE eav_attributes
  MODIFY entity_type ENUM('user','course','section','enrollment','parent_link','request','announcement') NOT NULL;

-- Entity type: 'announcement'
INSERT INTO eav_attributes (entity_type, name, data_type) VALUES
('announcement','title','string'),
('announcement','message','text'),
('announcement','audience','string'),   -- 'students', 'parents', 'all'
('announcement','created_by','int');    -- user.id of admin

-- View: announcements (read-only convenience)
CREATE OR REPLACE VIEW announcements AS
SELECT
  e.id,
  MAX(CASE WHEN a.name='title'       THEN v.value_string END) AS title,
  MAX(CASE WHEN a.name='message'     THEN v.value_text   END) AS message,
  MAX(CASE WHEN a.name='audience'    THEN v.value_string END) AS audience,
  MAX(CASE WHEN a.name='created_by'  THEN v.value_int    END) AS created_by,
  e.created_at
FROM entities e
LEFT JOIN eav_values v ON v.entity_id = e.id
LEFT JOIN eav_attributes a ON a.id = v.attribute_id AND a.entity_type='announcement'
WHERE e.entity_type='announcement'
GROUP BY e.id, e.created_at;


INSERT IGNORE INTO eav_attributes (entity_type, name, data_type) VALUES
('announcement','title','string'),
('announcement','message','text'),
('announcement','audience','string'),
('announcement','created_by','int');

