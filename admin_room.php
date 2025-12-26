
<?php
require_once 'auth.php';
require_role('admin');
require_once 'db.php';
require_once 'eav.php';

$pdo = getDB();
$errors = [];
$success = null;

/* Load courses for dropdown and summary */
$courses = $pdo->query('SELECT id, code, title, room FROM courses ORDER BY code')
               ->fetchAll(PDO::FETCH_ASSOC);

/* ===========================================================
   NEW PART (Manual time-based scheduling, unified form)
   - No auto slots.
   - Week = Saturday → Thursday only (Friday blocked).
   - Hours = 08:00..20:00 (1-hour slots).
   - DB UNIQUE keys enforce: room/time and course/time conflicts.
   =========================================================== */

function is_friday(string $date): bool {
    $d = new DateTimeImmutable($date);
    return $d->format('D') === 'Fri';
}
function is_hour_in_range(string $time): bool {
    $parts = explode(':', $time);
    if (count($parts) < 2) return false;
    $h = (int)$parts[0]; $m = (int)$parts[1];
    return $m === 0 && $h >= 8 && $h <= 19; // 19 → 20 end
}

/* Create booking from unified form */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_slot') {
    $slot_date   = trim($_POST['slot_date'] ?? '');
    $start_time  = trim($_POST['start_time'] ?? '');
    $room_number = (int)($_POST['room_number'] ?? 0);
    $course_id   = (int)($_POST['course_id'] ?? 0);
    $set_default = isset($_POST['set_default_room']) ? 1 : 0;

    if ($slot_date === '' || $start_time === '' || !$room_number || !$course_id) {
        $errors[] = 'All fields are required.';
    } elseif (is_friday($slot_date)) {
        $errors[] = 'Friday is not allowed.';
    } elseif (!is_hour_in_range($start_time)) {
        $errors[] = 'Start time must be a whole hour between 08:00 and 19:00.';
    }

    if ($room_number < 100 || $room_number > 900) {
        $errors[] = 'Room number must be between 100 and 900.';
    }

    // Course must exist (view-backed)
    if ($course_id) {
        $chk = $pdo->prepare('SELECT id FROM courses WHERE id = ?');
        $chk->execute([$course_id]);
        if (!$chk->fetch()) $errors[] = 'Selected course does not exist.';
    }

    if (empty($errors)) {
        $end_time = DateTimeImmutable::createFromFormat('H:i', substr($start_time,0,5))
                    ->modify('+1 hour')->format('H:i:00');

        try {
            $pdo->beginTransaction();

            // Insert booking
            $ins = $pdo->prepare('
                INSERT INTO room_schedule (slot_date, start_time, end_time, room_number, course_id)
                VALUES (?, ?, ?, ?, ?)
            ');
            $ins->execute([$slot_date, $start_time, $end_time, $room_number, $course_id]);

            // Optional: also set the course's default room attribute
            if ($set_default) {
                eav_set($course_id, 'course', 'room', $room_number);
            }

            $pdo->commit();
            $success = 'Slot added successfully.';
        } catch (Throwable $t) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $msg = $t->getMessage();
            if (strpos($msg, 'uq_room_slot') !== false) {
                $errors[] = 'This room is already booked at that time.';
            } elseif (strpos($msg, 'uq_course_slot') !== false) {
                $errors[] = 'This course is already booked at that time.';
            } else {
                $errors[] = 'Error: ' . $msg;
            }
        }
    }
}

/* Delete a booking from the grid */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_slot') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $pdo->prepare('DELETE FROM room_schedule WHERE id = ?')->execute([$id]);
        $success = 'Slot removed.';
    }
}

/* Week selection: default to nearest Saturday */
$weekStartParam = $_GET['week'] ?? '';
if ($weekStartParam === '') {
    $today = new DateTimeImmutable('today');
    $map = ['Sat'=>0,'Sun'=>1,'Mon'=>2,'Tue'=>3,'Wed'=>4,'Thu'=>5,'Fri'=>6];
    $offset = $map[$today->format('D')] ?? 0;
    $weekStart = ($offset === 0) ? $today : $today->modify('-'.$offset.' days');
} else {
    $weekStart = new DateTimeImmutable($weekStartParam);
}
$prevWeek = $weekStart->modify('-7 days')->format('Y-m-d');
$nextWeek = $weekStart->modify('+7 days')->format('Y-m-d');

$days = [];
for ($i=0; $i<7; $i++) $days[] = $weekStart->modify("+{$i} day");

/* Fetch booked slots for the selected week (Sat→Thu) */
$bookings = []; // [date][start_time] => list of bookings
$stmt = $pdo->prepare('
    SELECT s.*, c.code AS course_code, c.title AS course_title
    FROM room_schedule s
    LEFT JOIN courses c ON c.id = s.course_id
    WHERE slot_date BETWEEN ? AND ?
      AND DATE_FORMAT(slot_date, "%a") <> "Fri"
    ORDER BY slot_date, start_time, room_number
');
$stmt->execute([$days[0]->format('Y-m-d'), $days[6]->format('Y-m-d')]);
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $b) {
    $bookings[$b['slot_date']][$b['start_time']][] = $b;
}

include 'header.php';
?>

<div class="container my-3">
  <h3>Room Scheduling</h3>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
  <?php endif; ?>
  <?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <!-- Week selector (controls the window and constrains the date picker) -->
  <div class="card card-body mb-3">
    <div class="d-flex justify-content-between align-items-end">
      <div>
        <h5 class="mb-2">Week (Saturday → Thursday)</h5>
        <div class="text-muted">Time window: 08:00–20:00 (1-hour slots). Friday excluded.</div>
      </div>
      <form method="get" class="d-flex align-items-end gap-2">
        <div>
          <label class="form-label mb-0">Week start (Saturday)</label>
          <input type="date" name="week" class="form-control" value="<?= $weekStart->format('Y-m-d') ?>">
        </div>
        <div class="pb-1">
          <button class="btn btn-outline-primary">Go</button>
        </div>
      </form>
    </div>
    <div class="mt-2">
      <a class="btn btn-outline-secondary btn-sm" href="?week=<?= htmlspecialchars($prevWeek) ?>">Previous</a>
      <a class="btn btn-outline-secondary btn-sm" href="?week=<?= htmlspecialchars($nextWeek) ?>">Next</a>
    </div>
  </div>

  <!-- Unified form: schedule course + room + slot (date is limited to the selected week) -->
  <div class="card card-body mb-4">
    <h5 class="mb-3">Schedule a Room for a Course</h5>
    <form method="post" class="row g-3" id="scheduleForm">
      <input type="hidden" name="action" value="create_slot">

      <div class="col-md-4">
        <label class="form-label">Course</label>
        <select name="course_id" class="form-select" required>
          <option value="">-- select course --</option>
          <?php foreach ($courses as $c): ?>
            <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['code'].' — '.$c['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-2">
        <label class="form-label">Room #</label>
        <input name="room_number" type="number" min="100" max="900" class="form-control" placeholder="e.g., 101" required>
      </div>

      <?php
        // Bound the date input to the current week (Sat..Thu)
        $weekMin = $days[0]->format('Y-m-d');
        $weekMax = $days[6]->format('Y-m-d');
        // If weekMin is Friday (edge cases), shift to Saturday
        if ((new DateTimeImmutable($weekMin))->format('D') === 'Fri') $weekMin = $days[1]->format('Y-m-d');
        if ((new DateTimeImmutable($weekMax))->format('D') === 'Fri') $weekMax = $days[5]->format('Y-m-d');
      ?>

      <div class="col-md-3">
        <label class="form-label">Date (in this week)</label>
        <input name="slot_date" id="slotDate" type="date" class="form-control"
               min="<?= htmlspecialchars($weekMin) ?>" max="<?= htmlspecialchars($weekMax) ?>" required>
        <div class="form-text">Friday is blocked; choose a day in the selected week.</div>
      </div>

      <div class="col-md-2">
        <label class="form-label">Start Time</label>
        <select name="start_time" id="startTime" class="form-select" required>
          <?php for ($h=8; $h<=19; $h++): ?>
            <option value="<?= sprintf('%02d:00:00', $h) ?>"><?= sprintf('%02d:00', $h) ?></option>
          <?php endfor; ?>
        </select>
        <div class="form-text">1-hour slot; ends at +1h.</div>
      </div>

      <div class="col-md-12">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="set_default_room" name="set_default_room" value="1">
          <label class="form-check-label" for="set_default_room">
            Also set this room as the course’s default room attribute
          </label>
        </div>
      </div>

      <div class="col-12">
        <button class="btn btn-primary">Add Slot</button>
      </div>
    </form>
  </div>

  <!-- One window: week timetable (click an empty cell to prefill the form) -->
  <table class="table table-bordered table-sm" id="weekTable">
    <thead class="table-light">
      <tr>
        <th style="width:170px;">Day / Time</th>
        <?php for ($h=8; $h<20; $h++): ?>
          <th><?= sprintf('%02d:00', $h) ?></th>
        <?php endfor; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($days as $d):
          if ($d->format('D') === 'Fri') continue;
          $dayStr = $d->format('Y-m-d');
      ?>
        <tr>
          <th><?= $d->format('D') ?> <small class="text-muted">(<?= $dayStr ?>)</small></th>
          <?php for ($h=8; $h<20; $h++):
              $st = sprintf('%02d:00:00', $h);
              $cell = $bookings[$dayStr][$st] ?? [];
          ?>
            <td data-date="<?= htmlspecialchars($dayStr) ?>" data-time="<?= htmlspecialchars($st) ?>">
              <?php if (empty($cell)): ?>
                <span class="text-muted">—</span>
              <?php else: foreach ($cell as $b): ?>
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="badge bg-success">
                    <?= htmlspecialchars($b['course_code'] ?? 'Course') ?>
                    <span class="badge bg-light text-dark ms-1">Room <?= (int)$b['room_number'] ?></span>
                  </span>
                  <form method="post" onsubmit="return confirm('Remove this slot?');">
                    <input type="hidden" name="action" value="delete_slot">
                    <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </div>
              <?php endforeach; endif; ?>
            </td>
          <?php endfor; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="mt-3">
    <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
  </div>
</div>

<?php include 'footer.php'; ?>

<!-- Small script: clicking an empty cell prefills date/time in the unified form -->
<script>
(function () {
  const table = document.getElementById('weekTable');
  const dateInput = document.getElementById('slotDate');
  const timeSelect = document.getElementById('startTime');

  if (table && dateInput && timeSelect) {
    table.addEventListener('click', function (e) {
      const td = e.target.closest('td[data-date][data-time]');
      if (!td) return;

      // If the cell shows an existing booking, do nothing
      const hasBooking = td.querySelector('.badge');
      if (hasBooking) return;

      // Prefill the form with this day/time
      dateInput.value = td.getAttribute('data-date');
      timeSelect.value = td.getAttribute('data-time');
      // Scroll to the form for a smoother flow
      document.getElementById('scheduleForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  }
})();
</script>
