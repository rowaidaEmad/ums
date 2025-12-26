<?php
require_once 'auth.php';
require_role('admin');
require_once 'eav.php';

define('ADMIN_ID', 1);
$current_price = eav_get(ADMIN_ID, 'user', 'credit_hour_price');

$success = '';
$error = '';

/**
 * Calculate the total fees for a student using PURE EAV
 */
function calculate_student_fees(int $student_id): int {
    $pdo = eav_db();

    // Get admin credit hour price
    $price_per_credit = (int) eav_get(ADMIN_ID, 'user', 'credit_hour_price');
    if ($price_per_credit <= 0) {
        return 0;
    }

    $stmt = $pdo->prepare("
        SELECT SUM(ch.value_int) AS total_credits
        FROM entities e
        -- enrollment.student_id
        JOIN eav_values es ON es.entity_id = e.id
        JOIN eav_attributes asid ON asid.id = es.attribute_id
            AND asid.entity_type = 'enrollment'
            AND asid.name = 'student_id'

        -- enrollment.course_id
        JOIN eav_values ec ON ec.entity_id = e.id
        JOIN eav_attributes acid ON acid.id = ec.attribute_id
            AND acid.entity_type = 'enrollment'
            AND acid.name = 'course_id'

        -- course.credit_hours
        JOIN eav_values ch ON ch.entity_id = ec.value_int
        JOIN eav_attributes ach ON ach.id = ch.attribute_id
            AND ach.entity_type = 'course'
            AND ach.name = 'credit_hours'

        WHERE e.entity_type = 'enrollment'
          AND es.value_int = ?
    ");

    $stmt->execute([$student_id]);
    $total_credits = (int) ($stmt->fetchColumn() ?? 0);

    return $total_credits * $price_per_credit;
}

/**
 * Get total registered credit hours for a student (PURE EAV)
 */
function get_student_total_credits(int $student_id): int {
    $pdo = eav_db();

    $stmt = $pdo->prepare("
        SELECT SUM(ch.value_int) AS total_credits
        FROM entities e
        JOIN eav_values es ON es.entity_id = e.id
        JOIN eav_attributes asid ON asid.id = es.attribute_id
            AND asid.entity_type='enrollment' AND asid.name='student_id'
        JOIN eav_values ec ON ec.entity_id = e.id
        JOIN eav_attributes acid ON acid.id = ec.attribute_id
            AND acid.entity_type='enrollment' AND acid.name='course_id'
        JOIN eav_values ch ON ch.entity_id = ec.value_int
        JOIN eav_attributes ach ON ach.id = ch.attribute_id
            AND ach.entity_type='course' AND ach.name='credit_hours'
        WHERE e.entity_type='enrollment'
          AND es.value_int = ?
    ");

    $stmt->execute([$student_id]);
    return (int) ($stmt->fetchColumn() ?? 0);
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
        // Fetch all students via EAV helper
        $students = eav_list_users_by_role('student');

        foreach ($students as $student):
            $student_id = (int)$student['id'];

            // NEW clean logic
            $total_credits = get_student_total_credits($student_id);
            $total_fees    = calculate_student_fees($student_id);
        ?>
            <tr>
                <td><?= htmlspecialchars($student['name']) ?></td>
                <td><?= $total_credits ?></td>
                <td><?= number_format($total_fees ?? 0) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<a href="admin_dashboard.php" class="btn btn-secondary mt-3">Back</a>

<?php include 'footer.php'; ?>
