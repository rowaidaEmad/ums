# University Management System (UMS) — PHP + MySQL (EAV Database Model)

The **University Management System (UMS)** is a role-based web application developed using **PHP** and **MySQL**.  
It provides a complete academic management platform for universities, supporting:

- User management  
- Course and section management  
- Student registration  
- Professor grading  
- Parent monitoring and requests  
- Messaging system  
- Announcements  
- Room scheduling  

This version of the project is built using a **pure EAV (Entity–Attribute–Value)** database model instead of traditional relational tables.

---

## Table of Contents

- [Tech Stack](#tech-stack)
- [System Roles](#system-roles)
- [Main Features](#main-features)
- [How to Run the Project](#how-to-run-the-project)
- [Demo Accounts](#demo-accounts)
- [Database Design — EAV Model](#database-design--eav-model)
- [Project Structure](#project-structure)
- [Troubleshooting](#troubleshooting)
- [Limitations](#limitations)
- [License](#license)

---

## Tech Stack

- **PHP 8+**
- **MySQL**
- **PDO for database interaction**
- **Apache Server (XAMPP/WAMP/LAMP)**
- **Bootstrap CSS**

---

## System Roles

| Role       | Description |
|-----------|-------------|
| Admin     | Full system control and management |
| Student   | Course registration and academic tracking |
| Professor | Teaching, student grading, course management |
| Parent    | Student monitoring and request submission |

---

## Main Features

### Admin Features

Admins have full access to manage the university system:

- Create and manage users (students, professors, parents, admins)
- Add, edit, and delete courses
- Create sections and assign professors
- Manage section capacity and enrollment
- Link and unlink parents to students
- Approve/reject/respond to parent requests
- Post announcements for all roles
- Schedule rooms and time slots for courses

---

### Student Features

Students can:

- View all available courses
- Register in course sections
- View enrolled courses
- View grades once submitted
- Receive announcements
- Send and receive messages

---

### Professor Features

Professors can:

- View assigned courses and sections
- View enrolled students
- Submit and update grades
- Communicate with students via messaging

---

### Parent Features

Parents can:

- View linked student academic information
- Submit requests to administration
- Track request approval/rejection status
- View announcements intended for parents

---

## How to Run the Project

### 1. Requirements

- PHP **8.0 or higher**
- MySQL database server
- Apache server (XAMPP recommended)
- PDO MySQL extension enabled

---

### 2. Setup with XAMPP

1. Install **XAMPP**
2. Start **Apache** and **MySQL**
3. Copy the project folder into:

```
C:\xampp\htdocs\
```

Example:

```
C:\xampp\htdocs\ums-final-branch\
```

---

### 3. Database Installation

1. Open phpMyAdmin:

```
http://localhost/phpmyadmin
```

2. Import:

```
init.sql
```

This automatically:

- Creates database `ums_eav`
- Creates all EAV tables
- Inserts demo accounts
- Creates SQL views

---

### 4. Configure Database Connection

Edit:

```
config.php
```

Default:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ums_eav');
define('DB_USER', 'root');
define('DB_PASS', '');
```

---

### 5. Start the Application

Open:

```
http://localhost/ums-final-branch/index.php
```

---

## Demo Accounts

### Admin

- Email: `admin@ums.edu`
- Password: `admin123`

### Student

- Email: `somaya@student.edu`
- Password: `student123`

### Professor

- Email: `Drmohamed@prof.edu`
- Password: `prof123`

### Parent

- Email: `parent@ums.edu`
- Password: `parent123`

---

## Database Design — EAV Model

### What is EAV?

EAV stands for:

**Entity – Attribute – Value**

Instead of fixed columns, values are stored dynamically.

Example:

| entity_id | attribute | value |
|----------|----------|------|
| 1        | name     | Rowaida |
| 1        | email    | rowaida@ums.edu |

---

### Why This Project Uses EAV

UMS contains many entity types:

- Users
- Courses
- Sections
- Requests
- Messages
- Announcements

Each has different attributes, so EAV provides flexibility.

---

### EAV Core Tables

#### 1. `entities`

Stores each object:

| entity_id | entity_type |
|----------|-------------|
| 1        | user        |
| 10       | course      |

---

#### 2. `eav_attributes`

Defines allowed attributes:

| attr_id | entity_type | attr_name | data_type |
|--------|-------------|----------|----------|

---

#### 3. `eav_values`

Stores values:

| entity_id | attr_id | value_string | value_int |
|----------|---------|--------------|----------|

Supported types:

- `value_string`
- `value_text`
- `value_int`
- `value_bool`

---

### SQL Views for Easier Queries

To simplify, SQL views are created:

- `users`
- `courses`
- `sections`
- `grades`
- `messages`

So PHP can query:

```sql
SELECT * FROM users WHERE email=?;
```

---

### EAV Helper Functions in PHP

File:

```
eav.php
```

Key functions:

- `eav_create_entity()`
- `eav_set()`
- `eav_get()`

Example:

```php
$course_id = eav_create_entity("course");
eav_set($course_id, "course", "title", "Database Systems");
```

---

## Project Structure

```
ums-final-branch/
│── index.php
│── auth.php
│── config.php
│── db.php
│── eav.php
│── init.sql
│
├── admin_*.php
├── student_*.php
├── professor_*.php
├── parent_*.php
│
├── header.php
├── footer.php
└── bootstrap.min.css
```

---

## Troubleshooting

### Database Connection Error

- Ensure MySQL is running
- Check `config.php`
- Import `init.sql`

---

### Blank Page

- Use PHP 8+
- Check Apache logs

---

### Parent Dashboard Empty

Admin must link parent to a student first.

---

## Limitations

- Passwords are stored in plaintext (demo only)
- EAV is slower for large-scale systems
- Educational project only

---

## License

Educational and academic use only.
