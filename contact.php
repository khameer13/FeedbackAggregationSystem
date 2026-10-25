<?php
defined('APP_BOOTSTRAPPED') || exit;

$message = '';
$values = ['name' => '', 'mobile' => '', 'email' => '', 'message' => ''];

if (is_post()) {
    csrf_verify();
    foreach ($values as $key => $_) {
        $values[$key] = post($key);
    }

    if ($values['name'] === '' || $values['message'] === '') {
        $message = alert('Please enter your name and a message.');
    } elseif (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $message = alert('Please enter a valid email address.');
    } elseif (!valid_mobile($values['mobile'])) {
        $message = alert('Mobile number must be 10 digits.');
    } elseif (mb_strlen($values['message']) > 1000) {
        $message = alert('Message must be 1000 characters or fewer.');
    } else {
        db_exec(
            'INSERT INTO contact (name, email, mobile, message) VALUES (?, ?, ?, ?)',
            [$values['name'], $values['email'], $values['mobile'], $values['message']]
        );
        $message = alert('Thank you. The admin will contact you soon.', 'success');
        $values = array_map(fn () => '', $values);
    }
}
?>
<div class="row">
    <div class="col-md-8">
        <h3>Contact us</h3>
        <?= $message ?>
        <form method="post" action="index.php?info=contact">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="c-name">Name</label>
                <input id="c-name" type="text" class="form-control" name="name" maxlength="50" value="<?= e($values['name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="c-mobile">Mobile number</label>
                <input id="c-mobile" type="tel" class="form-control" name="mobile" pattern="[0-9]{10}" value="<?= e($values['mobile']) ?>" required>
            </div>
            <div class="form-group">
                <label for="c-email">Email address</label>
                <input id="c-email" type="email" class="form-control" name="email" maxlength="100" value="<?= e($values['email']) ?>" required>
            </div>
            <div class="form-group">
                <label for="c-message">Message</label>
                <textarea id="c-message" rows="8" name="message" class="form-control" maxlength="1000" style="resize:none" required><?= e($values['message']) ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send message</button>
        </form>
    </div>
</div>
