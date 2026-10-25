<?php
defined('APP_BOOTSTRAPPED') || exit;

$message = '';
$v = $faculty;
if (is_post()) {
    csrf_verify();
    foreach (['name', 'designation', 'programme', 'semester', 'mobile'] as $key) {
        $v[$key] = post($key);
    }

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

    if ($errors) {
        $message = alert(implode(' ', $errors));
    } else {
        db_exec(
            'UPDATE faculty SET name = ?, designation = ?, programme = ?, semester = ?, mobile = ? WHERE id = ?',
            [$v['name'], $v['designation'], $v['programme'], $v['semester'], $v['mobile'], $faculty['id']]
        );
        $message = alert('Profile updated.', 'success');
    }
}
?>
<h2 class="page-header">Update profile</h2>
<div class="col-lg-8">
    <?= $message ?>
    <form method="post" action="index.php?page=update_profile">
        <?= csrf_field() ?>
        <div class="form-group"><label>Name</label>
            <input type="text" name="name" class="form-control" maxlength="50" value="<?= e($v['name']) ?>" required></div>
        <div class="form-group"><label>Designation</label>
            <input type="text" name="designation" class="form-control" maxlength="100" value="<?= e($v['designation']) ?>" required></div>
        <div class="form-group"><label>Email</label>
            <input type="email" class="form-control" value="<?= e($faculty['email']) ?>" readonly></div>
        <div class="form-group"><label>Programme</label>
            <input type="text" name="programme" class="form-control" maxlength="50" value="<?= e($v['programme']) ?>" required></div>
        <div class="form-group"><label>Semester</label>
            <select name="semester" class="form-control" required>
                <?php foreach (SEMESTERS as $s): ?>
                    <option value="<?= e($s) ?>" <?= $v['semester'] === $s ? 'selected' : '' ?>><?= e(strtoupper($s)) ?></option>
                <?php endforeach; ?>
            </select></div>
        <div class="form-group"><label>Mobile number</label>
            <input type="tel" name="mobile" class="form-control" pattern="[0-9]{10}" value="<?= e($v['mobile']) ?>" required></div>
        <p class="text-muted">To change your password use <a href="index.php?page=update_password">Update password</a>.</p>
        <button type="submit" class="btn btn-success">Update profile</button>
    </form>
</div>
