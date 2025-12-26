<?php
require_once 'auth.php';
require_role('admin');
?>
<?php include 'header.php'; ?>

<h3>Admin Dashboard</h3>
<p>Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?>.</p>
<div style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px;">
    <!-- Create Users Card -->
    <a href="admin_create_user.php" style="
        display: block;
        width: 200px;
        padding: 20px;
        text-align: center;
        text-decoration: none;
        color: #fff;
        background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    ">
        <div style="font-size: 40px; margin-bottom: 10px;">👤</div>
        <div style="font-size: 18px; font-weight: bold;">Create User</div>
    </a>

    <!-- Manage Courses Card -->
    <a href="admin_courses.php" style="
        display: block;
        width: 200px;
        padding: 20px;
        text-align: center;
        text-decoration: none;
        color: #fff;
        background: linear-gradient(135deg, #696cd6ff,  #696cd6ff);
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        transition: transform 0.2s, box-shadow 0.2s;
    " onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)';">
        <img src="icons/course.png" alt="Courses" style="width:50px; margin-bottom:10px;">
        <div style="font-weight:bold; font-size:16px;">Manage Courses</div>
    </a>

    <!-- Manage Sections Card -->
    <a href="admin_sections.php" style="
        display: block;
        width: 200px;
        padding: 20px;
        text-align: center;
        text-decoration: none;
        color: #fff;
        background: linear-gradient(135deg,  #696cd6ff,  #696cd6ff);
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        transition: transform 0.2s, box-shadow 0.2s;
    " onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)';">
        <img src="icons/section.jpg" alt="Sections" style="width:50px; margin-bottom:10px;">
        <div style="font-weight:bold; font-size:16px;">Manage Sections</div>
    </a>

    <!-- Schedule Rooms Card -->
    <a href="admin_room.php" style="
        display: block;
        width: 200px;
        padding: 20px;
        text-align: center;
        text-decoration: none;
        color: #fff;
        background: linear-gradient(135deg,  #696cd6ff,  #696cd6ff);
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        transition: transform 0.2s, box-shadow 0.2s;
    " onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)';">
        <img src="icons/room.png" alt="Rooms" style="width:50px; margin-bottom:10px;">
        <div style="font-weight:bold; font-size:16px;">Schedule Rooms</div>
    </a>

    <!-- Parent-Student Linking Card -->
    <a href="admin_parent_link.php" style="
        display: block;
        width: 200px;
        padding: 20px;
        text-align: center;
        text-decoration: none;
        color: #fff;
        background: linear-gradient(135deg,  #696cd6ff,  #696cd6ff);
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        transition: transform 0.2s, box-shadow 0.2s;
    " onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)';">
        <img src="icons/section.jpg" alt="Parent Linking" style="width:50px; margin-bottom:10px;">
        <div style="font-weight:bold; font-size:16px;">Parent Linking</div>
    </a>

    <!-- Parent Requests Card -->
    <a href="admin_requests.php" style="
        display: block;
        width: 200px;
        padding: 20px;
        text-align: center;
        text-decoration: none;
        color: #fff;
        background: linear-gradient(135deg,  #696cd6ff,  #696cd6ff);
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        transition: transform 0.2s, box-shadow 0.2s;
    " onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)';">
        <img src="icons/course.png" alt="Requests" style="width:50px; margin-bottom:10px;">
        <div style="font-weight:bold; font-size:16px;">Parent Requests</div>
    </a>
</div>


<?php include 'footer.php'; ?>
