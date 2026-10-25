<?php
require_once __DIR__ . '/../dbconfig.php';
$auth = require_role('faculty', '../index.php?info=faculty_login');

$faculty = db_one(
    'SELECT id, name, designation, programme, semester, email, mobile, is_active FROM faculty WHERE id = ?',
    [$auth['id']]
);
if (!$faculty || !(int) $faculty['is_active']) {   // deleted or deactivated while logged in
    logout();
    redirect('../index.php?info=faculty_login');
}

$pages = [
    'update_password' => 'update_password.php',
    'update_profile'  => 'update_profile.php',
    'feedback'        => 'feedback.php',
];
$page = $_GET['page'] ?? '';
$page = is_string($page) ? $page : '';

$content = null;
if (isset($pages[$page])) {
    ob_start();
    include __DIR__ . '/' . $pages[$page];
    $content = ob_get_clean();
}
$received = (int) db_value('SELECT COUNT(*) FROM feedback WHERE faculty_id = ?', [$faculty['id']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Faculty dashboard &middot; Faculty Feedback System</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/dashboard.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-inverse navbar-fixed-top" style="background:#428bca">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" style="color:#FFFFFF" href="index.php">Hello, <?= e($faculty['name']) ?></a>
        </div>
        <ul class="nav navbar-nav navbar-right">
            <li>
                <form method="post" action="logout.php" class="navbar-form">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-link" style="color:#FFFFFF">Logout</button>
                </form>
            </li>
        </ul>
    </div>
</nav>
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
            <ul class="nav nav-sidebar">
                <li class="<?= $content === null ? 'active' : '' ?>"><a href="index.php">Dashboard</a></li>
                <li><a href="index.php?page=feedback"><span class="glyphicon glyphicon-list"></span> My feedback</a></li>
                <li><a href="index.php?page=update_profile"><span class="glyphicon glyphicon-user"></span> Update profile</a></li>
                <li><a href="index.php?page=update_password"><span class="glyphicon glyphicon-lock"></span> Update password</a></li>
            </ul>
        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <?php if ($content !== null): ?>
                <?= $content ?>
            <?php else: ?>
                <h1 class="page-header">Dashboard</h1>
                <p><?= e($faculty['designation']) ?> &middot; <?= e($faculty['programme']) ?>, semester <?= e(strtoupper($faculty['semester'])) ?></p>
                <p>You have received <strong><?= $received ?></strong> feedback form<?= $received === 1 ? '' : 's' ?>.</p>
                <a class="btn btn-primary" href="index.php?page=feedback">View my feedback</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="../css/jquery.min.js"></script>
<script src="../css/bootstrap.min.js"></script>
</body>
</html>
