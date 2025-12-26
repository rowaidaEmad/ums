<?php
require_once 'auth.php';
require_role('student');
require_once 'db.php';
require_once 'eav.php';

$pdo = getDB();
$userId = $_SESSION['user']['id'];

// Load student's enrollments with course info + marks
$enrollmentsStmt = $pdo->prepare(
    "SELECT e.id AS enrollment_id, c.id AS course_id, c.code, c.title, c.description, c.credit_hours,
            g.grade,
            eav_midterm.value_int AS midterm,
            eav_activities.value_int AS activities,
            eav_final.value_int AS final,
            eav_total.value_int AS total,
            u.name AS professor_name
     FROM enrollments e
     JOIN courses c ON e.course_id = c.id
     LEFT JOIN eav_values AS eav_midterm ON eav_midterm.entity_id = e.id
       AND eav_midterm.attribute_id = (SELECT id FROM eav_attributes WHERE entity_type='enrollment' AND name='midterm')
     LEFT JOIN eav_values AS eav_activities ON eav_activities.entity_id = e.id
       AND eav_activities.attribute_id = (SELECT id FROM eav_attributes WHERE entity_type='enrollment' AND name='activities')
     LEFT JOIN eav_values AS eav_final ON eav_final.entity_id = e.id
       AND eav_final.attribute_id = (SELECT id FROM eav_attributes WHERE entity_type='enrollment' AND name='final')
     LEFT JOIN eav_values AS eav_total ON eav_total.entity_id = e.id
       AND eav_total.attribute_id = (SELECT id FROM eav_attributes WHERE entity_type='enrollment' AND name='total')
     LEFT JOIN grades g ON g.enrollment_id = e.id
     LEFT JOIN users u ON c.professor_id = u.id
     WHERE e.student_id = ?
     ORDER BY c.code"
);
$enrollmentsStmt->execute([$userId]);
$enrollments = $enrollmentsStmt->fetchAll(PDO::FETCH_ASSOC);

// Upcoming scheduled slots (next 14 days)
$today = (new DateTimeImmutable('today'))->format('Y-m-d');
$twoWeeks = (new DateTimeImmutable('today'))->modify('+14 days')->format('Y-m-d');

$slotsStmt = $pdo->prepare("
    SELECT s.course_id, s.slot_date, s.start_time, s.end_time, s.room_number
    FROM room_schedule s
    WHERE s.slot_date BETWEEN ? AND ?
    ORDER BY s.slot_date, s.start_time
");
try {
    $slotsStmt->execute([$today, $twoWeeks]);
    $slotsByCourse = [];
    foreach ($slotsStmt->fetchAll(PDO::FETCH_ASSOC) as $s) {
        $slotsByCourse[$s['course_id']][] = $s;
    }
} catch (Throwable $t) {
    $slotsByCourse = [];
}

include 'header.php';
?>

<div class="container my-3">
  <h3>My Courses</h3>

  <?php if (!empty($enrollments)): ?>
  <div class="row g-3">
      <?php foreach ($enrollments as $course): ?>
      <div class="col-md-4">
        <button class="btn btn-primary w-100 h-100 d-flex flex-column justify-content-center align-items-center"
                data-bs-toggle="modal"
                data-bs-target="#courseModal"
                data-course='<?= json_encode($course, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>'
                data-slots='<?= json_encode($slotsByCourse[$course["course_id"]] ?? [], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>'>
          <strong><?= htmlspecialchars($course['code']) ?></strong>
          <span><?= htmlspecialchars($course['title']) ?></span>
        </button>
      </div>
      <?php endforeach; ?>
  </div>
  <?php else: ?>
      <p>You are not enrolled in any courses yet.</p>
  <?php endif; ?>

  <div class="mt-4">
      <a href="student_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
  </div>
</div>

<!-- Course Details Modal -->
<div class="modal fade" id="courseModal" tabindex="-1" aria-labelledby="courseModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="courseModalLabel">Course Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <h5 id="cd-title">—</h5>
        <div class="text-muted mb-2" id="cd-code">—</div>
        <p><strong>Professor:</strong> <span id="cd-prof">—</span></p>
        <p><strong>Credit Hours:</strong> <span id="cd-credits">—</span></p>

        <table class="table table-bordered table-sm mt-2">
            <thead class="table-light">
                <tr>
                    <th>Midterm</th>
                    <th>Activities</th>
                    <th>Final</th>
                    <th>Total</th>
                    <th>Grade</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td id="cd-midterm">-</td>
                    <td id="cd-activities">-</td>
                    <td id="cd-final">-</td>
                    <td id="cd-total">-</td>
                    <td id="cd-grade">-</td>
                </tr>
            </tbody>
        </table>

        <h6>Description</h6>
        <div class="border rounded p-2 mb-3" id="cd-desc" style="min-height: 60px;">—</div>

        <h6>Upcoming Scheduled Slots (next 14 days)</h6>
        <div id="cd-slots" class="table-responsive">
          <div class="text-muted">No upcoming slots.</div>
        </div>

      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>

<script>
document.getElementById('courseModal').addEventListener('show.bs.modal', function(event){
    const button = event.relatedTarget;
    const course = JSON.parse(button.getAttribute('data-course') || '{}');
    const slots = JSON.parse(button.getAttribute('data-slots') || '[]');

    document.getElementById('cd-title').textContent = course.title || '-';
    document.getElementById('cd-code').textContent  = course.code ? ('Code: ' + course.code) : '-';
    document.getElementById('cd-prof').textContent  = course.professor_name || '-';
    document.getElementById('cd-credits').textContent = course.credit_hours || '-';

    document.getElementById('cd-midterm').textContent    = course.midterm ?? '-';
    document.getElementById('cd-activities').textContent = course.activities ?? '-';
    document.getElementById('cd-final').textContent      = course.final ?? '-';
    document.getElementById('cd-total').textContent      = course.total ?? '-';
    document.getElementById('cd-grade').textContent      = course.grade ?? '-';

    document.getElementById('cd-desc').textContent       = course.description || '-';

    const container = document.getElementById('cd-slots');
    if (!slots || slots.length === 0) {
        container.innerHTML = '<div class="text-muted">No upcoming slots.</div>';
    } else {
        let html = '<table class="table table-sm table-bordered mb-0"><thead class="table-light"><tr><th>Date</th><th>Start</th><th>End</th><th>Room</th></tr></thead><tbody>';
        slots.forEach(s => {
            html += `<tr><td>${s.slot_date}</td><td>${s.start_time?.slice(0,5)}</td><td>${s.end_time?.slice(0,5)}</td><td>${s.room_number}</td></tr>`;
        });
        html += '</tbody></table>';
        container.innerHTML = html;
    }
});
</script>