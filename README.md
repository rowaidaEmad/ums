#  University Management System (UMS)

A simple web-based **University Management System** built with **PHP** and **MySQL**.

It supports three roles:

- **Admin** – manages courses, sections, and room assignments  
- **Professor** – views assigned courses and manages student grades  
- **Student** – registers for courses, views enrollments, and checks grades  

This project is intended for **learning / coursework** and is **not production-ready** (e.g., passwords are stored in plain text).

---

## 📑 Table of Contents

- [Features](#-features)
  - [Admin](#admin-features)
  - [Professor](#professor-features)
  - [Student](#student-features)
- [Technology Stack](#-technology-stack)
- [Project Structure](#-project-structure)
- [Database Schema](#-database-schema)
  - [Main Tables](#main-tables)
  - [Seed Data (Demo Accounts)](#seed-data-demo-accounts)
- [Installation & Setup](#-installation--setup)
  - [1. Requirements](#1-requirements)
  - [2. Database Setup](#2-database-setup)
  - [3. Configure the Application](#3-configure-the-application)
  - [4. Run the Application](#4-run-the-application)
- [Usage](#-usage)
  - [Admin Workflow](#admin-workflow)
  - [Professor Workflow](#professor-workflow)
  - [Student Workflow](#student-workflow)
- [Tests](#-tests)
- [Security Notes & Limitations](#-security-notes--limitations)
- [Future Improvements](#-future-improvements)

---

##  Features

### Admin Features

Available through:

- `admin_dashboard.php`
- `admin_courses.php`
- `admin_sections.php`
- `admin_room.php`

**Admin can:**

- View an **Admin Dashboard** with quick-access cards.
- **Manage courses**:
  - Create / edit / delete courses.
  - Assign a professor to each course.
  - Mark courses as **core** (`is_core`).
  - Add optional **prerequisites** and **required level** (`must_level`).
- **Manage sections**:
  - Create sections for a course (section numbers `1–4`).
  - Set a **capacity** per section (max number of students).
  - Optionally override which professor is teaching a section.
  - Delete sections.
- **Assign rooms**:
  - Link a `room` to a course (e.g., room 101, 202, etc.).

---

### Professor Features

Available through:

- `professor_dashboard.php`
- `professor_courses.php`
- `professor_grades.php`

**Professor can:**

- View a **Professor Dashboard** with a shortcut to:
  - **My Courses** – list of all courses assigned to the logged-in professor.
- From the **courses page**:
  - See course **code, title, description, level, core flag, and room**.
  - Navigate to manage **grades** for a course.
- From the **grades page** (`professor_grades.php`):
  - View all students enrolled in a course.
  - See each student’s **name** and **current grade** (if any).
  - Enter or update grades for each student (grades stored in `grades` table).
  - Grades are stored per **enrollment**, not per user directly.

---

### Student Features

Available through:

- `student_dashboard.php`
- `student_register.php`
- `student_courses.php`

**Student can:**

- View a **Student Dashboard** with:
  - Card for **Course Registration** (`student_register.php`)
  - Card for **My Courses / Grades** (`student_courses.php`)
- On **Course Registration** (`student_register.php`):
  - See all available **courses**.
  - See all **sections** for each course.
  - See **current occupancy vs capacity** per section.
  - Enroll in a section of a course (if:
    - Not already enrolled in that course, and  
    - Section is not full).
  - Unenroll from a course.
- On **My Courses / Grades** (`student_courses.php`):
  - See all courses/sections the student is enrolled in.
  - See assigned professor and grade (or “Not graded” if none yet).

---

##  Technology Stack

- **Language:** PHP (pure PHP, no framework)
- **Server:** Apache (e.g., XAMPP, WAMP, LAMP)
- **Database:** MySQL
- **Frontend:** HTML5, CSS, [Bootstrap 5](https://getbootstrap.com/)
- **Testing:** PHPUnit, Codeception (configured via `composer.json`)

---

