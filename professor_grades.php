<?php
require_once 'auth.php';
require_role('professor');
require_once 'db.php';
require_once 'eav.php';

$pdo = getDB();
$userId = $_SESSION['user']['id'];
$course_id = (int)($_GET['course_id'] ?? 0);

// Ensure this course belongs to current professor
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND professor_id = ?");
$stmt->execute([$course_id, $userId]);
$course = $stmt->fetch();

if (!$course) {
    http_response_code(404);
    echo "Course not found or you are not assigned to it.";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($_POST['grades'] ?? [] as $enrollment_id => $marks) {
            $enrollment_id = (int)$enrollment_id;

            // Ensure enrollment belongs to this course
            $check = $pdo->prepare("SELECT id FROM enrollments WHERE id = ? AND course_id = ?");
            $check->execute([$enrollment_id, $course_id]);

            if ($check->fetch()) {
                $midterm = (float)($marks['midterm'] ?? 0);
                $activities = (float)($marks['activities'] ?? 0);
                $final = (float)($marks['final'] ?? 0);

                // Validate ranges
                if ($midterm < 0 || $midterm > 20) {
                    throw new Exception("Midterm mark for enrollment ID $enrollment_id must be between 0 and 20.");
                }
                if ($activities < 0 || $activities > 40) {
                    throw new Exception("Activities mark for enrollment ID $enrollment_id must be between 0 and 40.");
                }
                if ($final < 0 || $final > 40) {
                    throw new Exception("Final mark for enrollment ID $enrollment_id must be between 0 and 40.");
                }

               $total = $midterm + $activities + $final; // max 100

                // Determine letter grade (with + / -)
                if ($total >= 95) $letterGrade = 'A+';
                elseif ($total >= 90) $letterGrade = 'A';
                elseif ($total >= 85) $letterGrade = 'A-';
                elseif ($total >= 80) $letterGrade = 'B+';
                elseif ($total >= 75) $letterGrade = 'B';
                elseif ($total >= 70) $letterGrade = 'B-';
                elseif ($total >= 65) $letterGrade = 'C+';
                elseif ($total >= 60) $letterGrade = 'C';
                elseif ($total >= 55) $letterGrade = 'C-';
                elseif ($total >= 50) $letterGrade = 'D';
                else $letterGrade = 'F';

                // Save all marks + total + letter grade
                eav_set($enrollment_id, 'enrollment', 'midterm', $midterm);
                eav_set($enrollment_id, 'enrollment', 'activities', $activities);
                eav_set($enrollment_id, 'enrollment', 'final', $final);
                eav_set($enrollment_id, 'enrollment', 'total', $total);
                eav_set($enrollment_id, 'enrollment', 'grade', $letterGrade); // replaces GPA

            }
        }
        $success = "Grades saved successfully!";
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}


// Load students
$students = $pdo->prepare(
    "SELECT e.id AS enrollment_id, u.name AS student_name, u.email
     FROM enrollments e
     JOIN users u ON e.student_id = u.id
     WHERE e.course_id = ?
     ORDER BY u.name"
);
$students->execute([$course_id]);
$students = $students->fetchAll();
?>

<?php include 'header.php'; ?>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php elseif (!empty($success)): ?>
<div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<h3>Grades for <?= htmlspecialchars($course['code'] . ' - ' . $course['title']) ?></h3>

<form method="post" id="gradesForm">
<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>Student</th>
            <th>Email</th>
            <th>Midterm</th>
            <th>Activities</th>
            <th>Final</th>
            <th>Total</th>
            <th>Grade</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($students as $s): ?>
        <tr data-enrollment="<?= $s['enrollment_id'] ?>">
            <td><?= htmlspecialchars($s['student_name']) ?></td>
            <td><?= htmlspecialchars($s['email']) ?></td>
            <?php
            $marks = ['midterm','activities','final','total','grade'];
            foreach ($marks as $m):
                $val = eav_get($s['enrollment_id'], 'enrollment', $m);
                $readonly = in_array($m, ['total','grade']) ? 'readonly' : '';
                $type = $m === 'grade' ? 'text' : 'number'; // <-- grade is text
            ?>
            <td>
                <?php
                $maxAttr = '';
                if ($m === 'midterm') $maxAttr = 'max="20"';
                if ($m === 'activities') $maxAttr = 'max="40"';
                if ($m === 'final') $maxAttr = 'max="40"';
                ?>
                <input type="<?= $type ?>" name="grades[<?= $s['enrollment_id'] ?>][<?= $m ?>]"
                    value="<?= htmlspecialchars($val ?? '') ?>"
                    class="form-control form-control-sm mark-input"
                    min="0" <?= $maxAttr ?>
                    data-field="<?= $m ?>"
                    <?= $readonly ?>>
            </td>
            <?php endforeach; ?>

        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<button type="button" class="btn" id="calculateBtn" 
        style="background-color: purple; color: white; border: none;">
    Calculate Total & GPA
</button>

<button type="submit" class="btn" 
        style="background-color: purple; color: white; border: none;">
    Save Grades
</button>

<a href="professor_courses.php" class="btn btn-secondary">Back to My Courses</a>
</form>

<script>
document.getElementById('calculateBtn').addEventListener('click', () => {
    let error = '';
    document.querySelectorAll('tr[data-enrollment]').forEach(row => {
        let midterm = parseFloat(row.querySelector('[data-field="midterm"]').value) || 0;
        let activities = parseFloat(row.querySelector('[data-field="activities"]').value) || 0;
        let finalExam = parseFloat(row.querySelector('[data-field="final"]').value) || 0;

        // Validate ranges
        if (midterm < 0 || midterm > 20) error += `Midterm mark must be 0-20 for ${row.querySelector('td').textContent}\n`;
        if (activities < 0 || activities > 40) error += `Activities mark must be 0-40 for ${row.querySelector('td').textContent}\n`;
        if (finalExam < 0 || finalExam > 40) error += `Final mark must be 0-40 for ${row.querySelector('td').textContent}\n`;

        // Clamp values
        midterm = Math.min(Math.max(midterm, 0), 20);
        activities = Math.min(Math.max(activities, 0), 40);
        finalExam = Math.min(Math.max(finalExam, 0), 40);

        row.querySelector('[data-field="midterm"]').value = midterm;
        row.querySelector('[data-field="activities"]').value = activities;
        row.querySelector('[data-field="final"]').value = finalExam;

        // Calculate total
        const total = midterm + activities + finalExam;
        row.querySelector('[data-field="total"]').value = total;

        // Calculate letter grade
        let letterGrade = '';
        if (total >= 95) letterGrade = 'A+';
        else if (total >= 90) letterGrade = 'A';
        else if (total >= 85) letterGrade = 'A-';
        else if (total >= 80) letterGrade = 'B+';
        else if (total >= 75) letterGrade = 'B';
        else if (total >= 70) letterGrade = 'B-';
        else if (total >= 65) letterGrade = 'C+';
        else if (total >= 60) letterGrade = 'C';
        else if (total >= 55) letterGrade = 'C-';
        else if (total >= 50) letterGrade = 'D';
        else letterGrade = 'F';

        row.querySelector('[data-field="grade"]').value = letterGrade;
    });

    if (error) alert(error);
});



</script>

<?php include 'footer.php'; ?>
