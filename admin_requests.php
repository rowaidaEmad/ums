<?php
require_once 'auth.php';
require_role('admin');
require_once 'eav.php';
require_once 'db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    $reply = trim($_POST['reply_note'] ?? '');

    if ($id <= 0 || $status === '') {
        $error = 'Invalid update.';
    } else {
        eav_update_request($id, $status, $reply);
        $message = "Request #$id updated.";
    }
}

$filter = trim($_GET['status'] ?? '');
$all = eav_fetch_requests(null);
if ($filter !== '') {
    $all = array_values(array_filter($all, fn($r) => ($r['status'] ?? '') === $filter));
}

$statuses = ['OPEN','IN_PROGRESS','CLOSED'];
?>
<?php include 'header.php'; ?>

<h3>Parent Requests (Admin)</h3>

<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<form method="get" class="mb-3">
  <label class="form-label">Filter by Status</label>
  <select name="status" class="form-select" onchange="this.form.submit()">
    <option value="">All</option>
    <?php foreach ($statuses as $s): ?>
      <option value="<?= $s ?>" <?= ($filter===$s?'selected':'') ?>><?= $s ?></option>
    <?php endforeach; ?>
  </select>
</form>

<div class="table-responsive">
<table class="table table-sm table-striped align-middle">
  <thead>
    <tr>
      <th>ID</th>
      <th>Date</th>
      <th>Parent</th>
      <th>Student</th>
      <th>Type</th>
      <th>Status</th>
      <th style="width: 35%;">Reply Note</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
  <?php if (empty($all)): ?>
    <tr><td colspan="8" class="text-muted">No requests.</td></tr>
  <?php else: ?>
    <?php foreach ($all as $r): ?>
      <tr>
        <form method="post">
          <td><?= (int)$r['id'] ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"></td>
          <td><?= htmlspecialchars($r['created_at']) ?></td>
          <td><?= (int)$r['parent_id'] ?></td>
          <td><?= (int)$r['student_id'] ?></td>
          <td><?= htmlspecialchars($r['request_type'] ?? '-') ?></td>
          <td>
            <select name="status" class="form-select form-select-sm">
              <?php foreach ($statuses as $s): ?>
                <option value="<?= $s ?>" <?= (($r['status'] ?? '')===$s?'selected':'') ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td>
            <textarea name="reply_note" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($r['reply_note'] ?? '') ?></textarea>
          </td>
          <td><button class="btn btn-sm btn-primary">Save</button></td>
        </form>
      </tr>
    <?php endforeach; ?>
  <?php endif; ?>
  </tbody>
</table>
</div>

<a href="admin_dashboard.php" class="btn btn-secondary">Back</a>

<?php include 'footer.php'; ?>
