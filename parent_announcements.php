

<?php
require_once 'auth.php';
require_role('parent');

// Ensure DB connection
if (!isset($pdo) || !($pdo instanceof PDO)) {
    if (file_exists(__DIR__ . '/db.php')) {
        require_once __DIR__ . '/db.php';
    }
}

// Fallback if db.php didn't set $pdo
if (!isset($pdo) || !($pdo instanceof PDO)) {
    $dsn  = 'mysql:host=localhost;dbname=ums_eav;charset=utf8mb4';
    $user = 'root';
    $pass = '';
    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (Throwable $e) {
        include 'header.php';
        echo '<div class="alert alert-danger">Database connection failed: '
           . htmlspecialchars($e->getMessage()) . '</div>';
        echo '<a href="parent_dashboard.php" class="btn btn-secondary mt-3">Back</a>';
        include 'footer.php';
        exit;
    }
}

include 'header.php';
?>
<h3 class="mb-4">University Announcements</h3>

<?php
$announcements = null;
$errorTip = null;

try {
    // Try announcements view
    $sql = "SELECT id, title, message, audience, created_at
            FROM announcements
            WHERE LOWER(audience) IN ('all','parents')
            ORDER BY id DESC
            LIMIT 20";
    $stmt = $pdo->query($sql);
    $announcements = $stmt->fetchAll();

} catch (Throwable $e) {
    // Fallback to EAV join
    try {
        $sql = "
        SELECT e.id,
               MAX(CASE WHEN a.name='title' THEN v.value_string END) AS title,
               MAX(CASE WHEN a.name='message' THEN v.value_text END) AS message,
               MAX(CASE WHEN a.name='audience' THEN v.value_string END) AS audience,
               e.created_at
        FROM entities e
        LEFT JOIN eav_values v ON v.entity_id = e.id
        LEFT JOIN eav_attributes a ON a.id = v.attribute_id AND a.entity_type='announcement'
        WHERE e.entity_type='announcement'
        GROUP BY e.id, e.created_at
        ORDER BY e.id DESC
        LIMIT 20";
        $rows = $pdo->query($sql)->fetchAll();
        $announcements = [];
        foreach ($rows as $r) {
            $aud = strtolower(trim($r['audience'] ?? 'all'));
            if ($aud === 'parents' || $aud === 'all') {
                $announcements[] = $r;
            }
        }
    } catch (Throwable $ignored) {
        $errorTip = "Announcements aren’t initialized yet. Ask the admin to run the patch at the end of <code>init.sql</code> and restart the app.";
    }
}
?>

<div class="card p-3">
<?php if ($errorTip): ?>
    <div class="alert alert-danger"><?php echo $errorTip; ?></div>
<?php elseif (empty($announcements)): ?>
    <div class="alert alert-info">No announcements yet.</div>
<?php else: ?>
    <ul class="list-group list-group-flush">
        <?php foreach ($announcements as $a): ?>
            <li class="list-group-item">
                <h5><?php echo htmlspecialchars($a['title'] ?? '(No title)'); ?></h5>
                <p style="white-space:pre-wrap;"><?php echo htmlspecialchars($a['message'] ?? ''); ?></p>
                <small class="text-muted">
                    Audience: <?php echo htmlspecialchars($a['audience'] ?? 'all'); ?> ·
                    Created: <?php echo htmlspecialchars($a['created_at'] ?? ''); ?>
                </small>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
</div>

<a href="parent_dashboard.php" class="btn btn-secondary mt-3">Back</a>
<?php include 'footer.php'; ?>
