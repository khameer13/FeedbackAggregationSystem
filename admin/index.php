<?php
require_once __DIR__ . '/../dbconfig.php';

$user = current_user();
if ($user && $user['role'] === 'admin') {
    redirect('dashboard.php');
}

$error = '';
$email = '';
if (is_post()) {
    csrf_verify();
    $email = post('email');
    $password = $_POST['password'] ?? '';
    $password = is_string($password) ? $password : '';

    $admin = db_one('SELECT id, email, password_hash FROM admins WHERE email = ?', [$email]);
    if ($email !== '' && verify_password($password, $admin['password_hash'] ?? null)) {
        login_as('admin', (int) $admin['id'], $admin['email']);
        redirect('dashboard.php');
    }
    $error = 'Wrong email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin login</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/metisMenu.min.css" rel="stylesheet">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
    <link href="../css/font-awesome.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="login-panel panel panel-default">
                <div class="panel-heading"><h3 class="panel-title">Admin sign in</h3></div>
                <div class="panel-body">
                    <?= $error ? alert($error) : '' ?>
                    <form method="post">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <input class="form-control" name="email" type="email" placeholder="E-mail" value="<?= e($email) ?>" autofocus required>
                        </div>
                        <div class="form-group">
                            <input class="form-control" name="password" type="password" placeholder="Password" required>
                        </div>
                        <button type="submit" class="btn btn-lg btn-success btn-block">Login</button>
                    </form>
                    <p class="text-muted" style="margin-top:15px"><a href="../index.php">&larr; Back to site</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../css/jquery.min.js"></script>
<script src="../css/bootstrap.min.js"></script>
</body>
</html>
