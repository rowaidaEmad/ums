<?php
require_once 'auth.php';
require_role('parent');
require_once 'eav.php';
require_once 'db.php';

$parent_id = (int)$_SESSION['user']['id'];
$students = eav_get_linked_students($parent_id);

if (empty($students)) {
    header('Location: parent_dashboard.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int)($_POST['student_id'] ?? 0);
    $type = trim($_POST['request_type'] ?? '');
    $msg = trim($_POST['message'] ?? '');

    if (!eav_is_student_linked_to_parent($parent_id, $student_id)) {
        $error = 'You can only create requests for your linked students.';
    } elseif ($type === '' || $msg === '') {
        $error = 'Please select request type and write a message.';
    } else {
        $rid = eav_create_parent_request($parent_id, $student_id, $type, $msg);
        $message = 'Request created (ID: ' . $rid . ').';
    }
}

$requests = eav_fetch_requests($parent_id);
$types = ['Meeting','Complaint','Academic Concern','Other'];
?>
<?php include 'header.php'; ?>

<h3>My Requests</h3>

<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="card mb-4">
  <div class="card-body">
    <h5 class="card-title">Create New Request</h5>
    <form method="post">
      <div class="row g-2">
        <div class="col-md-4">
          <label class="form-label">Student</label>
          <select name="student_id" class="form-select" required>
            <?php foreach ($students as $s): ?>
              <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['name']) ?> (ID: <?= (int)$s['id'] ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Request Type</label>
          <select name="request_type" class="form-select" required>
            <option value="">-- Choose --</option>
            <?php foreach ($types as $t): ?>
              <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-12">
          <label class="form-label">Message</label>
          <textarea name="message" class="form-control" rows="3" required></textarea>
        </div>
      </div>
      <button class="btn btn-primary mt-3">Submit</button>
      <a href="parent_dashboard.php" class="btn btn-secondary mt-3">Back</a>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <h5 class="card-title">Request List</h5>
    <div class="table-responsive">
      <table class="table table-sm table-striped align-middle">
        <thead>
          <tr>
            <th>ID</th>
            <th>Date</th>
            <th>Student</th>
            <th>Type</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($requests)): ?>
            <tr><td colspan="6" class="text-muted">No requests yet.</td></tr>
          <?php else: ?>
            <?php foreach ($requests as $r): ?>
              <tr>
                <td><?= (int)$r['id'] ?></td>
                <td><?= htmlspecialchars($r['created_at']) ?></td>
                <td><?= (int)$r['student_id'] ?></td>
                <td><?= htmlspecialchars($r['request_type'] ?? '-') ?></td>
                <td><?= htmlspecialchars($r['status'] ?? '-') ?></td>
                <td><a class="btn btn-sm btn-outline-primary" href="parent_request_view.php?id=<?= (int)$r['id'] ?>">View</a></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
