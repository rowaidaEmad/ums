<?php
require_once 'auth.php';
require_role('parent');
require_once 'eav.php';

$parent_id = (int)$_SESSION['user']['id'];
$id = (int)($_GET['id'] ?? 0);
$r = $id ? eav_get_request($id) : null;

if (!$r || (int)$r['parent_id'] !== $parent_id) {
    http_response_code(404);
    echo "Not found";
    exit;
}
?>
<?php include 'header.php'; ?>
<h3>Request #<?= (int)$r['id'] ?></h3>

<div class="card">
  <div class="card-body">
    <p class="mb-1"><strong>Date:</strong> <?= htmlspecialchars($r['created_at']) ?></p>
    <p class="mb-1"><strong>Student ID:</strong> <?= (int)$r['student_id'] ?></p>
    <p class="mb-1"><strong>Type:</strong> <?= htmlspecialchars($r['request_type'] ?? '-') ?></p>
    <p class="mb-1"><strong>Status:</strong> <?= htmlspecialchars($r['status'] ?? '-') ?></p>
    <hr>
    <p><strong>Message</strong></p>
    <div class="border rounded p-2"><?= nl2br(htmlspecialchars($r['message'] ?? '')) ?></div>
    <hr>
    <p><strong>Admin Reply</strong></p>
    <div class="border rounded p-2"><?= nl2br(htmlspecialchars($r['reply_note'] ?? '')) ?></div>

    <a href="parent_requests.php" class="btn btn-secondary mt-3">Back</a>
  </div>
</div>

<?php include 'footer.php'; ?>
