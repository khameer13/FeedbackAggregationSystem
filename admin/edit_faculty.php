<?php
defined('APP_BOOTSTRAPPED') || exit;

$id = get_id();
$faculty = $id ? db_one('SELECT id, name, designation, programme, semester, email, mobile FROM faculty WHERE id = ?', [$id]) : null;
if (!$faculty) {
    echo alert('Faculty member not found.');
    return;
}

$message = '';
if (is_post()) {
    csrf_verify();
    $v = [];
    foreach (['name', 'designation', 'programme', 'semester', 'mobile'] as $key) {
        $v[$key] = post($key);
    }
    $password = $_POST['password'] ?? '';
    $password = is_string($password) ? $password : '';

    $errors = [];
    if ($v['name'] === '' || $v['designation'] === '' || $v['programme'] === '') {
        $errors[] = 'Name, designation and programme are required.';
    }
    if (!in_array($v['semester'], SEMESTERS, true)) {
        $errors[] = 'Please select a semester.';
    }
    if (!valid_mobile($v['mobile'])) {
        $errors[] = 'Mobile number must be 10 digits.';
    }
    if ($password !== '' && strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
    }

    if ($errors) {
        $message = alert(implode(' ', $errors));
        $faculty = array_merge($faculty, $v);
    } else {
        db_exec(
            'UPDATE faculty SET name = ?, designation = ?, programme = ?, semester = ?, mobile = ? WHERE id = ?',
            [$v['name'], $v['designation'], $v['programme'], $v['semester'], $v['mobile'], $id]
        );
        if ($password !== '') {
            db_exec('UPDATE faculty SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $id]);
        }
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Faculty details updated.'];
        redirect('dashboard.php?info=show_faculty');
    }
}
?>
<h1 class="page-header">Update faculty</h1>
<div class="col-lg-8">
    <?= $message ?>
    <form method="post" action="dashboard.php?info=edit_faculty&amp;id=<?= (int) $faculty['id'] ?>">
        <?= csrf_field() ?>
        <div class="form-group"><label>Name</label>
            <input type="text" name="name" class="form-control" maxlength="50" value="<?= e($faculty['name']) ?>" required></div>
        <div class="form-group"><label>Designation</label>
            <input type="text" name="designation" class="form-control" maxlength="100" value="<?= e($faculty['designation']) ?>" required></div>
        <div class="form-group"><label>Email</label>
            <input type="email" class="form-control" value="<?= e($faculty['email']) ?>" readonly></div>
        <div class="form-group"><label>New password (leave blank to keep the current one)</label>
            <input type="password" name="password" class="form-control" autocomplete="new-password"></div>
        <div class="form-group"><label>Programme</label>
            <input type="text" name="programme" class="form-control" maxlength="50" value="<?= e($faculty['programme']) ?>" required></div>
        <div class="form-group"><label>Semester</label>
            <select name="semester" class="form-control" required>
                <?php foreach (SEMESTERS as $s): ?>
                    <option value="<?= e($s) ?>" <?= $faculty['semester'] === $s ? 'selected' : '' ?>><?= e(strtoupper($s)) ?></option>
                <?php endforeach; ?>
            </select></div>
        <div class="form-group"><label>Mobile number</label>
            <input type="tel" name="mobile" class="form-control" pattern="[0-9]{10}" value="<?= e($faculty['mobile']) ?>" required></div>
        <button type="submit" class="btn btn-success">Update faculty</button>
    </form>
</div>
