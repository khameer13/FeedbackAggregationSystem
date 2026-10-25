<?php
defined('APP_BOOTSTRAPPED') || exit;

$message = '';
$v = [
    'name' => '', 'email' => '', 'mobile' => '', 'programme' => '', 'semester' => '',
    'gender' => '', 'dob' => '', 'hobbies' => [],
];

if (is_post()) {
    csrf_verify();
    foreach (['name', 'email', 'mobile', 'programme', 'semester', 'gender', 'dob'] as $key) {
        $v[$key] = post($key);
    }
    $v['hobbies'] = array_values(array_intersect(array_keys(HOBBIES), (array) ($_POST['hobbies'] ?? [])));
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $password = is_string($password) ? $password : '';
    $confirm  = is_string($confirm) ? $confirm : '';

    $dob = DateTime::createFromFormat('!Y-m-d', $v['dob']);
    $errors = [];
    if ($v['name'] === '' || mb_strlen($v['name']) > 40) {
        $errors[] = 'Please enter your name (up to 40 characters).';
    }
    if (!filter_var($v['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($problem = password_problem($password, $confirm)) {
        $errors[] = $problem;
    }
    if (!valid_mobile($v['mobile'])) {
        $errors[] = 'Mobile number must be 10 digits.';
    }
    if (!in_array($v['programme'], PROGRAMMES, true)) {
        $errors[] = 'Please select your programme.';
    }
    if (!in_array($v['semester'], SEMESTERS, true)) {
        $errors[] = 'Please select your semester.';
    }
    if (!in_array($v['gender'], ['m', 'f'], true)) {
        $errors[] = 'Please select your gender.';
    }
    if (!$dob || $dob->format('Y-m-d') !== $v['dob'] || $dob > new DateTime('-10 years')) {
        $errors[] = 'Please enter a valid date of birth.';
    }
    if (!$errors && db_value('SELECT 1 FROM students WHERE email = ?', [$v['email']])) {
        $errors[] = 'An account with this email already exists.';
    }

    $image = null;
    if (!$errors) {
        [$image, $uploadError] = store_avatar($_FILES['image'] ?? []);
        if ($uploadError) {
            $errors[] = $uploadError;
        }
    }

    if ($errors) {
        $message = alert(implode(' ', $errors));
    } else {
        db_exec(
            'INSERT INTO students (name, email, password_hash, mobile, programme, semester, gender, hobbies, image, dob)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $v['name'], $v['email'], password_hash($password, PASSWORD_DEFAULT), $v['mobile'],
                $v['programme'], $v['semester'], $v['gender'], implode(',', $v['hobbies']), $image, $v['dob'],
            ]
        );
        $message = alert('Registration successful. You can now log in.', 'success');
        $v = array_map(fn ($x) => is_array($x) ? [] : '', $v);
    }
}
?>
<div class="row">
    <div class="col-sm-8 col-sm-offset-2">
        <h2 class="text-center">Student registration</h2>
        <?= $message ?>
        <form method="post" action="index.php?info=registration" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="r-name">Name</label>
                <input id="r-name" type="text" name="name" class="form-control" maxlength="40" value="<?= e($v['name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="r-email">Email</label>
                <input id="r-email" type="email" name="email" class="form-control" maxlength="100" value="<?= e($v['email']) ?>" required>
            </div>
            <div class="form-group">
                <label for="r-pass">Password (at least <?= PASSWORD_MIN_LENGTH ?> characters)</label>
                <input id="r-pass" type="password" name="password" class="form-control" minlength="<?= PASSWORD_MIN_LENGTH ?>" required>
            </div>
            <div class="form-group">
                <label for="r-pass2">Confirm password</label>
                <input id="r-pass2" type="password" name="confirm_password" class="form-control" minlength="<?= PASSWORD_MIN_LENGTH ?>" required>
            </div>
            <div class="form-group">
                <label for="r-mobile">Mobile</label>
                <input id="r-mobile" type="tel" name="mobile" class="form-control" pattern="[0-9]{10}" value="<?= e($v['mobile']) ?>" required>
            </div>
            <div class="form-group">
                <label for="r-prog">Programme</label>
                <select id="r-prog" name="programme" class="form-control" required>
                    <option value="">Select</option>
                    <?php foreach (PROGRAMMES as $p): ?>
                        <option value="<?= e($p) ?>" <?= $v['programme'] === $p ? 'selected' : '' ?>><?= e($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="r-sem">Semester</label>
                <select id="r-sem" name="semester" class="form-control" required>
                    <option value="">Select</option>
                    <?php foreach (SEMESTERS as $s): ?>
                        <option value="<?= e($s) ?>" <?= $v['semester'] === $s ? 'selected' : '' ?>><?= e(strtoupper($s)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Gender</label><br>
                <label class="radio-inline"><input type="radio" name="gender" value="m" <?= $v['gender'] === 'm' ? 'checked' : '' ?> required> Male</label>
                <label class="radio-inline"><input type="radio" name="gender" value="f" <?= $v['gender'] === 'f' ? 'checked' : '' ?>> Female</label>
            </div>
            <div class="form-group">
                <label>Hobbies</label><br>
                <?php foreach (HOBBIES as $key => $label): ?>
                    <label class="checkbox-inline"><input type="checkbox" name="hobbies[]" value="<?= e($key) ?>" <?= in_array($key, $v['hobbies'], true) ? 'checked' : '' ?>> <?= e($label) ?></label>
                <?php endforeach; ?>
            </div>
            <div class="form-group">
                <label for="r-img">Profile image (optional, JPG/PNG/GIF/WEBP, max 2 MB)</label>
                <input id="r-img" type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
            </div>
            <div class="form-group">
                <label for="r-dob">Date of birth</label>
                <input id="r-dob" type="date" name="dob" class="form-control" value="<?= e($v['dob']) ?>" required>
            </div>
            <button type="submit" class="btn btn-info">Register</button>
            <button type="reset" class="btn btn-default">Reset</button>
        </form>
    </div>
</div>
