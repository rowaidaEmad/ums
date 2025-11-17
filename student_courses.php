<?php
require_once 'auth.php';
require_role('student');
require_once 'db.php';

$pdo = getDB();
$userId = $_SESSION['user']['id'];

// Handle enroll
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'enroll') {
    $course_id = (int)($_POST['course_id'] ?? 0);
    if ($course_id) {
        try {
            $stmt = $pdo->prepare(
                'INSERT IGNORE INTO enrollments (student_id, course_id) VALUES (?, ?)'
            );
            $stmt->execute([$userId, $course_id]);
        } catch (PDOException $e) {
            // ignore duplicate
        }
    }
}

// All courses with info if current student is enrolled
$courses = $pdo->prepare(
    "SELECT c.*, u.name AS professor_name,
            EXISTS(
                SELECT 1 FROM enrollments e WHERE e.course_id = c.id AND e.student_id = ?
            ) AS enrolled
     FROM courses c
     LEFT JOIN users u ON c.professor_id = u.id
     ORDER BY c.code"
);
$courses->execute([$userId]);
$courses = $courses->fetchAll();

// My courses + grades
$my = $pdo->prepare(
    "SELECT c.code, c.title, g.grade
     FROM enrollments e
     JOIN courses c ON e.course_id = c.id
     LEFT JOIN grades g ON g.enrollment_id = e.id
     WHERE e.student_id = ?
     ORDER BY c.code"
);
$my->execute([$userId]);
$myCourses = $my->fetchAll();
?>

<?php include 'header.php'; ?>

<h3>Course Registration</h3>

<h5>Available Courses</h5>
<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>Code</th>
            <th>Title</th>
            <th>Professor</th>
            <th style="width: 120px;"></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($courses as $c): ?>
        <tr>
            <td><?= htmlspecialchars($c['code']) ?></td>
            <td><?= htmlspecialchars($c['title']) ?></td>
            <td><?= htmlspecialchars($c['professor_name'] ?? '—') ?></td>
            <td>
                <?php if ($c['enrolled']): ?>
                    <span class="badge bg-success">Enrolled</span>
                <?php else: ?>
                    <form method="post">
                        <input type="hidden" name="action" value="enroll">
                        <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
                        <button class="btn btn-sm btn-primary">Enroll</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h5 class="mt-4">My Courses &amp; Grades</h5>
<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>Code</th>
            <th>Title</th>
            <th>Grade</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($myCourses as $m): ?>
        <tr>
            <td><?= htmlspecialchars($m['code']) ?></td>
            <td><?= htmlspecialchars($m['title']) ?></td>
            <td><?= htmlspecialchars($m['grade'] ?? 'Not graded') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>
