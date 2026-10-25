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
        $student = db_one('SELECT id, email, password_hash FROM students WHERE email = ?', [$email]);
        if (verify_password($password, $student['password_hash'] ?? null)) {
            login_as('student', (int) $student['id'], $student['email']);
            redirect('user/');
        }
        $error = 'Invalid email or password.';
    }
}
?>
<div class="row">
    <div class="col-sm-6 col-sm-offset-3">
        <h2>Student login</h2>
        <?= $error ? alert($error) : '' ?>
        <form method="post" action="index.php?info=login">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="s-email">Email</label>
                <input id="s-email" type="email" name="email" class="form-control" value="<?= e($email) ?>" required autofocus>
            </div>
            <div class="form-group">
                <label for="s-pass">Password</label>
                <input id="s-pass" type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-info">Login</button>
        </form>
    </div>
</div>
