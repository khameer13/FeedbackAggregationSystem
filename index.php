<?php
require_once __DIR__ . '/dbconfig.php';

// Whitelisted public pages. Each page runs (and may redirect) before any
// layout HTML is sent, then its output is placed inside the layout.
$pages = [
    'about'         => 'about.php',
    'contact'       => 'contact.php',
    'login'         => 'login.php',
    'faculty_login' => 'faculty_login.php',
    'registration'  => 'registration.php',
];
$info = $_GET['info'] ?? '';
$info = is_string($info) ? $info : '';

$content = null;
if (isset($pages[$info])) {
    ob_start();
    include __DIR__ . '/' . $pages[$info];
    $content = ob_get_clean();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Online Feedback System</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/modern-business.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-default navbar-fixed-top" style="background:#66CCFF">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#main-nav">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="index.php" style="color:#FFFFFF">Online Feedback System</a>
        </div>
        <div class="collapse navbar-collapse" id="main-nav">
            <ul class="nav navbar-nav navbar-right">
                <li><a style="color:#FFFFFF" href="index.php"><i class="fa fa-home fa-fw"></i> Home</a></li>
                <li><a style="color:#FFFFFF" href="index.php?info=about"><i class="fa fa-info-circle fa-fw"></i> About</a></li>
                <li><a style="color:#FFFFFF" href="index.php?info=registration"><i class="fa fa-user-plus fa-fw"></i> Registration</a></li>
                <li class="dropdown">
                    <a style="color:#FFFFFF" href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-sign-in fa-fw"></i> Login <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="index.php?info=login">Student</a></li>
                        <li><a href="index.php?info=faculty_login">Faculty</a></li>
                        <li><a href="admin/">Admin</a></li>
                    </ul>
                </li>
                <li><a style="color:#FFFFFF" href="index.php?info=contact"><i class="fa fa-phone fa-fw"></i> Contact</a></li>
            </ul>
        </div>
    </div>
</nav>

<?php if ($content !== null): ?>
    <div class="container" style="margin-top:80px;margin-bottom:80px"><?= $content ?></div>
<?php else: ?>
    <header id="myCarousel" class="carousel slide">
        <ol class="carousel-indicators">
            <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
            <li data-target="#myCarousel" data-slide-to="1"></li>
            <li data-target="#myCarousel" data-slide-to="2"></li>
        </ol>
        <div class="carousel-inner">
            <div class="item active"><div class="fill" style="background-image:url('images/feedback.jpg');"></div></div>
            <div class="item"><div class="fill" style="background-image:url('images/feedback1.jpg');"></div></div>
            <div class="item"><div class="fill" style="background-image:url('images/feedback3.jpg');"></div></div>
        </div>
        <a class="left carousel-control" href="#myCarousel" data-slide="prev"><span class="icon-prev"></span></a>
        <a class="right carousel-control" href="#myCarousel" data-slide="next"><span class="icon-next"></span></a>
    </header>
    <div class="container">
        <div class="row">
            <div class="col-sm-10" style="margin-top:60px;margin-bottom:80px">
                <h2>About the Faculty Feedback System</h2>
                <p>Students rate each faculty member who teaches their semester on twelve questions
                   (1&nbsp;=&nbsp;strongly disagree, 5&nbsp;=&nbsp;strongly agree) and can leave written comments.
                   Faculty see the feedback they received, and administrators manage faculty and student
                   accounts and view per-question averages for each faculty member.</p>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="navbar-fixed-bottom nav navbar-inverse text-center" style="padding:10px;background:#66CCFF">
    <span style="color:#FFFFFF">College project &middot; based on an open-source template by
        <a href="https://www.phptpoint.com" style="color:#FFFFFF;text-decoration:underline">Phptpoint</a></span>
</div>

<script src="css/jquery.min.js"></script>
<script src="css/bootstrap.min.js"></script>
<script>$('.carousel').carousel({ interval: 5000 });</script>
</body>
</html>
