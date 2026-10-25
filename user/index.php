<?php
require_once __DIR__ . '/../dbconfig.php';
$auth = require_role('student', '../index.php?info=login');

$student = db_one(
    'SELECT id, name, email, mobile, programme, semester, gender, hobbies, image, dob FROM students WHERE id = ?',
    [$auth['id']]
);
if (!$student) {           // account was deleted while logged in
    logout();
    redirect('../index.php?info=login');
}

$pages = [
    'update_password' => 'update_password.php',
    'update_profile'  => 'update_profile.php',
    'feedback'        => 'give_feedback.php',
];
$page = $_GET['page'] ?? '';
$page = is_string($page) ? $page : '';

$content = null;
if (isset($pages[$page])) {
    ob_start();
    include __DIR__ . '/' . $pages[$page];
    $content = ob_get_clean();
}
$given = (int) db_value('SELECT COUNT(*) FROM feedback WHERE student_id = ?', [$student['id']]);
$available = (int) db_value(
    'SELECT COUNT(*) FROM faculty WHERE semester = ? AND is_active = 1', [$student['semester']]
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student dashboard &middot; Faculty Feedback System</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/dashboard.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-inverse navbar-fixed-top" style="background:#428bca">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" style="color:#FFFFFF" href="index.php">Hello, <?= e($student['name']) ?></a>
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
                <li><a href="#"><img style="border-radius:50px" src="<?= e(avatar_url($student['image'])) ?>" width="100" height="100" alt="Profile picture"></a></li>
                <li><a href="index.php?page=update_password"><span class="glyphicon glyphicon-lock"></span> Update password</a></li>
                <li><a href="index.php?page=update_profile"><span class="glyphicon glyphicon-user"></span> Update profile</a></li>
                <li><a href="index.php?page=feedback"><span class="glyphicon glyphicon-pencil"></span> Give feedback</a></li>
            </ul>
        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <?php if ($content !== null): ?>
                <?= $content ?>
            <?php else: ?>
                <h1 class="page-header">Dashboard</h1>
                <p><?= e($student['programme']) ?>, semester <?= e(strtoupper($student['semester'])) ?></p>
                <p>You have given feedback for <strong><?= $given ?></strong> of <strong><?= $available ?></strong>
                   faculty members teaching your semester.</p>
                <?php if ($given < $available): ?>
                    <a class="btn btn-primary" href="index.php?page=feedback">Give feedback</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="../css/jquery.min.js"></script>
<script src="../css/bootstrap.min.js"></script>
</body>
</html>
