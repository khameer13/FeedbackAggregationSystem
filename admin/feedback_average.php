<?php
defined('APP_BOOTSTRAPPED') || exit;

$facultyList = db_all('SELECT id, name, designation FROM faculty ORDER BY name');
$facultyId = get_id('faculty');

$selected = null;
$stats = [];
$total = 0;
if ($facultyId) {
    $selected = db_one('SELECT id, name, designation FROM faculty WHERE id = ?', [$facultyId]);
}
if ($selected) {
    $total = (int) db_value('SELECT COUNT(*) FROM feedback WHERE faculty_id = ?', [$facultyId]);
    if ($total > 0) {
        // One pass over the rows: average and 5..1 distribution for every question.
        $selects = [];
        for ($q = 1; $q <= 12; $q++) {
            $selects[] = "AVG(q$q) AS avg$q";
            for ($r = 5; $r >= 1; $r--) {
                $selects[] = "SUM(q$q = $r) AS q{$q}_r$r";
            }
        }
        $agg = db_one('SELECT ' . implode(', ', $selects) . ' FROM feedback WHERE faculty_id = ?', [$facultyId]);
        for ($q = 1; $q <= 12; $q++) {
            $dist = [];
            for ($r = 5; $r >= 1; $r--) {
                $dist[$r] = (int) $agg["q{$q}_r$r"];
            }
            $stats[$q] = ['avg' => (float) $agg["avg$q"], 'dist' => $dist];
        }
    }
}
$overall = $stats ? array_sum(array_column($stats, 'avg')) / count($stats) : null;
?>
<h1 class="page-header">Feedback averages</h1>
<form method="get" action="dashboard.php" class="form-inline" style="margin-bottom:20px">
    <input type="hidden" name="info" value="feedback_average">
    <label for="fa-faculty">Faculty</label>
    <select id="fa-faculty" name="faculty" class="form-control" required>
        <option value="">Select faculty</option>
        <?php foreach ($facultyList as $f): ?>
            <option value="<?= (int) $f['id'] ?>" <?= $facultyId === (int) $f['id'] ? 'selected' : '' ?>><?= e($f['name']) ?> (<?= e($f['designation']) ?>)</option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-success">Show averages</button>
</form>

<?php if ($facultyId && !$selected): ?>
    <?= alert('Faculty member not found.') ?>
<?php elseif ($selected && $total === 0): ?>
    <p>No feedback yet for <strong><?= e($selected['name']) ?></strong>.</p>
<?php elseif ($selected): ?>
    <h4><?= e($selected['name']) ?>: <?= $total ?> student response<?= $total === 1 ? '' : 's' ?>,
        overall average <strong><?= number_format($overall, 2) ?></strong> / 5</h4>
    <div class="table-responsive">
    <table class="table table-bordered table-hover">
        <tr class="success">
            <th>#</th><th>Question</th><th>Average</th>
            <?php foreach (RATING_LABELS as $r => $label): ?><th title="<?= e($label) ?>"><?= $r ?></th><?php endforeach; ?>
        </tr>
        <?php foreach (FEEDBACK_QUESTIONS as $section => $questions): ?>
            <tr><th colspan="<?= 3 + count(RATING_LABELS) ?>" class="active"><?= e($section) ?></th></tr>
            <?php foreach ($questions as $q => $text): ?>
            <tr>
                <td><?= $q ?></td>
                <td><?= e($text) ?></td>
                <td><strong><?= number_format($stats[$q]['avg'], 2) ?></strong></td>
                <?php foreach ($stats[$q]['dist'] as $count): ?><td><?= $count ?></td><?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </table>
    </div>
<?php endif; ?>
