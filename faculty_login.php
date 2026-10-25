<?php
defined('APP_BOOTSTRAPPED') || exit;

$error = '';
$email = '';

if (is_post()) {
    csrf_verify();
    $email = post('email');
    $password = $_POST['password'] ?? '';

    if ($email === '' || !is_string($password) || $password === '') {
        $error = 'Please fill in all the fields.';
    } else {
        $faculty = db_one('SELECT id, email, password_hash, is_active FROM faculty WHERE email = ?', [$email]);
        if (verify_password($password, $faculty['password_hash'] ?? null)) {
            if (!(int) $faculty['is_active']) {
                $error = 'Your account has been deactivated. Please contact the admin.';
            } else {
                login_as('faculty', (int) $faculty['id'], $faculty['email']);
                redirect('faculty/');
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<div class="row">
    <div class="col-sm-6 col-sm-offset-3">
        <h2>Faculty login</h2>
        <?= $error ? alert($error) : '' ?>
        <form method="post" action="index.php?info=faculty_login">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="f-email">Email</label>
                <input id="f-email" type="email" name="email" class="form-control" value="<?= e($email) ?>" required autofocus>
            </div>
            <div class="form-group">
                <label for="f-pass">Password</label>
                <input id="f-pass" type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-info">Login</button>
        </form>
    </div>
</div>
