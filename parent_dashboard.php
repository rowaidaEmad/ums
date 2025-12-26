<?php
require_once 'auth.php';
require_role('parent');
require_once 'eav.php';
require_once 'db.php';

$parent_id = (int)$_SESSION['user']['id'];
$students = eav_get_linked_students($parent_id);

$selected_student_id = (int)($_GET['student_id'] ?? 0);
if ($selected_student_id === 0 && !empty($students)) {
    $selected_student_id = (int)$students[0]['id'];
}

$selected_student = null;
foreach ($students as $s) {
    if ((int)$s['id'] === $selected_student_id) { $selected_student = $s; break; }
}

$avg = null;
if ($selected_student) {
    $avg = eav_student_current_average((int)$selected_student['id']);
}

// Grades snapshot: reuse enrollments view
$grades = [];
if ($selected_student) {
    $pdo = getDB();
    $stmt = $pdo->prepare("
		SELECT c.title AS course_title, g.grade
		FROM enrollments e
		JOIN courses c ON c.id = e.course_id
		LEFT JOIN grades g ON g.enrollment_id = e.id
        WHERE e.student_id = ?
        ORDER BY c.title
    ");
    $stmt->execute([(int)$selected_student['id']]);
    $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<?php include 'header.php'; ?>

<h3>Parent Dashboard</h3>
<p>Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?>.</p>

<?php if (empty($students)): ?>
  <div class="alert alert-warning">
    No students are linked to your parent account yet. Please contact the Admin.
  </div>
  <?php include 'footer.php'; exit; ?>
<?php endif; ?>

<form method="get" class="mb-3">
  <label class="form-label">Choose Student</label>
  <select name="student_id" class="form-select" onchange="this.form.submit()">
    <?php foreach ($students as $s): ?>
      <option value="<?= (int)$s['id'] ?>" <?= ((int)$s['id']===$selected_student_id?'selected':'') ?>>
        <?= htmlspecialchars($s['name']) ?> (ID: <?= (int)$s['id'] ?>)
      </option>
    <?php endforeach; ?>
  </select>
</form>

<div class="row g-3">
  <div class="col-md-4">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Student Summary</h5>
        <?php if ($selected_student): ?>
          <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($selected_student['name']) ?></p>
          <p class="mb-1"><strong>Student ID:</strong> <?= (int)$selected_student['id'] ?></p>
          <p class="mb-1"><strong>Major/Program:</strong> <?= htmlspecialchars($selected_student['program'] ?? '-') ?></p>
          <p class="mb-1"><strong>Level/Year:</strong> <?= htmlspecialchars($selected_student['level'] ?? '-') ?></p>
          <p class="mb-0"><strong>Current Average:</strong>
            <?= ($avg===null ? '-' : number_format($avg, 2) . '%') ?>
          </p>
        <?php endif; ?>
      </div>
    </div>

    <div class="card mt-3">
      <div class="card-body">
        <h5 class="card-title">Announcements</h5>
    
        <a href="parent_announcements.php" class="btn btn-sm btn-outline-primary mt-2">Open</a>
      </div>
    </div>

    <div class="card mt-3">
      <div class="card-body">
        <h5 class="card-title">Requests</h5>
        <p class="text-muted mb-0">Send a formal request to admin/advisor and track its status.</p>
        <a href="parent_requests.php" class="btn btn-sm btn-outline-primary mt-2">Open</a>
      </div>
    </div>
  </div>

  <div class="col-md-8">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Grades Snapshot</h5>
        <div class="table-responsive">
          <table class="table table-sm table-striped align-middle">
            <thead>
              <tr>
                <th>Course</th>
                <th>Midterm</th>
                <th>Final</th>
                <th>Total / Current</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($grades)): ?>
                <tr><td colspan="4" class="text-muted">No grades yet.</td></tr>
              <?php else: ?>
                <?php foreach ($grades as $g): ?>
                  <tr>
                    <td><?= htmlspecialchars($g['course_title']) ?></td>
                    <td>-</td>
                    <td>-</td>
                    <td><?= htmlspecialchars($g['grade'] ?? '-') ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
