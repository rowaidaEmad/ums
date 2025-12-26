<?php
require_once 'auth.php';
require_role('admin');
require_once 'db.php';
require_once 'eav.php';

$pdo = getDB();
$message = '';
$error = '';

$parents = eav_list_users_by_role('parent');
$students = eav_list_users_by_role('student');

$selected_parent = (int)($_POST['parent_id'] ?? $_GET['parent_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_ids = $_POST['student_ids'] ?? [];
    if ($selected_parent <= 0) {
        $error = 'Please select a parent.';
    } elseif (empty($student_ids)) {
        $error = 'Please select at least one student.';
    } else {
        $linked = 0;
        foreach ($student_ids as $sid) {
            $sid = (int)$sid;
            if ($sid > 0) {
                $id = eav_link_parent_student($selected_parent, $sid);
                if ($id) $linked++;
            }
        }
        $message = $linked . ' link(s) created (duplicates ignored).';
    }
}

$linked_students = [];
if ($selected_parent > 0) {
    $linked_students = eav_get_linked_students($selected_parent);
    $linked_ids = array_map(fn($r) => (int)$r['id'], $linked_students);
} else {
    $linked_ids = [];
}
?>
<?php include 'header.php'; ?>

<h3>Parent–Student Linking</h3>
<p class="text-muted">Link a parent to one or more students. Parents will only see linked students in their dashboard.</p>

<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<form method="post" class="mb-4">
  <div class="mb-3">
    <label class="form-label">Select Parent</label>
    <select name="parent_id" class="form-select" required onchange="location.href='admin_parent_link.php?parent_id='+this.value">
      <option value="">-- Choose parent --</option>
      <?php foreach ($parents as $p): ?>
        <option value="<?= (int)$p['id'] ?>" <?= ((int)$p['id']===$selected_parent?'selected':'') ?>>
          <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['email']) ?>)
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="mb-3">
    <label class="form-label">Select Student(s)</label>
    <div class="form-text mb-2">Hold Ctrl (Windows) or Cmd (Mac) to select multiple.</div>
    <select name="student_ids[]" class="form-select" multiple size="8" required>
      <?php foreach ($students as $s): ?>
        <option value="<?= (int)$s['id'] ?>" <?= (in_array((int)$s['id'], $linked_ids) ? 'selected' : '') ?>>
          <?= htmlspecialchars($s['name']) ?> (ID: <?= (int)$s['id'] ?>)
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <button class="btn btn-primary">Save Links</button>
  <a href="admin_dashboard.php" class="btn btn-secondary">Back</a>
</form>

<?php if ($selected_parent > 0): ?>
  <h5>Currently linked students</h5>
  <?php if (empty($linked_students)): ?>
    <p class="text-muted">No students linked yet.</p>
  <?php else: ?>
    <ul class="list-group">
      <?php foreach ($linked_students as $s): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
          <span><?= htmlspecialchars($s['name']) ?> (ID: <?= (int)$s['id'] ?>)</span>
          <form method="post" style="margin:0">
            <input type="hidden" name="parent_id" value="<?= (int)$selected_parent ?>">
            <input type="hidden" name="student_ids[]" value="<?= (int)$s['id'] ?>">
            <button class="btn btn-sm btn-outline-danger" name="unlink" value="1" formaction="admin_parent_unlink.php">Unlink</button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
<?php endif; ?>

<?php include 'footer.php'; ?>
