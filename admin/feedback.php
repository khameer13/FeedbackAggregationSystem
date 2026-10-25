<?php
defined('APP_BOOTSTRAPPED') || exit;

$rows = db_all(
    'SELECT f.*, s.name AS student_name, s.email AS student_email, fa.name AS faculty_name
       FROM feedback f
       JOIN students s ON s.id = f.student_id
       JOIN faculty fa ON fa.id = f.faculty_id
      ORDER BY f.created_at DESC'
);
?>
<h1 class="page-header">All feedback</h1>
<?php if (!$rows): ?>
    <h3 class="text-danger">No feedback has been submitted yet.</h3>
<?php else: ?>
<p class="text-muted">Ratings: 5 = strongly agree &hellip; 1 = strongly disagree. Hover a column header to see the question.</p>
<div class="table-responsive">
<table class="table table-bordered table-condensed">
    <thead>
    <tr class="success">
        <th>#</th><th>Student</th><th>Faculty</th>
        <?php foreach (FEEDBACK_QUESTIONS as $section => $questions): foreach ($questions as $n => $text): ?>
            <th title="<?= e($text) ?>">Q<?= $n ?></th>
        <?php endforeach; endforeach; ?>
        <th>Liked</th><th>Disliked</th><th>Date</th><th>Delete</th>
    </tr>
    </thead>
    <?php foreach ($rows as $i => $row): ?>
    <tr>
        <td><?= $i + 1 ?></td>
        <td><?= e($row['student_name']) ?><br><small><?= e($row['student_email']) ?></small></td>
        <td><?= e($row['faculty_name']) ?></td>
        <?php for ($q = 1; $q <= 12; $q++): ?><td><?= (int) $row['q' . $q] ?></td><?php endfor; ?>
        <td><?= nl2br(e($row['liked'])) ?></td>
        <td><?= nl2br(e($row['disliked'])) ?></td>
        <td><?= e(substr($row['created_at'], 0, 10)) ?></td>
        <td class="text-center">
            <form method="post" action="delete_feedback.php" onsubmit="return confirm('Delete this feedback?');" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                <button class="btn btn-link" style="padding:0" title="Delete"><span class="glyphicon glyphicon-remove" style="color:red"></span></button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</div>
<?php endif; ?>
