<?php
defined('APP_BOOTSTRAPPED') || exit;

$message = '';
if (is_post()) {
    csrf_verify();
    $old     = (string) ($_POST['old_password'] ?? '');
    $new     = (string) ($_POST['new_password'] ?? '');
    $confirm = (string) ($_POST['confirm_password'] ?? '');

    // Check the old password of *this* student only.
    $hash = db_value('SELECT password_hash FROM students WHERE id = ?', [$student['id']]);
    if (!verify_password($old, $hash)) {
        $message = alert('Wrong old password.');
    } elseif ($problem = password_problem($new, $confirm)) {
        $message = alert($problem);
    } else {
        db_exec('UPDATE students SET password_hash = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), $student['id']]);
        $message = alert('Password updated.', 'success');
    }
}
?>
<h2 class="page-header">Update password</h2>
<div class="col-sm-6">
    <?= $message ?>
    <form method="post" action="index.php?page=update_password">
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
