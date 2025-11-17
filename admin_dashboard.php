<?php
require_once 'auth.php';
require_role('admin');
?>
<?php include 'header.php'; ?>

<h3>Admin Dashboard</h3>
<p>Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?>.</p>

<ul>
    <li><a href="admin_courses.php">Manage Courses</a></li>
</ul>

<?php include 'footer.php'; ?>
