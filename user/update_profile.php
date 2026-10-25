<?php
defined('APP_BOOTSTRAPPED') || exit;

$message = '';
$v = $student;
$v['hobbies'] = $student['hobbies'] !== '' ? explode(',', $student['hobbies']) : [];

if (is_post()) {
    csrf_verify();
    foreach (['name', 'mobile', 'gender', 'dob'] as $key) {
        $v[$key] = post($key);
    }
    $v['hobbies'] = array_values(array_intersect(array_keys(HOBBIES), (array) ($_POST['hobbies'] ?? [])));
    $dob = DateTime::createFromFormat('!Y-m-d', $v['dob']);

    $errors = [];
    if ($v['name'] === '' || mb_strlen($v['name']) > 40) {
        $errors[] = 'Please enter your name (up to 40 characters).';
    }
    if (!valid_mobile($v['mobile'])) {
        $errors[] = 'Mobile number must be 10 digits.';
    }
    if (!in_array($v['gender'], ['m', 'f'], true)) {
        $errors[] = 'Please select your gender.';
    }
    if (!$dob || $dob->format('Y-m-d') !== $v['dob'] || $dob > new DateTime('-10 years')) {
        $errors[] = 'Please enter a valid date of birth.';
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
            'UPDATE students SET name = ?, mobile = ?, gender = ?, hobbies = ?, dob = ? WHERE id = ?',
            [$v['name'], $v['mobile'], $v['gender'], implode(',', $v['hobbies']), $v['dob'], $student['id']]
        );
        if ($image !== null) {
            if ($student['image'] && preg_match('/^[a-f0-9]{32}\.(jpg|png|gif|webp)$/', $student['image'])) {
                @unlink(AVATAR_DIR . '/' . $student['image']);
            }
            db_exec('UPDATE students SET image = ? WHERE id = ?', [$image, $student['id']]);
        }
        $_SESSION['flash'] = 'Profile updated successfully.';
        redirect('index.php?page=update_profile');
    }
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<h2 class="page-header">Update your profile</h2>
<div class="col-sm-8">
    <?= $flash ? alert($flash, 'success') : '' ?>
    <?= $message ?>
    <form method="post" action="index.php?page=update_profile" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group"><label>Name</label>
            <input class="form-control" type="text" name="name" maxlength="40" value="<?= e($v['name']) ?>" required></div>
        <div class="form-group"><label>Email</label>
            <input class="form-control" type="email" value="<?= e($student['email']) ?>" readonly></div>
        <div class="form-group"><label>Mobile</label>
            <input class="form-control" type="tel" name="mobile" pattern="[0-9]{10}" value="<?= e($v['mobile']) ?>" required></div>
        <div class="form-group"><label>Gender</label><br>
            <label class="radio-inline"><input type="radio" name="gender" value="m" <?= $v['gender'] === 'm' ? 'checked' : '' ?> required> Male</label>
            <label class="radio-inline"><input type="radio" name="gender" value="f" <?= $v['gender'] === 'f' ? 'checked' : '' ?>> Female</label></div>
        <div class="form-group"><label>Hobbies</label><br>
            <?php foreach (HOBBIES as $key => $label): ?>
                <label class="checkbox-inline"><input type="checkbox" name="hobbies[]" value="<?= e($key) ?>" <?= in_array($key, $v['hobbies'], true) ? 'checked' : '' ?>> <?= e($label) ?></label>
            <?php endforeach; ?></div>
        <div class="form-group"><label>Date of birth</label>
            <input class="form-control" type="date" name="dob" value="<?= e($v['dob']) ?>" required></div>
        <div class="form-group"><label>New profile image (optional, max 2 MB)</label>
            <input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp"></div>
        <button type="submit" class="btn btn-primary">Update my profile</button>
    </form>
</div>
