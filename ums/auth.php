<?php
session_start();

function require_login()
{
    if (!isset($_SESSION['user'])) {
        header('Location: index.php');
        exit;
    }
}

function require_role($role)
{
    require_login();
    if ($_SESSION['user']['role'] !== $role) {
        http_response_code(403);
        echo "403 Forbidden - You don't have access to this page.";
        exit;
    }
}

function redirect_by_role($role)
{
    switch ($role) {
        case 'admin':
            header('Location: admin_dashboard.php');
            break;
        case 'student':
            header('Location: student_dashboard.php');
            break;
        case 'professor':
            header('Location: professor_dashboard.php');
            break;
        default:
            header('Location: index.php');
    }
    exit;
}
?>
