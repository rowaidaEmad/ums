<?php
require_once 'auth.php';
require_role('admin');
require_once 'db.php';
require_once 'eav.php';

$pdo = getDB();
$errors = [];

// Load professors for dropdown (needed before the form)
$professors = $pdo->query("SELECT id, name FROM users WHERE role = 'professor' ORDER BY name")->fetchAll();

// Load existing courses (needed for prerequisites multi-select)
// We select only code and title here for the dropdown; we also retrieve full rows later for the table.
$courseOptions = $pdo->query("SELECT id, code, title FROM courses ORDER BY code")->fetchAll(PDO::FETCH_ASSOC);

// Handle create new course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $code = trim($_POST['code'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $professor_id = $_POST['professor_id'] !== '' ? (int)$_POST['professor_id'] : null;
    $is_core = isset($_POST['is_core']) ? 1 : 0;
    $must_level = trim($_POST['must_level'] ?? '');

    // Handle prerequisites: could be posted as array (multi-select) or single comma-separated string
    if (isset($_POST['prerequisites']) && is_array($_POST['prerequisites'])) {
        // from multi-select: values should be course codes
        $pr_list = array_map('trim', $_POST['prerequisites']);
    } else {
        // fallback for older clients: free-text input
        $pr_raw = trim($_POST['prerequisites'] ?? '');
        if ($pr_raw === '') {
            $pr_list = [];
        } else {
            // split on commas, semicolons or whitespace
            $pr_list = array_filter(array_map('trim', preg_split('/[,\s;]+/', $pr_raw)));
        }
    }

    if ($code === '' || $title === '') {
        $errors[] = "Course code and title are required.";
    }

    // Prevent listing itself as prerequisite (case-insensitive)
    foreach ($pr_list as $pr) {
        if (strcasecmp($pr, $code) === 0) {
            $errors[] = "A course cannot list itself as a prerequisite.";
            break;
        }
    }

    // Normalize prerequisites to a comma-separated string for storage (dedupe case-insensitively)
    $prerequisites = '';
    if (!empty($pr_list)) {
        $normalized = [];
        foreach ($pr_list as $p) {
            $p = trim($p);
            if ($p === '') continue;
            $normalized[strtoupper($p)] = $p; // dedupe case-insensitively
        }
        $prerequisites = implode(', ', array_values($normalized));
    }

    // Check for duplicate course code (pure EAV: enforced in code)
    if (empty($errors) && eav_course_code_exists($code)) {
        $errors[] = "A course with code {$code} already exists.";
    }

    if (empty($errors)) {
        $pdo->beginTransaction();
        try {
            $courseId = eav_create_entity('course');
            eav_set($courseId, 'course', 'code', $code);
            eav_set($courseId, 'course', 'title', $title);
            eav_set($courseId, 'course', 'description', $description);
            if ($professor_id !== null) eav_set($courseId, 'course', 'professor_id', $professor_id);
            eav_set($courseId, 'course', 'is_core', (int)$is_core);
            if ($prerequisites !== '') eav_set($courseId, 'course', 'prerequisites', $prerequisites);
            if ($must_level !== '') eav_set($courseId, 'course', 'must_level', $must_level);

            $pdo->commit();
        } catch (Throwable $t) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = "Error: " . $t->getMessage();
        }

        // Reload course options so the newly created course appears immediately in the prerequisites list
        $courseOptions = $pdo->query("SELECT id, code, title FROM courses ORDER BY code")->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Handle delete course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $pdo->beginTransaction();
        try {
            // Pure EAV: manual cascades
            eav_delete_sections_by_course($id);
            eav_delete_enrollments_by_course($id);
            eav_delete_entity($id);
            $pdo->commit();
        } catch (Throwable $t) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = "Error: " . $t->getMessage();
        }

        // reload course options after deletion
        $courseOptions = $pdo->query("SELECT id, code, title FROM courses ORDER BY code")->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Load existing courses (full rows) for the table view
$courses = $pdo->query(
    "SELECT c.*, u.name AS professor_name
     FROM courses c
     LEFT JOIN users u ON c.professor_id = u.id
     ORDER BY c.code"
)->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include 'header.php'; ?>

<h3>Manage Courses</h3>

<div class="row">
    <div class="col-md-5">
        <h5>Create New Course</h5>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
            </div>
        <?php endif; ?>

        <form method="post" class="card card-body mb-4">
            <input type="hidden" name="action" value="create">
            <div class="mb-3">
                <label class="form-label">Course Code</label>
                <input name="code" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Professor</label>
                <select name="professor_id" class="form-select">
                    <option value="">-- Unassigned --</option>
                    <?php foreach ($professors as $p): ?>
                        <option value="<?= (int)$p['id'] ?>">
                            <?= htmlspecialchars($p['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="is_core" class="form-check-input" id="coreCheck">
                <label class="form-check-label" for="coreCheck">Core Course</label>
            </div>

            <!-- Prerequisites multi-select dropdown populated from existing courses -->
            <div class="mb-3">
                <label class="form-label">Prerequisites</label>
                <select name="prerequisites[]" class="form-select" multiple size="6">
                    <?php foreach ($courseOptions as $option): ?>
                        <option value="<?= htmlspecialchars($option['code']) ?>">
                            <?= htmlspecialchars($option['code'] . ' — ' . $option['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">
                    Hold Ctrl (Cmd) to select multiple. (You cannot select the same code as the course you're creating.)
                </small>
            </div>

            <div class="mb-3">
                <label class="form-label">Required Level</label>
                <select name="must_level" class="form-select">
                    <option value="">-- None --</option>
                    <option value="SP1">SP1</option>
                    <option value="SP2">SP2</option>
                    <option value="SP3">SP3</option>
                    <option value="SP4">SP4</option>
                    <option value="SP5">SP5</option>
                </select>
            </div>
            <button class="btn btn-success">Create Course</button>
        </form>
    </div>

    <div class="col-md-7">
        <h5>Existing Courses</h5>
        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Title</th>
                    <th>Professor</th>
                    <th>Core</th>
                    <th>Prerequisites</th>
                    <th>Level</th>
                    <th style="width: 80px;"></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($courses as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['code'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($c['title'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($c['professor_name'] ?? '—') ?></td>
                    <td><?= ($c['is_core'] ?? 0) ? 'Yes' : 'No' ?></td>
                    <td><?= htmlspecialchars($c['prerequisites'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($c['must_level'] ?? '—') ?></td>
                    <td>
                        <form method="post" onsubmit="return confirm('Delete course?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                            <button class="btn btn-sm btn-danger">Del</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Back to Dashboard Button -->
<div class="mt-4">
    <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>
<?php include 'footer.php'; ?>
