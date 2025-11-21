<?php
require_once 'auth.php';
require_role('student');
require_once 'db.php';

$pdo = getDB();
$userId = $_SESSION['user']['id'];

// Flash messages
if (!isset($_SESSION['flash'])) $_SESSION['flash'] = [];
$flash = &$_SESSION['flash'];

// ----------------------
// Handle POST actions
// ----------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $course_id = intval($_POST['course_id'] ?? 0);
    $section_id = intval($_POST['section_id'] ?? 0);

    // ----------------------
    // ENROLL
    // ----------------------
    if ($action === 'enroll' && $course_id && $section_id) {

        try {
            // Check if already enrolled in this course
            $q = $pdo->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
            $q->execute([$userId, $course_id]);

            if ($q->fetch()) {
                $flash['error'] = "You are already enrolled in this course.";
            } else {

                $pdo->beginTransaction();

                // Lock selected section for update
                $sec = $pdo->prepare("
                    SELECT id, capacity,
                        (SELECT COUNT(*) FROM enrollments WHERE section_id = ?) AS enrolled_count
                    FROM sections WHERE id = ? FOR UPDATE
                ");
                $sec->execute([$section_id, $section_id]);
                $section = $sec->fetch(PDO::FETCH_ASSOC);

                if (!$section) {
                    $pdo->rollBack();
                    $flash['error'] = "Section not found.";
                } else {

                    if ($section['enrolled_count'] >= $section['capacity']) {
                        $pdo->rollBack();
                        $flash['error'] = "This section is already full.";
                    } else {
                        $ins = $pdo->prepare("INSERT INTO enrollments (student_id, course_id, section_id) VALUES (?,?,?)");
                        $ins->execute([$userId, $course_id, $section_id]);

                        $pdo->commit();
                        $flash['success'] = "Enrollment successful!";
                    }
                }
            }

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $flash['error'] = "Error: " . $e->getMessage();
        }

        header("Location: student_register.php");
        exit;
    }

    // ----------------------
    // UNENROLL
    // ----------------------
    if ($action === 'unenroll' && $course_id) {

        try {
            $del = $pdo->prepare("DELETE FROM enrollments WHERE student_id = ? AND course_id = ?");
            $del->execute([$userId, $course_id]);
            $flash['success'] = "You have been unenrolled.";
        } catch (PDOException $e) {
            $flash['error'] = "Error: " . $e->getMessage();
        }

        header("Location: student_register.php");
        exit;
    }
}

// ----------------------
// Load data to display
// ----------------------

$courses = $pdo->query("SELECT id, code, title FROM courses ORDER BY code")
               ->fetchAll(PDO::FETCH_ASSOC);

$sections = $pdo->query("
    SELECT s.id AS section_id, s.course_id, s.section_number, s.capacity,
           (SELECT COUNT(*) FROM enrollments WHERE section_id = s.id) AS enrolled_count
    FROM sections s ORDER BY s.course_id, s.section_number
")->fetchAll(PDO::FETCH_ASSOC);

// Organize sections by course
$sectionsByCourse = [];
foreach ($sections as $s) $sectionsByCourse[$s['course_id']][] = $s;

// Get current enrollments: course_id => section_id
$enrolled = $pdo->prepare("SELECT course_id, section_id FROM enrollments WHERE student_id = ?");
$enrolled->execute([$userId]);
$enrolledCourses = $enrolled->fetchAll(PDO::FETCH_KEY_PAIR);

// Copy flash messages then clear
$messages = $flash;
$_SESSION['flash'] = [];

?>

<?php include 'header.php'; ?>

<h3>Course Registration</h3>

<!-- Messages -->
<?php if (!empty($messages['error'])): ?>
<div class="alert alert-danger"><?= htmlspecialchars($messages['error']) ?></div>
<?php endif; ?>

<?php if (!empty($messages['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($messages['success']) ?></div>
<?php endif; ?>

<table class="table table-bordered table-sm">
    <thead class="table-light">
        <tr>
            <th>Course</th>
            <th style="width:300px;">Section</th>
            <th>Seats</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>

    <?php foreach ($courses as $course): 
        $cid = $course['id'];
        $courseSections = $sectionsByCourse[$cid] ?? [];
        $isEnrolled = isset($enrolledCourses[$cid]);
        $enrolledSecId = $isEnrolled ? $enrolledCourses[$cid] : null;
    ?>
        <tr>

            <td><?= htmlspecialchars($course['code'] . " - " . $course['title']) ?></td>

            <td>
                <?php if ($isEnrolled): ?>
                    <?php
                        // Find section number
                        $secNum = "Unknown";
                        foreach ($courseSections as $s)
                            if ($s['section_id'] == $enrolledSecId) $secNum = "Section " . $s['section_number'];
                    ?>
                    <div class="form-control bg-light"><?= htmlspecialchars($secNum) ?></div>

                <?php elseif (empty($courseSections)): ?>
                    <span class="text-muted">No sections</span>

                <?php else: ?>
                    <form method="POST" class="d-flex" style="gap:8px">
                        <input type="hidden" name="action" value="enroll">
                        <input type="hidden" name="course_id" value="<?= $cid ?>">

                        <select name="section_id" class="form-select form-select-sm" required>
                            <option value="">Select section</option>
                            <?php foreach ($courseSections as $s): 
                                $left = $s['capacity'] - $s['enrolled_count'];
                            ?>
                                <option value="<?= $s['section_id'] ?>" <?= $left <= 0 ? "disabled" : "" ?>>
                                    Section <?= $s['section_number'] ?> (<?= $left ?> left)
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit" class="btn btn-sm btn-primary">Enroll</button>
                    </form>
                <?php endif; ?>
            </td>

            <td>
                <?php
                    if ($isEnrolled) {
                        foreach ($courseSections as $s)
                            if ($s['section_id'] == $enrolledSecId)
                                echo $s['enrolled_count'] . " / " . $s['capacity'];
                    } else {
                        if (!empty($courseSections)) {
                            $s = $courseSections[0];
                            echo $s['enrolled_count'] . " / " . $s['capacity'];
                        } else echo "—";
                    }
                ?>
            </td>

            <td>
                <?php if ($isEnrolled): ?>
                    <span class="badge bg-success">Enrolled</span>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="unenroll">
                        <input type="hidden" name="course_id" value="<?= $cid ?>">
                        <button class="btn btn-sm btn-danger ms-2">Unenroll</button>
                    </form>
                <?php else: ?>
                    <span class="badge bg-warning text-dark">Available</span>
                <?php endif; ?>
            </td>

        </tr>
    <?php endforeach; ?>

    </tbody>
</table>

<a href="student_dashboard.php" class="btn btn-secondary mt-3">Back</a>

<?php include 'footer.php'; ?>
