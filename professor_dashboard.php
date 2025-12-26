<?php
require_once 'auth.php';
require_role('professor');

require_once 'db.php';
$pdo = getDB();
$professor_id = $_SESSION['user']['id'] ?? null;

$message_count = 0;
if ($professor_id !== null) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) AS c
            FROM entities e
            JOIN eav_values v_to ON v_to.entity_id = e.id
            JOIN eav_attributes a_to ON a_to.id = v_to.attribute_id
            WHERE e.entity_type = 'message'
              AND a_to.entity_type = 'message'
              AND a_to.name = 'to_user_id'
              AND v_to.value_int = ?
        ");
        $stmt->execute([$professor_id]);
        $message_count = (int)($stmt->fetchColumn() ?? 0);
    } catch (Exception $ex) {
        // If messaging schema isn't present yet, keep count as 0 (don't break dashboard)
        $message_count = 0;
    }
}
?>
<?php include 'header.php'; ?>


<h3>Professor Dashboard</h3>
<p>Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?>.</p>

<div style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px;">
    <!-- View courses -->
    <a href="professor_courses.php" style="
        display: block;
        width: 200px;
        padding: 20px;
        text-align: center;
        text-decoration: none;
        color: #fff;
        background: linear-gradient(135deg, #696cd6ff,  #696cd6ff);
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        transition: transform 0.2s, box-shadow 0.2s;
    " onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.3)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)';">
        <img src="icons/course.png" alt="Courses" style="width:50px; margin-bottom:10px;">
        <div style="font-weight:bold; font-size:16px;">View My Courses</div>
    </a>

    <!-- Messages Inbox -->
    <a href="professor_messages.php" style="
        display: block;
        width: 200px;
        padding: 20px;
        text-align: center;
        text-decoration: none;
        color: #fff;
        background: linear-gradient(135deg, #28a745, #218838);
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        transition: transform 0.2s, box-shadow 0.2s;
    " onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.3)';"
      onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.2)';">
        <img src="icons/message.png" alt="Messages" style="width:50px; margin-bottom:10px;">
        <div style="font-weight:bold; font-size:16px;">Messages</div>
        <div style="margin-top:6px; font-size:14px;">
            <?php if ($message_count > 0): ?>
                <span style="background:#ffc107; color:#000; padding:2px 10px; border-radius:14px; font-weight:bold;">
                    <?= $message_count ?> new
                </span>
            <?php else: ?>
                <span>No new messages</span>
            <?php endif; ?>
        </div>
    </a>


   

<?php include 'footer.php'; ?>
