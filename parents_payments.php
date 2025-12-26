<?php
require_once 'auth.php';
require_role('parent');
require_once 'eav.php';
require_once 'db.php';

/* ================================
   Fee calculation functions
   ================================ */

/**
 * Get total registered credit hours for a student (EAV-safe)
 */

function get_student_total_credits(int $student_id): int
{
    $pdo = eav_db();

    $stmt = $pdo->prepare("
        SELECT SUM(ev_ch.value_int) AS total_credits
        FROM entities e
        -- enrollment.student_id
        JOIN eav_values ev_sid ON ev_sid.entity_id = e.id
        JOIN eav_attributes a_sid ON a_sid.id = ev_sid.attribute_id

        -- enrollment.course_id
        JOIN eav_values ev_cid ON ev_cid.entity_id = e.id
        JOIN eav_attributes a_cid ON a_cid.id = ev_cid.attribute_id

        -- course.credit_hours
        JOIN entities c ON c.id = ev_cid.value_int
        JOIN eav_values ev_ch ON ev_ch.entity_id = c.id
        JOIN eav_attributes a_ch ON a_ch.id = ev_ch.attribute_id

        WHERE e.entity_type = 'enrollment'
          AND a_sid.entity_type = 'enrollment'
          AND a_sid.name = 'student_id'
          AND ev_sid.value_int = ?

          AND a_cid.entity_type = 'enrollment'
          AND a_cid.name = 'course_id'

          AND a_ch.entity_type = 'course'
          AND a_ch.name = 'credit_hours'
    ");

    $stmt->execute([$student_id]);
    return (int)($stmt->fetchColumn() ?? 0);
}

/**
 * Calculate total fees for a student
 */
function calculate_student_fees(int $student_id): int
{
    $price = (int)(eav_get(1, 'user', 'credit_hour_price') ?? 0);
    $credits = get_student_total_credits($student_id);

    return $credits * $price;
}

/* ================================
   Parent & student selection
   ================================ */

$parent_id = (int)$_SESSION['user']['id'];
$students = eav_get_linked_students($parent_id);

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

/* ================================
   Page rendering
   ================================ */

include 'header.php';
?>

<h3>Payments</h3>

<?php if (!$selected_student): ?>
    <div class="alert alert-warning">No student selected.</div>
    <a href="parent_dashboard.php" class="btn btn-secondary">Back</a>
    <?php include 'footer.php'; exit; ?>
<?php endif; ?>

<?php
$total_credits = get_student_total_credits($selected_student_id);
$total_fees = calculate_student_fees($selected_student_id);
$price = (int)(eav_get(1, 'user', 'credit_hour_price') ?? 0);
?>

<div class="card p-3 mb-3" style="max-width:600px;">
    <h5 class="mb-3"><?= htmlspecialchars($selected_student['name']) ?></h5>

    <p><strong>Total Credits:</strong> <?= $total_credits ?></p>
    <p><strong>Price per Credit:</strong> <?= number_format($price) ?> EGP</p>
    <p><strong>Total Fees:</strong> <?= number_format($total_fees) ?> EGP</p>

</div>

<a href="parent_dashboard.php?student_id=<?= (int)$selected_student_id ?>"
   class="btn btn-secondary mt-2">
   Back
</a>

<?php include 'footer.php'; ?>
