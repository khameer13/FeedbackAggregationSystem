<?php
defined('APP_BOOTSTRAPPED') || exit;

// Feedback is shown anonymously to faculty: no student names or emails.
$rows = db_all('SELECT * FROM feedback WHERE faculty_id = ? ORDER BY created_at DESC', [$faculty['id']]);
$averages = [];
if ($rows) {
    for ($q = 1; $q <= 12; $q++) {
        $averages[$q] = array_sum(array_column($rows, 'q' . $q)) / count($rows);
    }
}
?>
<h2 class="page-header">My feedback</h2>
<?php if (!$rows): ?>
    <h3 class="text-danger">No feedback received yet.</h3>
<?php else: ?>
<p class="text-muted"><?= count($rows) ?> response<?= count($rows) === 1 ? '' : 's' ?>. Ratings: 5 = strongly agree &hellip; 1 = strongly disagree. Hover a column header to see the question.</p>
<div class="table-responsive">
<table class="table table-bordered table-condensed">
    <thead>
    <tr class="success">
        <th>#</th>
        <?php for ($q = 1; $q <= 12; $q++): ?><th title="<?= e(question_text($q)) ?>">Q<?= $q ?></th><?php endfor; ?>
        <th>Liked</th><th>Disliked</th><th>Date</th>
    </tr>
    </thead>
    <tr class="info">
        <th>Avg</th>
        <?php for ($q = 1; $q <= 12; $q++): ?><th><?= number_format($averages[$q], 1) ?></th><?php endfor; ?>
        <th colspan="3"></th>
    </tr>
    <?php foreach ($rows as $i => $row): ?>
    <tr>
        <td><?= $i + 1 ?></td>
        <?php for ($q = 1; $q <= 12; $q++): ?><td><?= (int) $row['q' . $q] ?></td><?php endfor; ?>
        <td><?= nl2br(e($row['liked'])) ?></td>
        <td><?= nl2br(e($row['disliked'])) ?></td>
        <td><?= e(substr($row['created_at'], 0, 10)) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
</div>
<?php endif; ?>
