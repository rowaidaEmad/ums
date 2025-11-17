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

    if ($code && $title) {
        $stmt = $pdo->prepare(
            'INSERT INTO courses (code, title, description, professor_id) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$code, $title, $description, $professor_id]);
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
                    <th style="width: 80px;"></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($courses as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['code']) ?></td>
                    <td><?= htmlspecialchars($c['title']) ?></td>
                    <td><?= htmlspecialchars($c['professor_name'] ?? '—') ?></td>
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
