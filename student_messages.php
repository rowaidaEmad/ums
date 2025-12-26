<?php
require_once 'auth.php';
require_once 'eav.php';
require_once 'db.php';
require_role('student');

$pdo = getDB();
$student_id = (int)$_SESSION['user']['id'];

// Professors the student is associated with (via courses view)
$profStmt = $pdo->prepare("
    SELECT DISTINCT u.id AS professor_id, u.name AS professor_name
    FROM enrollments e
    JOIN courses c ON c.id = e.course_id
    JOIN users u ON u.id = c.professor_id
    WHERE e.student_id = ?
      AND u.role = 'professor'
    ORDER BY u.name
");
$profStmt->execute([$student_id]);
$professors = $profStmt->fetchAll();

$selected_prof = isset($_GET['professor_id']) ? (int)$_GET['professor_id'] : 0;
if ($selected_prof === 0 && count($professors) > 0) {
    $selected_prof = (int)$professors[0]['professor_id'];
}

// Send message
$flash = null;
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
    $selected_prof = (int)($_POST['professor_id'] ?? 0);
    $body = trim($_POST['body'] ?? '');

    if ($selected_prof <= 0) {
        $error = "Please choose a professor.";
    } elseif ($body === '') {
        $error = "Message cannot be empty.";
    } else {
        // Verify professor is in allowed list (linked via enrollment)
        $allowed = false;
        foreach ($professors as $p) {
            if ((int)$p['professor_id'] === $selected_prof) { $allowed = true; break; }
        }
        if (!$allowed) {
            $error = "You can only message professors of your enrolled courses.";
        } else {
            $mid = eav_create_entity('message');
            eav_set($mid, 'message', 'from_user_id', $student_id);
            eav_set($mid, 'message', 'to_user_id', $selected_prof);
            eav_set($mid, 'message', 'body', $body);
            $flash = "Message sent.";
        }
    }
}

// Load conversation
$messages = [];
if ($selected_prof > 0) {
    $msgStmt = $pdo->prepare("
        SELECT m.id, m.from_user_id, m.to_user_id, m.body, m.created_at,
               uf.name AS from_name, ut.name AS to_name
        FROM messages m
        JOIN users uf ON uf.id = m.from_user_id
        JOIN users ut ON ut.id = m.to_user_id
        WHERE (m.from_user_id = ? AND m.to_user_id = ?)
           OR (m.from_user_id = ? AND m.to_user_id = ?)
        ORDER BY m.created_at ASC, m.id ASC
    ");
    $msgStmt->execute([$student_id, $selected_prof, $selected_prof, $student_id]);
    $messages = $msgStmt->fetchAll();
}
?>
<?php include 'header.php'; ?>

<h3>Messages</h3>

<?php if ($flash): ?>
  <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>
<?php if ($error): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="row">
  <div class="col-md-4">
    <form method="get" class="card card-body mb-3">
      <label class="form-label">Professor</label>
      <select class="form-select" name="professor_id" onchange="this.form.submit()">
        <?php if (count($professors) === 0): ?>
          <option value="0">No professors available</option>
        <?php else: ?>
          <?php foreach ($professors as $p): ?>
            <option value="<?= (int)$p['professor_id'] ?>" <?= ((int)$p['professor_id']===$selected_prof?'selected':'') ?>>
              <?= htmlspecialchars($p['professor_name']) ?>
            </option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
    </form>

    <form method="post" class="card card-body">
      <input type="hidden" name="action" value="send">
      <input type="hidden" name="professor_id" value="<?= (int)$selected_prof ?>">
      <label class="form-label">New message</label>
      <textarea class="form-control" name="body" rows="4" placeholder="Type your message..."></textarea>
      <button class="btn btn-primary mt-2" type="submit" <?= ($selected_prof<=0?'disabled':'') ?>>Send</button>
      <small class="text-muted mt-2 d-block">Only professors of your enrolled courses can be messaged.</small>
    </form>
  </div>

  <div class="col-md-8">
    <div class="card card-body" style="max-height: 70vh; overflow:auto;">
      <?php if ($selected_prof<=0): ?>
        <p class="text-muted mb-0">No conversation selected.</p>
      <?php elseif (count($messages)===0): ?>
        <p class="text-muted mb-0">No messages yet.</p>
      <?php else: ?>
        <?php foreach ($messages as $m): ?>
          <?php $is_me = ((int)$m['from_user_id'] === $student_id); ?>
          <div class="mb-3">
            <div class="d-flex justify-content-between">
              <strong><?= htmlspecialchars($m['from_name']) ?><?= $is_me ? " (You)" : "" ?></strong>
              <small class="text-muted"><?= htmlspecialchars($m['created_at']) ?></small>
            </div>
            <div class="border rounded p-2 <?= $is_me ? 'bg-light' : '' ?>">
              <?= nl2br(htmlspecialchars($m['body'])) ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
