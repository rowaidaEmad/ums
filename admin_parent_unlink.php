<?php
require_once 'auth.php';
require_role('admin');
require_once 'eav.php';

$parent_id = (int)($_POST['parent_id'] ?? 0);
$student_id = (int)($_POST['student_ids'][0] ?? 0);

if ($parent_id > 0 && $student_id > 0) {
    eav_unlink_parent_student($parent_id, $student_id);
}
header('Location: admin_parent_link.php?parent_id=' . $parent_id);
exit;
