<?php
require_once 'auth.php';
require_role('admin');
require_once 'db.php';

$pdo = getDB();
$errors = [];
$success = '';

// Handle room assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'assign') {
    $course_id = (int)($_POST['course_id'] ?? 0);
    $room = trim($_POST['room'] ?? '');

    if (!$course_id || !$room) {
        $errors[] = "Course and room are required.";
    } elseif (!is_numeric($room) || $room < 100 || $room > 900) {
        $errors[] = "Room number must be between 100 and 900.";
    } else {
        $stmt = $pdo->prepare('UPDATE courses SET room = ? WHERE id = ?');
        $stmt->execute([$room, $course_id]);
        $success = "Room assigned successfully.";
    }
}

// Load courses
$courses = $pdo->query('SELECT id, code, title, room FROM courses ORDER BY code')->fetchAll();
?>

<?php include 'header.php'; ?>

<h3>Room Scheduling</h3>

<div class="row">
    <div class="col-md-5">
        <h5>Assign Room to Course</h5>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" class="card card-body">
            <input type="hidden" name="action" value="assign">

            <div class="mb-3">
                <label class="form-label">Select Course</label>
                <select name="course_id" class="form-select" required>
                    <option value="">-- Select Course --</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>">
                            <?= htmlspecialchars($c['code'] . ' - ' . $c['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Room Number</label>
                <input type="number" name="room" class="form-control" placeholder="e.g., 101" required min="100" max="900">
                <div class="form-text">Enter a room number between 100 and 900.</div>
            </div>

            <button class="btn btn-primary">Assign Room</button>
        </form>
    </div>

    <div class="col-md-7">
        <h5>Current Room Assignments</h5>
        <table class="table table-bordered table-sm">
            <thead class="table-light">
                <tr>
                    <th>Course Code</th>
                    <th>Title</th>
                    <th>Room</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['code']) ?></td>
                        <td><?= htmlspecialchars($c['title']) ?></td>
                        <td><?= htmlspecialchars($c['room'] ?? '—') ?></td>
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
