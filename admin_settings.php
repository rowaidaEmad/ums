<?php
require_once 'auth.php';
require_role('admin');
require_once 'eav.php';

define('ADMIN_ID', 1);
$current_price = eav_get(ADMIN_ID, 'user', 'credit_hour_price');

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $price = (int)($_POST['credit_hour_price'] ?? 0);

    // Check range
    if ($price < 2000 || $price > 6000) {
        $error = "Price must be between 2000 and 6000 EGP.";
    } else {
        eav_set(ADMIN_ID, 'user', 'credit_hour_price', $price);
        $success = "Credit hour price updated successfully.";
        $current_price = $price;
    }
}

include 'header.php';
?>

<h3>System Settings</h3>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" class="card p-3" style="max-width:400px;">
    <label class="form-label">Price per Credit Hour</label>
    <input
        type="number"
        name="credit_hour_price"
        class="form-control"
        value="<?= htmlspecialchars($current_price ?? '') ?>"
        min="2000"
        max="6000"
        required
    >
    <small class="text-muted">Example: 3500 (EGP). Must be between 2000 and 6000.</small>

    <button class="btn btn-primary mt-3">Save</button>
</form>

<a href="admin_dashboard.php" class="btn btn-secondary mt-3">Back</a>

<?php include 'footer.php'; ?>
