<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>UMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CDN -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
    >
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <?php
            $dashboard = 'index.php';
            if (isset($_SESSION['user']['role'])) {
                switch ($_SESSION['user']['role']) {
                    case 'admin': $dashboard = 'admin_dashboard.php'; break;
                    case 'student': $dashboard = 'student_dashboard.php'; break;
                    case 'professor': $dashboard = 'professor_dashboard.php'; break;
                    case 'parent': $dashboard = 'parent_dashboard.php'; break;
                }
            }
        ?>
        <a class="navbar-brand" href="<?= htmlspecialchars($dashboard) ?>">UMS</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user'])): ?>
                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="admin_create_user.php">Create User</a></li>
                        <li class="nav-item"><a class="nav-link" href="admin_parent_link.php">Parent Linking</a></li>
                        <li class="nav-item"><a class="nav-link" href="admin_requests.php">Requests</a></li>
                    <?php elseif ($_SESSION['user']['role'] === 'parent'): ?>
                        <li class="nav-item"><a class="nav-link" href="parent_dashboard.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="parent_requests.php">Requests</a></li>
                        <li class="nav-item"><a class="nav-link" href="parent_announcements.php">Announcements</a></li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            <?= htmlspecialchars($_SESSION['user']['name']) ?>
                            (<?= htmlspecialchars($_SESSION['user']['role']) ?>)
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container">
