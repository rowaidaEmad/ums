<?php
require_once 'auth.php';
require_role('professor');
require_once 'db.php';

$pdo = getDB();
$userId = $_SESSION['user']['id'];

// Load courses assigned to this professor
$stmt = $pdo->prepare(
    "SELECT c.id, c.code, c.title, c.description, c.must_level, c.is_core, c.room
     FROM courses c
     WHERE c.professor_id = ?
     ORDER BY c.code"
);
$stmt->execute([$userId]);
$courses = $stmt->fetchAll();
?>

<?php include 'header.php'; ?>

<h3>My Assigned Courses</h3>

<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>Code</th>
            <th>Title</th>
            <th>Description</th>
            <th>Level</th>
            <th>Core</th>
            <th>Room</th>
            <th style="width: 140px;"></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($courses as $c): ?>
        <tr>
            <td><?= htmlspecialchars($c['code']) ?></td>
            <td><?= htmlspecialchars($c['title']) ?></td>
            <td><?= htmlspecialchars($c['description'] ?? '—') ?></td>
            <td><?= htmlspecialchars($c['must_level'] ?? '—') ?></td>
            <td><?= ($c['is_core'] ?? 0) ? 'Yes' : 'No' ?></td>
            <td><?= htmlspecialchars($c['room'] ?? '—') ?></td>
            <td>
                <a class="btn btn-sm btn-primary"
                   href="professor_grades.php?course_id=<?= $c['id'] ?>">
                    View Students / Grades
                </a>
            </td>
        </tr>
    <?php endforeach; ?>

    <?php if (empty($courses)): ?>
        <tr>
            <td colspan="7" class="text-center">No courses assigned yet.</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>