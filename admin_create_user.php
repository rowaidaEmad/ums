<?php
require_once 'auth.php';
require_role('admin');
require_once 'eav.php';

$success = '';
$error = '';

$name = '';
$email = '';
$password = '';
$role = 'student';
$program = '';
$level = '';

$auto_email = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? 'student');
    $program = trim($_POST['program'] ?? '');
    $level = trim($_POST['level'] ?? '');
    $auto_email = isset($_POST['auto_email']) && $_POST['auto_email'] === '1';

    // Basic validation
    if ($role !== 'student' && $role !== 'parent') {
        $error = 'Invalid role selected.';
    } elseif ($name === '' || $password === '') {
        $error = 'Please fill in all required fields (name, password).';
    } elseif (!$auto_email && $email === '') {
        $error = 'Please enter an email address or enable auto-generate email.';
    } elseif (!$auto_email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($role === 'student' && ($program === '' || $level === '')) {
        $error = 'Please fill in all required student fields (program, level).';
    } elseif (!$auto_email && eav_user_email_exists($email)) {
        $error = 'This email is already in use. Please choose a unique email.';
    } else {
        try {
            $uid = eav_create_entity('user');

            // Auto-generate email if requested. We base it on the new entity id,
            // so it's naturally unique in almost all cases.
            if ($auto_email) {
                $domain = 'ums.edu';
                $base = strtolower($role) . '_' . $uid;
                $generated = $base . '@' . $domain;
                $tries = 0;
                while (eav_user_email_exists($generated) && $tries < 5) {
                    $generated = $base . '_' . bin2hex(random_bytes(2)) . '@' . $domain;
                    $tries++;
                }
                if (eav_user_email_exists($generated)) {
                    throw new Exception('Failed to generate a unique email. Please try again.');
                }
                $email = $generated;
            }

            eav_set($uid, 'user', 'name', $name);
            eav_set($uid, 'user', 'email', $email);
            // NOTE: Project uses plain-text password check in login (index.php)
            eav_set($uid, 'user', 'password', $password);
            eav_set($uid, 'user', 'role', $role);

            // Optional for parent; required for student
            if ($program !== '') {
                eav_set($uid, 'user', 'program', $program);
            }
            if ($level !== '') {
                eav_set($uid, 'user', 'level', $level);
            }

            $success = "User created successfully. New User ID: " . $uid . " | Email: " . $email;
            // reset form
            $name = $email = $password = $program = $level = '';
            $role = 'student';
            $auto_email = false;
        } catch (Exception $e) {
            $error = 'Failed to create user: ' . $e->getMessage();
        }
    }
}
?>
<?php include 'header.php'; ?>

<h3>Create New Parent / Student</h3>
<p class="text-muted">Admin can create a new parent or student. The system assigns the user ID automatically.</p>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" class="card p-3" style="max-width: 700px;">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Full Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Role <span class="text-danger">*</span></label>
            <select name="role" class="form-select" required>
                <option value="student" <?= $role==='student'?'selected':'' ?>>Student</option>
                <option value="parent" <?= $role==='parent'?'selected':'' ?>>Parent</option>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" id="email_input" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" <?= $auto_email ? 'disabled' : '' ?> <?= $auto_email ? '' : 'required' ?>>
            <div class="form-text">Must be unique. If "Auto-generate" is enabled, the system will create a unique email automatically.</div>
            <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" id="auto_email" name="auto_email" value="1" <?= $auto_email ? 'checked' : '' ?>>
                <label class="form-check-label" for="auto_email">
                    Auto-generate unique email (e.g., parent_123@ums.edu)
                </label>
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label">Password <span class="text-danger">*</span></label>
            <input type="text" name="password" class="form-control" value="<?= htmlspecialchars($password) ?>" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Program / Major <?= $role==='student' ? '<span class="text-danger">*</span>' : '' ?></label>
            <input type="text" name="program" class="form-control" value="<?= htmlspecialchars($program) ?>" placeholder="e.g., Computer Science">
        </div>

        <div class="col-md-6">
            <label class="form-label">Level / Year <?= $role==='student' ? '<span class="text-danger">*</span>' : '' ?></label>
            <input type="text" name="level" class="form-control" value="<?= htmlspecialchars($level) ?>" placeholder="e.g., 3">
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Create User</button>
        <a href="admin_dashboard.php" class="btn btn-secondary">Back</a>
    </div>
</form>

<script>
// Simple UX: disable/enable email field when auto-generate is toggled.
(function() {
  var cb = document.getElementById('auto_email');
  var email = document.getElementById('email_input');
  if (!cb || !email) return;
  function sync() {
    if (cb.checked) {
      email.disabled = true;
      email.removeAttribute('required');
      email.value = '';
    } else {
      email.disabled = false;
      email.setAttribute('required', 'required');
    }
  }
  cb.addEventListener('change', sync);
  sync();
})();
</script>

<?php include 'footer.php'; ?>
