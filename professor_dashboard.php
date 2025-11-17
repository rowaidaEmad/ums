<?php
require_once 'auth.php';
require_role('professor');
?>
<?php include 'header.php'; ?>

<h3>Professor Dashboard</h3>
<p>Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?>.</p>

<ul>
    <li><a href="professor_courses.php">View My Courses &amp; Enter Grades</a></li>
</ul>

<?php include 'footer.php'; ?>
