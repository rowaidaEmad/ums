# University Management System (UMS)

A complete PHP + MySQL web-based **University Management System** supporting three user roles:

- **Admin**
- **Professor**
- **Student**

The system allows course management, section assignments, room scheduling, student enrollment, grade submissions, and more. This project is created for learning, assignments, and academic demonstration.

---

## 📑 Table of Contents

- [Features](#features)
- [Technology Stack](#technology-stack)
- [Database Schema](#database-schema)
- [Installation & Setup](#installation--setup)


---

##  Features

### **Admin Features**
- Manage Courses  
  - Add / Edit / Delete  
  - Assign professors  
  - Add prerequisites  
  - Mark course as core or level-based  
- Manage Sections  
  - Create up to 4 sections per course  
  - Set section capacity  
  - Assign section-specific professor  
- Assign Rooms for courses  
- Full dashboard with shortcuts  

### **Professor Features**
- View assigned courses  
- Manage grades for students enrolled in their courses  
- Update grades any time  
- Dashboard with course shortcuts  

### **Student Features**
- View available courses & free seats  
- Register in course sections  
- Unregister anytime  
- View grades  
- Dashboard with course & registration tools  

---

## Technology Stack

| Component     | Technology |
|--------------|------------|
| Backend      | PHP (Native) |
| Frontend     | HTML, CSS, Bootstrap |
| Database     | MySQL |
| Testing      | PHPUnit, Codeception |
| Authentication | Session-based |

---


---

## Database Schema

The file **`init.sql`** creates the entire system database.

### **Main Tables**
- `users` – all system accounts  
- `courses` – available courses  
- `sections` – course sections (1–4)  
- `enrollments` – student registrations  
- `grades` – grades linked to enrollments  

### **Demo Accounts (from init.sql)**

#### **Admin**
| Email | Password |
|-------|----------|
| admin@ums.edu | admin123 |

#### **Professors** (Password: `prof123`)
`Drmohamed@prof.edu`, `Engabdelrahman@prof.edu`, `drKhalil@prof.edu`,  
`drayman@prof.edu`, `drnabil@prof.edu`, `drSherif@prof.edu`

#### **Students** (Password: `student123`)
`sommaya@student.edu`, `habiba@student.edu`, `rowaida@student.edu`,  
`ahmed@student.edu`, `rawan@student.edu`

---

## 🛠 Installation & Setup

### **1. Requirements**
- PHP 7.4+
- MySQL 5.7+  
- Apache server (XAMPP / WAMP / LAMP)
- Composer (optional for testing)

---

### **2. Database Setup**
1. Open phpMyAdmin  
2. Import `init.sql`  
3. Confirm tables are created (`users`, `courses`, `sections`, `enrollments`, `grades`, etc.)

---

### **3. To Run aplication**
1. install XAMPP (Windows)
2. Start Apache + MySQL, then open in browser : http://localhost/ums/index.php
3. open http://localhost/phpmyadmin to see live changes on data base
4. creat new data base named ums (only first time you open the project at your local machine )
5. import init.sql file (only first time you open the project at your local machine )
6. Login using any of the demo accounts listed above.

### **4. Configure Database**
Edit `config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ums');
define('DB_USER', 'root');
define('DB_PASS', '')



