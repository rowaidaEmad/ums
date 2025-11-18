<?php
require_once 'auth.php';
require_role('admin');
require_once 'db.php';

$pdo = getDB();

// Handle create new course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $code = trim($_POST['code'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $professor_id = $_POST['professor_id'] !== '' ? (int)$_POST['professor_id'] : null;
    $is_core = isset($_POST['is_core']) ? 1 : 0;
    $prerequisites = trim($_POST['prerequisites'] ?? '');
    $must_level = trim($_POST['must_level'] ?? '');

    if ($code && $title) {
        $stmt = $pdo->prepare(
            'INSERT INTO courses (code, title, description, professor_id, is_core, prerequisites, must_level)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$code, $title, $description, $professor_id, $is_core, $prerequisites, $must_level]);
    }
}

// Handle delete course
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $stmt = $pdo->prepare('DELETE FROM courses WHERE id = ?');
        $stmt->execute([$id]);
    }
}

// Load professors for dropdown
$professors = $pdo->query("SELECT id, name FROM users WHERE role = 'professor' ORDER BY name")
                  ->fetchAll();

// Load existing courses
$courses = $pdo->query(
    "SELECT c.*, u.name AS professor_name
     FROM courses c
     LEFT JOIN users u ON c.professor_id = u.id
     ORDER BY c.code"
)->fetchAll();
?>

<?php include 'header.php'; ?>

<h3>Manage Courses</h3>

<div class="row">
    <div class="col-md-5">
        <h5>Create New Course</h5>
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
                        <option value="<?= $p['id'] ?>">
                            <?= htmlspecialchars($p['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="is_core" class="form-check-input" id="coreCheck">
                <label class="form-check-label" for="coreCheck">Core Course</label>
            </div>
            <div class="mb-3">
                <label class="form-label">Prerequisites</label>
                <input name="prerequisites" class="form-control" placeholder="e.g., MATH101, CS100">
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
                    <td><?= htmlspecialchars($c['code']) ?></td>
                    <td><?= htmlspecialchars($c['title']) ?></td>
                    <td><?= htmlspecialchars($c['professor_name'] ?? '—') ?></td>
                    <td><?= $c['is_core'] ? 'Yes' : 'No' ?></td>
                    <td><?= htmlspecialchars($c['prerequisites'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($c['must_level'] ?? '—') ?></td>
                    <td>
                        <form method="post" onsubmit="return confirm('Delete course?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                            <button class="btn btn-sm btn-danger">Del</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>