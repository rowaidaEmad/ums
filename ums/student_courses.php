<?php
require_once 'auth.php';
require_role('student');
require_once 'db.php';

$pdo = getDB();
$userId = $_SESSION['user']['id'];

// Load student's enrolled sections with grades
$enrolledStmt = $pdo->prepare(
    "SELECT e.section_id, s.section_number, c.code AS course_code, c.title AS course_title,
            u.name AS professor_name, g.grade
     FROM enrollments e
     JOIN sections s ON e.section_id = s.id
     JOIN courses c ON s.course_id = c.id
     LEFT JOIN users u ON s.professor_id = u.id
     LEFT JOIN grades g ON g.enrollment_id = e.id
     WHERE e.student_id = ?
     ORDER BY c.code, s.section_number"
);
$enrolledStmt->execute([$userId]);
$enrolledSections = $enrolledStmt->fetchAll();
?>

<?php include 'header.php'; ?>

<h3>My Courses & Grades</h3>

<?php if ($enrolledSections): ?>
    <table class="table table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>Course</th>
                <th>Section</th>
                <th>Professor</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($enrolledSections as $e): ?>
            <tr>
                <td><?= htmlspecialchars($e['course_code'] . ' - ' . $e['course_title']) ?></td>
                <td><?= htmlspecialchars($e['section_number']) ?></td>
                <td><?= htmlspecialchars($e['professor_name'] ?? '—') ?></td>
                <td>
                    <?php if ($e['grade'] === null): ?>
                        <span class="badge bg-warning text-dark">Not graded</span>
                    <?php else: ?>
                        <span class="badge bg-info"><?= htmlspecialchars($e['grade']) ?></span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>You are not enrolled in any sections.</p>
<?php endif; ?>

<div class="mt-4">
    <a href="student_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

<?php include 'footer.php'; ?>
