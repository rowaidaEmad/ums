<?php 
require_once 'auth.php';
require_role('admin');
require_once 'db.php';

$pdo = getDB();
$errors = [];

// Create a section
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $course_id = (int)($_POST['course_id'] ?? 0);
    $section_number = (int)($_POST['section_number'] ?? 0);
    // If the form posts a professor_id explicitly, treat it as an override.
    $posted_prof_id = isset($_POST['professor_id']) && $_POST['professor_id'] !== '' 
                      ? (int)$_POST['professor_id'] 
                      : null;
    // Limit capacity to 1-65
    $capacity = max(1, min(65, (int)($_POST['capacity'] ?? 40)));

    // Validation
    if (!$course_id || $section_number < 1 || $section_number > 4) {
        $errors[] = 'Invalid course or section number (must be 1-4).';
    } else {
        // Ensure no more than one row for same course & section_number
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM sections WHERE course_id = ? AND section_number = ?');
        $stmt->execute([$course_id, $section_number]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'That section already exists for this course.';
        }
    }

    // Determine professor_id for the section:
    $professor_id = null;
    if ($posted_prof_id) {
        $professor_id = $posted_prof_id;
    } else {
        // Fetch professor already assigned to the course (from courses.professor_id)
        $pc = $pdo->prepare('SELECT professor_id FROM courses WHERE id = ?');
        $pc->execute([$course_id]);
        $prow = $pc->fetch();
        if ($prow && !empty($prow['professor_id'])) {
            $professor_id = (int)$prow['professor_id'];
        } else {
            $professor_id = null; // course has no assigned professor yet
        }
    }

    if (empty($errors)) {
        $ins = $pdo->prepare('INSERT INTO sections (course_id, section_number, professor_id, capacity) VALUES (?, ?, ?, ?)');
        $ins->execute([$course_id, $section_number, $professor_id, $capacity]);

        // Redirect depending on button clicked
        if (isset($_POST['save_exit'])) {
            header('Location: admin_dashboard.php'); // <-- change to your exit page
            exit;
        } else {
            header('Location: admin_sections.php?course_id=' . $course_id);
            exit;
        }
    }
}

// Delete section
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $course_id = (int)($_POST['course_id'] ?? 0);
    if ($id) {
        $del = $pdo->prepare('DELETE FROM sections WHERE id = ?');
        $del->execute([$id]);
        header('Location: admin_sections.php?course_id=' . $course_id);
        exit;
    }
}

// Load courses for dropdown
$courses = $pdo->query('SELECT id, code, title FROM courses ORDER BY code')->fetchAll();

// Load professors for dropdown (kept for optional override)
$professors = $pdo->query("SELECT id, name FROM users WHERE role = 'professor' ORDER BY name")->fetchAll();

$selectedCourseId = (int)($_GET['course_id'] ?? $courses[0]['id'] ?? 0);

// Fetch the course's assigned professor to display (read-only)
$courseProfessor = null;
if ($selectedCourseId) {
    $pc = $pdo->prepare('SELECT u.id, u.name FROM courses c LEFT JOIN users u ON c.professor_id = u.id WHERE c.id = ?');
    $pc->execute([$selectedCourseId]);
    $courseProfessor = $pc->fetch();
}

// Load sections for selected course
$sections = [];
if ($selectedCourseId) {
    $stmt = $pdo->prepare('SELECT s.*, u.name AS professor_name FROM sections s LEFT JOIN users u ON s.professor_id = u.id WHERE s.course_id = ? ORDER BY s.section_number');
    $stmt->execute([$selectedCourseId]);
    $sections = $stmt->fetchAll();
}
?>

<?php include 'header.php'; ?>
<h3>Manage Sections</h3>

<div class="row">
    <div class="col-md-5">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
        <?php endif; ?>

        <form method="get" class="mb-3">
            <label class="form-label">Select Course</label>
            <select name="course_id" class="form-select" onchange="this.form.submit()">
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $c['id'] === $selectedCourseId ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['code'] . ' - ' . $c['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <form method="post" class="card card-body">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="course_id" value="<?= $selectedCourseId ?>">

            <div class="mb-3">
                <label class="form-label">Section number (1-4)</label>
                <select name="section_number" class="form-select" required>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Assigned Course Professor</label>
                <?php if ($courseProfessor && !empty($courseProfessor['id'])): ?>
                    <div class="form-control" style="background:#f8f9fa;">
                        <?= htmlspecialchars($courseProfessor['name']) ?>
                    </div>
                    <input type="hidden" name="professor_id" value="<?= (int)$courseProfessor['id'] ?>">
                <?php else: ?>
                    <div class="form-control text-muted" style="background:#fff6f0;">
                        No professor assigned to this course yet.
                    </div>
                    <input type="hidden" name="professor_id" value="">
                <?php endif; ?>
                <div class="form-text">
                    Professor for new sections is taken from the course assignment (Manage Courses). To change, go to Manage Courses.
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Capacity</label>
                <input name="capacity" type="number" min="1" max="65" value="40" class="form-control">
                <div class="form-text">Capacity must be between 1 and 65.</div>
            </div>

            <div class="d-flex justify-content-between mt-3">
                <button type="submit" class="btn btn-success">Create Section</button>
                <button type="submit" name="save_exit" value="1" class="btn btn-primary">Save & Exit</button>
            </div>
        </form>
    </div>

    <div class="col-md-7">
        <h5>Sections for selected course</h5>

        <table class="table table-sm table-bordered">
            <thead class="table-light">
                <tr><th>#</th><th>Professor</th><th>Capacity</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($sections as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['section_number']) ?></td>
                    <td><?= htmlspecialchars($s['professor_name'] ?? '—') ?></td>
                    <td><?= (int)$s['capacity'] ?></td>
                    <td>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Delete section?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $s['id'] ?>">
                            <input type="hidden" name="course_id" value="<?= $selectedCourseId ?>">
                            <button class="btn btn-sm btn-danger">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($sections)): ?>
                <tr><td colspan="4" class="text-center">No sections defined for this course.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Back to Dashboard Button -->
<div class="mt-4">
    <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

<?php include 'footer.php'; ?>
