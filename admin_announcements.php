
<?php
require_once 'auth.php';
require_role('admin');
require_once 'db.php';
require_once 'eav.php';

$pdo = getDB();
$errors = [];
$success = null;

/* Create announcement */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $title    = trim($_POST['title'] ?? '');
    $message  = trim($_POST['message'] ?? '');
    $audience = ($_POST['audience'] ?? 'all');  // 'students' | 'parents' | 'all'
    $createdBy = (int)$_SESSION['user']['id'];

    if ($title === '' || $message === '') {
        $errors[] = 'Title and message are required.';
    } elseif (!in_array($audience, ['students','parents','all'], true)) {
        $errors[] = 'Invalid audience.';
    }

    if (empty($errors)) {
        try {
            $id = eav_create_entity('announcement');        // create entity
            eav_set($id, 'announcement', 'title', $title);
            eav_set($id, 'announcement', 'message', $message);
            eav_set($id, 'announcement', 'audience', $audience);
            eav_set($id, 'announcement', 'created_by', $createdBy);
            $success = 'Announcement published.';
        } catch (Throwable $t) {
            $errors[] = 'Error: ' . $t->getMessage();
        }
    }
}

/* Delete announcement */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        try {
            eav_delete_entity($id);
            $success = 'Announcement removed.';
        } catch (Throwable $t) {
            $errors[] = 'Error: ' . $t->getMessage();
        }
    }
}

/* Load announcements (latest first) */
$rows = $pdo->query("
    SELECT a.*, u.name AS admin_name
    FROM announcements a
    LEFT JOIN users u ON u.id = a.created_by
    ORDER BY a.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

include 'header.php';
?>
<div class="container my-3">
  <h3>Announcements</h3>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
  <?php endif; ?>
  <?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <div class="card card-body mb-4">
    <h5 class="mb-3">Create Announcement</h5>
    <form method="post" class="row g-3">
      <input type="hidden" name="action" value="create">
      <div class="col-md-6">
        <label class="form-label">Title</label>
        <input name="title" class="form-control" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Audience</label>
        <select name="audience" class="form-select" required>
          <option value="all">All</option>
          <option value="students">Students</option>
          <option value="parents">Parents</option>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label">Message</label>
        <textarea name="message" class="form-control" rows="5" required></textarea>
      </div>
      <div class="col-12">
        <button class="btn btn-primary">Publish</button>
      </div>
    </form>
  </div>

  <h5 class="mb-2">Recent Announcements</h5>
  <table class="table table-bordered table-sm">
    <thead class="table-light">
      <tr>
        <th>Title</th>
        <th>Audience</th>
        <th>Message</th>
        <th>Published By</th>
        <th>Created</th>
        <th style="width:90px;"></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['title'] ?? '—') ?></td>
          <td><?= htmlspecialchars(($r['audience'] ?? 'all')) ?></td>
          <td style="white-space: normal;"><?= nl2br(htmlspecialchars($r['message'] ?? '—')) ?></td>
          <td><?= htmlspecialchars($r['admin_name'] ?? '—') ?></td>
          <td><?= htmlspecialchars($r['created_at'] ?? '') ?></td>
          <td>
            <form method="post" onsubmit="return confirm('Delete this announcement?');">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="btn btn-sm btn-danger">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($rows)): ?>
        <tr><td colspan="6" class="text-center">No announcements yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <a href="admin_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>
<?php include 'footer.php'; ?>