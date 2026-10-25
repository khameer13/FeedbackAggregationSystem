<?php
defined('APP_BOOTSTRAPPED') || exit;

$message = '';
if (is_post()) {
    csrf_verify();
    $old     = (string) ($_POST['old_password'] ?? '');
    $new     = (string) ($_POST['new_password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    $row = db_one('SELECT password_hash FROM admins WHERE id = ?', [$admin['id']]);
    if (!verify_password($old, $row['password_hash'] ?? null)) {
        $message = alert('You entered the wrong old password.');
    } elseif ($problem = password_problem($new, $confirm)) {
        $message = alert($problem);
    } else {
        db_exec('UPDATE admins SET password_hash = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), $admin['id']]);
        $message = alert('Password changed successfully.', 'success');
    }
}
?>
<h1 class="page-header">Change password</h1>
<div class="col-lg-6">
    <?= $message ?>
    <form method="post" action="dashboard.php?info=update_password">
        <?= csrf_field() ?>
        <div class="form-group"><label>Old password</label>
            <input type="password" name="old_password" class="form-control" required></div>
        <div class="form-group"><label>New password (at least <?= PASSWORD_MIN_LENGTH ?> characters)</label>
            <input type="password" name="new_password" class="form-control" minlength="<?= PASSWORD_MIN_LENGTH ?>" required></div>
        <div class="form-group"><label>Confirm new password</label>
            <input type="password" name="confirm_password" class="form-control" minlength="<?= PASSWORD_MIN_LENGTH ?>" required></div>
        <button type="submit" class="btn btn-success">Update password</button>
    </form>
</div>
