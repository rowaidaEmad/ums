<?php
require_once 'auth.php';
require_role('professor');
require_once 'db.php';
require_once 'eav.php';

$pdo = getDB();
$userId = $_SESSION['user']['id'];
$course_id = (int)($_GET['course_id'] ?? 0);

// Ensure this course belongs to current professor
$stmt = $pdo->prepare(
    "SELECT * FROM courses WHERE id = ? AND professor_id = ?"
);
$stmt->execute([$course_id, $userId]);
$course = $stmt->fetch();

if (!$course) {
    http_response_code(404);
    echo "Course not found or you are not assigned to it.";
    exit;
}

// Save grades
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($_POST['grades'] ?? [] as $enrollment_id => $grade) {
            $enrollment_id = (int)$enrollment_id;
            $grade = trim($grade);

        // Only update if enrollment belongs to this course
        $check = $pdo->prepare(
            "SELECT id FROM enrollments WHERE id = ? AND course_id = ?"
        );
        $check->execute([$enrollment_id, $course_id]);

            if ($check->fetch()) {
                // Save grade as an EAV attribute on the enrollment entity
                eav_set($enrollment_id, 'enrollment', 'grade', $grade);
            }
        }
    } catch (Throwable $e) {
        // Keep behavior non-fatal: show error as plain text (similar to other pages)
        $error = $e->getMessage();
    }
}

// Load students enrolled in this course + grades
$students = $pdo->prepare(
    "SELECT e.id AS enrollment_id,
            u.name AS student_name,
            u.email,
            g.grade
     FROM enrollments e
     JOIN users u ON e.student_id = u.id
     LEFT JOIN grades g ON g.enrollment_id = e.id
     WHERE e.course_id = ?
     ORDER BY u.name"
);
$students->execute([$course_id]);
$students = $students->fetchAll();
?>

<?php include 'header.php'; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<h3>Grades for <?= htmlspecialchars($course['code'] . ' - ' . $course['title']) ?></h3>

<form method="post">
    <table class="table table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>Student</th>
                <th>Email</th>
                <th style="width: 120px;">Grade</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($students as $s): ?>
            <tr>
                <td><?= htmlspecialchars($s['student_name']) ?></td>
                <td><?= htmlspecialchars($s['email']) ?></td>
                <td>
                    <input type="text"
                           name="grades[<?= $s['enrollment_id'] ?>]"
                           value="<?= htmlspecialchars($s['grade'] ?? '') ?>"
                           class="form-control form-control-sm"
                           placeholder="e.g. A, B+, 90">
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <button class="btn btn-primary">Save Grades</button>
    <a href="professor_courses.php" class="btn btn-secondary">Back to My Courses</a>
</form>

<?php include 'footer.php'; ?>
