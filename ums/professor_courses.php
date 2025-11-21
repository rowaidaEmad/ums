<?php
require_once 'auth.php';
require_role('professor');
require_once 'db.php';

$pdo = getDB();
$userId = $_SESSION['user']['id'];

// Load courses assigned to this professor
$stmt = $pdo->prepare(
    "SELECT c.id, c.code, c.title, c.description, c.must_level, c.is_core, c.room
     FROM courses c
     WHERE c.professor_id = ?
     ORDER BY c.code"
);
$stmt->execute([$userId]);
$courses = $stmt->fetchAll();
?>

<?php include 'header.php'; ?>

<h3>My Assigned Courses</h3>

<div style="display:flex; flex-wrap:wrap; gap:20px;">
<?php foreach ($courses as $c): ?>
    <div style="border:1px solid #ccc; border-radius:10px; padding:20px; width:300px; box-shadow:0 4px 6px rgba(0,0,0,0.1); background:#fff;">
        <h5 style="margin-top:0;"><?= htmlspecialchars($c['code']) ?> - <?= htmlspecialchars($c['title']) ?></h5>
        <p><strong>Description:</strong> <?= htmlspecialchars($c['description'] ?? '—') ?></p>
        <p><strong>Level:</strong> <?= htmlspecialchars($c['must_level'] ?? '—') ?></p>
        <p><strong>Core:</strong> <?= ($c['is_core'] ?? 0) ? 'Yes' : 'No' ?></p>
        <p><strong>Room:</strong> <?= htmlspecialchars($c['room'] ?? 'Not assigned') ?></p>
        <a href="professor_grades.php?course_id=<?= $c['id'] ?>" 
           style="display:inline-block; padding:8px 12px; background:#007bff; color:#fff; border-radius:5px; text-decoration:none;">
            Enter Grades
        </a>
    </div>
<?php endforeach; ?>

<?php if (empty($courses)): ?>
    <p>No courses assigned yet.</p>
<?php endif; ?>
</div>

<div class="mt-4">
    <a href="professor_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

<?php include 'footer.php'; ?>
