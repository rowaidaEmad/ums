<?php
require_once 'auth.php';
require_role('professor');
require_once 'db.php';

$pdo = getDB();
$userId = $_SESSION['user']['id'];

$stmt = $pdo->prepare(
    "SELECT * FROM courses WHERE professor_id = ? ORDER BY code"
);
$stmt->execute([$userId]);
$courses = $stmt->fetchAll();
?>

<?php include 'header.php'; ?>

<h3>My Courses</h3>

<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>Code</th>
            <th>Title</th>
            <th style="width: 140px;"></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($courses as $c): ?>
        <tr>
            <td><?= htmlspecialchars($c['code']) ?></td>
            <td><?= htmlspecialchars($c['title']) ?></td>
            <td>
                <a class="btn btn-sm btn-primary"
                   href="professor_grades.php?course_id=<?= $c['id'] ?>">
                    View Students / Grades
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>
