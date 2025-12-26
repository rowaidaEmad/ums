<?php
require_once 'auth.php';
require_role('admin');
require_once 'eav.php';

define('ADMIN_ID', 1);
$current_price = eav_get(ADMIN_ID, 'user', 'credit_hour_price');

$success = '';
$error = '';

/**
 * Calculate the total fees for a student using EAV
 */
function calculate_student_fees(int $student_id): ?int {
    $pdo = eav_db();

    // Fetch admin's credit hour price
    $price_per_credit = eav_get(ADMIN_ID, 'user', 'credit_hour_price');
    if ($price_per_credit === null) return null;

    // Fetch total credits from enrollments using EAV
    $stmt = $pdo->prepare("
        SELECT SUM(c.value_int) AS total_credits
        FROM entities e
        JOIN eav_values ev_enr ON ev_enr.entity_id = e.id
        JOIN eav_attributes a_enr ON a_enr.id = ev_enr.attribute_id
        JOIN entities c_ent ON c_ent.id = ev_enr.value_int
        JOIN eav_values c ON c.entity_id = c_ent.id
        JOIN eav_attributes a ON a.id = c.attribute_id
        WHERE e.entity_type = 'enrollment'
          AND a_enr.entity_type='enrollment' AND a_enr.name='student_id' AND ev_enr.value_int = ?
          AND a.entity_type='course' AND a.name='credit_hours'
    ");
    $stmt->execute([$student_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_credits = (int)($row['total_credits'] ?? 0);

    return $total_credits * (int)$price_per_credit;
}

// Handle price update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $price = (int)($_POST['credit_hour_price'] ?? 0);

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

<h4>Student Fees</h4>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Student Name</th>
            <th>Total Credits</th>
            <th>Total Fees (EGP)</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $students = eav_list_users_by_role('student'); // fetch all students

        foreach ($students as $student):
            $total_fees = calculate_student_fees((int)$student['id']);

            // Optionally compute total credits separately
            $pdo = eav_db();
            $stmt_credits = $pdo->prepare("
                SELECT SUM(c.value_int) AS total_credits
                FROM entities e
                JOIN eav_values ev_enr ON ev_enr.entity_id = e.id
                JOIN eav_attributes a_enr ON a_enr.id = ev_enr.attribute_id
                JOIN entities c_ent ON c_ent.id = ev_enr.value_int
                JOIN eav_values c ON c.entity_id = c_ent.id
                JOIN eav_attributes a ON a.id = c.attribute_id
                WHERE e.entity_type = 'enrollment'
                  AND a_enr.entity_type='enrollment' AND a_enr.name='student_id' AND ev_enr.value_int = ?
                  AND a.entity_type='course' AND a.name='credit_hours'
            ");
            $stmt_credits->execute([$student['id']]);
            $credits_row = $stmt_credits->fetch(PDO::FETCH_ASSOC);
            $total_credits = (int)($credits_row['total_credits'] ?? 0);
        ?>
            <tr>
                <td><?= htmlspecialchars($student['name']) ?></td>
                <td><?= htmlspecialchars($total_credits) ?></td>
                <td><?= htmlspecialchars($total_fees ?? 0) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href="admin_dashboard.php" class="btn btn-secondary mt-3">Back</a>

<?php include 'footer.php'; ?>
