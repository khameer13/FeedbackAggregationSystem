<?php
defined('APP_BOOTSTRAPPED') || exit;

$facultyCount  = (int) db_value('SELECT COUNT(*) FROM faculty');
$studentCount  = (int) db_value('SELECT COUNT(*) FROM students');
$feedbackCount = (int) db_value('SELECT COUNT(*) FROM feedback');
?>
<h1 class="page-header">Admin dashboard</h1>
<div class="row">
    <div class="col-sm-4"><div class="panel panel-primary"><div class="panel-heading"><h3><?= $facultyCount ?></h3>Faculty</div></div></div>
    <div class="col-sm-4"><div class="panel panel-yellow panel-warning"><div class="panel-heading"><h3><?= $studentCount ?></h3>Students</div></div></div>
    <div class="col-sm-4"><div class="panel panel-green panel-success"><div class="panel-heading"><h3><?= $feedbackCount ?></h3>Feedback forms submitted</div></div></div>
</div>
