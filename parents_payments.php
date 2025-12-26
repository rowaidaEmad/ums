<?php
require_once 'auth.php';
require_role('parent');
require_once 'eav.php';
require_once 'db.php';

$parent_id = (int)$_SESSION['user']['id'];
$students = eav_get_linked_students($parent_id);

// Determine selected student
$selected_student_id = (int)($_GET['student_id'] ?? 0);
if ($selected_student_id === 0 && !empty($students)) {
    $selected_student_id = (int)$students[0]['id'];
}

$selected_student = null;
foreach ($students as $s) {
    if ((int)$s['id'] === $selected_student_id) {
        $selected_student = $s;
        break;
    }
}

$pdo = getDB();
$credit_hour_price = eav_get(ADMIN_ID, 'user', 'credit_hour_price');

function calculate_student_fees(int $student_id, int $credit_hour_price, PDO $pdo): array {
    // Total credits
    $stmt = $pdo->prepare("
        SELECT SUM(c.credit_hours) as total_credits
        FROM enrollments e
        JOIN courses c ON c.id = e.course_id
        WHERE e.student_id = ?
    ");
    $stmt->execute([$student_id]);
    $total_credits = (int)($stmt->fetchColumn() ?? 0);
    $total_fees = $total_credits * $credit_hour_price;

    // Total paid
    $stmt_paid = $pdo->prepare("
        SELECT SUM(amount) as paid
        FROM payments
        WHERE student_id = ? AND parent_id = ?
    ");
    $stmt_paid->execute([$student_id, $_SESSION['user']['id']]);
    $paid = (int)($stmt_paid->fetchColumn() ?? 0);

    return [
        'credits' => $total_credits,
        'total_fees' => $total_fees,
        'paid' => $paid,
        'due' => max(0, $total_fees - $paid)
    ];
}

// Handle payment submission
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (int)($_POST['amount'] ?? 0);
    if ($amount <= 0) {
        $error = "Payment must be greater than 0.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO payments (student_id, parent_id, amount, payment_date) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$selected_student_id, $parent_id, $amount]);
        $success = "Payment recorded successfully.";
    }
}

include 'header.php';
?>

<h3>Payments for <?= htmlspecialchars($selected_student['name'] ?? 'Student') ?></h3>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($selected_student): 
    $fees = calculate_student_fees($selected_student_id, $credit_hour_price, $pdo);
?>
<div class="card p-3 mb-3" style="max-width:600px;">
    <p><strong>Total Credits:</strong> <?= $fees['credits'] ?></p>
    <p><strong>Price per Credit:</strong> <?= number_format($credit_hour_price) ?> EGP</p>
    <p><strong>Total Fees:</strong> <?= number_format($fees['total_fees']) ?> EGP</p>
    <p><strong>Paid:</strong> <?= number_format($fees['paid']) ?> EGP</p>
    <p><strong>Due:</strong> <?= number_format($fees['due']) ?> EGP</p>

    <?php if ($fees['due'] > 0): ?>
        <form method="post" class="d-flex gap-2 mt-2">
            <input type="number" name="amount" class="form-control form-control-sm" min="1" max="<?= $fees['due'] ?>" placeholder="Amount" required>
            <button class="btn btn-success btn-sm">Pay</button>
        </form>
    <?php else: ?>
        <div class="text-success mt-2">All fees are paid.</div>
    <?php endif; ?>
</div>
<?php else: ?>
    <div class="alert alert-warning">No student selected.</div>
<?php endif; ?>

<a href="parent_dashboard.php" class="btn btn-secondary mt-2">Back</a>

<?php include 'footer.php'; ?>
