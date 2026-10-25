<?php
defined('APP_BOOTSTRAPPED') || exit;

$message = '';
$v = ['name' => '', 'designation' => '', 'email' => '', 'programme' => '', 'semester' => '', 'mobile' => ''];

if (is_post()) {
    csrf_verify();
    foreach ($v as $key => $_) {
        $v[$key] = post($key);
    }
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $password = is_string($password) ? $password : '';
    $confirm  = is_string($confirm) ? $confirm : '';

    $errors = [];
    if ($v['name'] === '' || $v['designation'] === '' || $v['programme'] === '') {
        $errors[] = 'Name, designation and programme are required.';
    }
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (!in_array($v['semester'], SEMESTERS, true)) {
        $errors[] = 'Please select a semester.';
    }
    if (!valid_mobile($v['mobile'])) {
        $errors[] = 'Mobile number must be 10 digits.';
    }
    if ($problem = password_problem($password, $confirm)) {
        $errors[] = $problem;
    }
    if (!$errors && db_value('SELECT 1 FROM faculty WHERE email = ?', [$v['email']])) {
        $errors[] = 'This email already exists. Choose a different email.';
    }

    if ($errors) {
        $message = alert(implode(' ', $errors));
    } else {
        db_exec(
            'INSERT INTO faculty (name, designation, programme, semester, email, password_hash, mobile)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$v['name'], $v['designation'], $v['programme'], $v['semester'], $v['email'],
             password_hash($password, PASSWORD_DEFAULT), $v['mobile']]
        );
        // The password is shared with the faculty member out of band; it is never emailed in plain text.
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'New faculty member added.'];
        redirect('dashboard.php?info=show_faculty');
    }
}
?>
<h1 class="page-header">Add faculty</h1>
<div class="col-lg-8">
    <?= $message ?>
    <form method="post" action="dashboard.php?info=add_faculty">
        <?= csrf_field() ?>
        <div class="form-group"><label>Name</label>
            <input type="text" name="name" class="form-control" maxlength="50" value="<?= e($v['name']) ?>" required></div>
        <div class="form-group"><label>Designation</label>
            <input type="text" name="designation" class="form-control" maxlength="100" value="<?= e($v['designation']) ?>" required></div>
        <div class="form-group"><label>Email</label>
            <input type="email" name="email" class="form-control" maxlength="100" value="<?= e($v['email']) ?>" required></div>
        <div class="form-group"><label>Password (at least <?= PASSWORD_MIN_LENGTH ?> characters)</label>
            <input type="password" name="password" class="form-control" minlength="<?= PASSWORD_MIN_LENGTH ?>" required></div>
        <div class="form-group"><label>Confirm password</label>
            <input type="password" name="confirm_password" class="form-control" minlength="<?= PASSWORD_MIN_LENGTH ?>" required></div>
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
        <button type="submit" class="btn btn-success">Add faculty</button>
    </form>
</div>
