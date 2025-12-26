<?php
require_once 'auth.php';
require_once 'eav.php';
require_once 'db.php';
require_role('professor');

$pdo = getDB();
$prof_id = (int)$_SESSION['user']['id'];

// Students associated with this professor (via courses -> enrollments)
$stuStmt = $pdo->prepare("
    SELECT DISTINCT u.id AS student_id, u.name AS student_name
    FROM courses c
    JOIN enrollments e ON e.course_id = c.id
    JOIN users u ON u.id = e.student_id
    WHERE c.professor_id = ?
      AND u.role = 'student'
    ORDER BY u.name
");
$stuStmt->execute([$prof_id]);
$students = $stuStmt->fetchAll();

$selected_student = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
if ($selected_student === 0 && count($students) > 0) {
    $selected_student = (int)$students[0]['student_id'];
}

$flash = null;
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
    $selected_student = (int)($_POST['student_id'] ?? 0);
    $body = trim($_POST['body'] ?? '');

    if ($selected_student <= 0) {
        $error = "Please choose a student.";
    } elseif ($body === '') {
        $error = "Message cannot be empty.";
    } else {
        // Verify student is in allowed list
        $allowed = false;
        foreach ($students as $s) {
            if ((int)$s['student_id'] === $selected_student) { $allowed = true; break; }
        }
        if (!$allowed) {
            $error = "You can only message students enrolled in your courses.";
        } else {
            $mid = eav_create_entity('message');
            eav_set($mid, 'message', 'from_user_id', $prof_id);
            eav_set($mid, 'message', 'to_user_id', $selected_student);
            eav_set($mid, 'message', 'body', $body);
            $flash = "Message sent.";
        }
    }
}

// Load conversation
$messages = [];
if ($selected_student > 0) {
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
    $msgStmt->execute([$prof_id, $selected_student, $selected_student, $prof_id]);
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
      <label class="form-label">Student</label>
      <select class="form-select" name="student_id" onchange="this.form.submit()">
        <?php if (count($students) === 0): ?>
          <option value="0">No students available</option>
        <?php else: ?>
          <?php foreach ($students as $s): ?>
            <option value="<?= (int)$s['student_id'] ?>" <?= ((int)$s['student_id']===$selected_student?'selected':'') ?>>
              <?= htmlspecialchars($s['student_name']) ?>
            </option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
    </form>

    <form method="post" class="card card-body">
      <input type="hidden" name="action" value="send">
      <input type="hidden" name="student_id" value="<?= (int)$selected_student ?>">
      <label class="form-label">Reply</label>
      <textarea class="form-control" name="body" rows="4" placeholder="Type your message..."></textarea>
      <button class="btn btn-primary mt-2" type="submit" <?= ($selected_student<=0?'disabled':'') ?>>Send</button>
      <small class="text-muted mt-2 d-block">Only students in your courses can be messaged.</small>
    </form>
  </div>

  <div class="col-md-8">
    <div class="card card-body" style="max-height: 70vh; overflow:auto;">
      <?php if ($selected_student<=0): ?>
        <p class="text-muted mb-0">No conversation selected.</p>
      <?php elseif (count($messages)===0): ?>
        <p class="text-muted mb-0">No messages yet.</p>
      <?php else: ?>
        <?php foreach ($messages as $m): ?>
          <?php $is_me = ((int)$m['from_user_id'] === $prof_id); ?>
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
