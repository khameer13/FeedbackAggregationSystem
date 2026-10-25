<?php
require_once __DIR__ . '/../dbconfig.php';
$admin = require_role('admin', 'index.php');

$pages = [
    'add_faculty'      => 'add_faculty.php',
    'show_faculty'     => 'show_faculty.php',
    'edit_faculty'     => 'edit_faculty.php',
    'display_student'  => 'display_student.php',
    'contact'          => 'contact.php',
    'feedback'         => 'feedback.php',
    'feedback_average' => 'feedback_average.php',
    'update_password'  => 'update_password.php',
];
$info = $_GET['info'] ?? '';
$page = (is_string($info) && isset($pages[$info])) ? $pages[$info] : 'dashboard_home.php';

// Run the page first so it can redirect before any HTML is sent.
ob_start();
include __DIR__ . '/' . $page;
$content = ob_get_clean();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin dashboard</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/metisMenu.min.css" rel="stylesheet">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
</head>
<body>
<div id="wrapper">
    <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom:0">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="dashboard.php">Faculty Feedback System</a>
        </div>
        <ul class="nav navbar-top-links navbar-right">
            <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-user fa-fw"></i> <?= e($admin['email']) ?> <i class="fa fa-caret-down"></i></a>
                <ul class="dropdown-menu dropdown-user">
                    <li><a href="dashboard.php?info=update_password"><i class="fa fa-gear fa-fw"></i> Change password</a></li>
                    <li class="divider"></li>
                    <li>
                        <form method="post" action="logout.php" style="padding:3px 20px">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-link" style="padding:0"><i class="fa fa-sign-out fa-fw"></i> Logout</button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
        <div class="navbar-default sidebar" role="navigation">
            <div class="sidebar-nav navbar-collapse">
                <ul class="nav" id="side-menu">
                    <li><a href="dashboard.php"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a></li>
                    <li>
                        <a href="#"><i class="fa fa-user fa-fw"></i> Faculty<span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li><a href="dashboard.php?info=add_faculty"><i class="fa fa-plus fa-fw"></i> Add faculty</a></li>
                            <li><a href="dashboard.php?info=show_faculty"><i class="fa fa-eye fa-fw"></i> Manage faculty</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"><i class="fa fa-users fa-fw"></i> Students<span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li><a href="dashboard.php?info=display_student"><i class="fa fa-eye fa-fw"></i> Manage students</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"><i class="fa fa-book fa-fw"></i> Feedback<span class="fa arrow"></span></a>
                        <ul class="nav nav-second-level">
                            <li><a href="dashboard.php?info=feedback"><i class="fa fa-list fa-fw"></i> All feedback</a></li>
                            <li><a href="dashboard.php?info=feedback_average"><i class="fa fa-bar-chart fa-fw"></i> Feedback averages</a></li>
                        </ul>
                    </li>
                    <li><a href="dashboard.php?info=contact"><i class="fa fa-envelope fa-fw"></i> Contact messages</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12" style="padding-top:15px">
                <?php if ($flash): ?><?= alert($flash['message'], $flash['type']) ?><?php endif; ?>
                <?= $content ?>
            </div>
        </div>
    </div>
</div>
<script src="../css/jquery.min.js"></script>
<script src="../css/bootstrap.min.js"></script>
<script src="../css/metisMenu.min.js"></script>
<script src="../css/sb-admin-2.js"></script>
</body>
</html>
