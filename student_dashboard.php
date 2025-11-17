<?php
require_once 'auth.php';
require_role('student');
?>
<?php include 'header.php'; ?>

<h3>Student Dashboard</h3>
<p>Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?>.</p>

<ul>
    <li><a href="student_courses.php">Register for Courses / View My Courses &amp; Grades</a></li>
</ul>

<?php include 'footer.php'; ?>
