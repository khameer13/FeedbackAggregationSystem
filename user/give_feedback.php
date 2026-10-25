<?php
defined('APP_BOOTSTRAPPED') || exit;

// Active faculty for the student's semester that the student has not rated yet.
$pending = db_all(
    'SELECT fa.id, fa.name, fa.designation
       FROM faculty fa
      WHERE fa.semester = ? AND fa.is_active = 1
        AND NOT EXISTS (SELECT 1 FROM feedback f WHERE f.faculty_id = fa.id AND f.student_id = ?)
      ORDER BY fa.name',
    [$student['semester'], $student['id']]
);
$pendingIds = array_map('intval', array_column($pending, 'id'));

$message = '';
$answers = [];
$liked = $disliked = '';
$facultyId = 0;

if (is_post()) {
    csrf_verify();
    $facultyId = (int) post('faculty');
    $liked = post('liked');
    $disliked = post('disliked');

    $errors = [];
    if (!in_array($facultyId, $pendingIds, true)) {
        $errors[] = 'Please choose a faculty member from the list.';
    }
    for ($q = 1; $q <= 12; $q++) {
        $value = filter_var($_POST['q' . $q] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 5]]);
        if ($value === false || $value === null) {
            $errors[] = 'Please answer every question.';
            break;
        }
        $answers[$q] = $value;
    }
    if (mb_strlen($liked) > 1000 || mb_strlen($disliked) > 1000) {
        $errors[] = 'Comments must be 1000 characters or fewer.';
    }

    if ($errors) {
        $message = alert(implode(' ', array_unique($errors)));
    } else {
        try {
            db_exec(
                'INSERT INTO feedback (student_id, faculty_id, q1, q2, q3, q4, q5, q6, q7, q8, q9, q10, q11, q12, liked, disliked)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                array_merge([(int) $student['id'], $facultyId], array_values($answers), [$liked, $disliked])
            );
            $_SESSION['flash'] = 'Thank you! Your feedback has been recorded.';
        } catch (mysqli_sql_exception $ex) {
            if ($ex->getCode() !== 1062) {   // 1062 = duplicate key: already submitted
                throw $ex;
            }
            $_SESSION['flash'] = 'You have already given feedback for this faculty member.';
        }
        redirect('index.php?page=feedback');
    }
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<h2 class="page-header">Student feedback form</h2>
<?= $flash ? alert($flash, 'success') : '' ?>
<?= $message ?>

<?php if (!$pending): ?>
    <p>You have given feedback for every faculty member teaching your semester. Thank you!</p>
<?php else: ?>
<form method="post" action="index.php?page=feedback">
    <?= csrf_field() ?>
    <p>Rate each statement: <?php foreach (RATING_LABELS as $r => $label): ?><span class="label label-default"><?= $r ?> = <?= e($label) ?></span> <?php endforeach; ?></p>

    <div class="form-group" style="margin-top:20px">
        <label for="fb-faculty">Faculty</label>
        <select id="fb-faculty" name="faculty" class="form-control" required>
            <option value="">Select faculty</option>
            <?php foreach ($pending as $f): ?>
                <option value="<?= (int) $f['id'] ?>" <?= $facultyId === (int) $f['id'] ? 'selected' : '' ?>><?= e($f['name']) ?> (<?= e($f['designation']) ?>)</option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php foreach (FEEDBACK_QUESTIONS as $section => $questions): ?>
        <h3><?= e($section) ?></h3>
        <table class="table table-bordered">
            <?php foreach ($questions as $q => $text): ?>
            <tr>
                <td><strong><?= $q ?>.</strong> <?= e($text) ?></td>
                <td style="white-space:nowrap">
                    <?php foreach (array_keys(RATING_LABELS) as $r): ?>
                        <label class="radio-inline"><input type="radio" name="q<?= $q ?>" value="<?= $r ?>" <?= ($answers[$q] ?? null) === $r ? 'checked' : '' ?> required> <?= $r ?></label>
                    <?php endforeach; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php endforeach; ?>

    <div class="form-group">
        <label for="fb-liked">What I liked about the course</label>
        <textarea id="fb-liked" name="liked" rows="4" class="form-control" maxlength="1000"><?= e($liked) ?></textarea>
    </div>
    <div class="form-group">
        <label for="fb-disliked">What I disliked about the course</label>
        <textarea id="fb-disliked" name="disliked" rows="4" class="form-control" maxlength="1000"><?= e($disliked) ?></textarea>
    </div>
    <p class="text-center"><button type="submit" class="btn btn-primary">Submit feedback</button></p>
</form>
<?php endif; ?>
