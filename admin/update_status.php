<?php
require_once __DIR__ . '/../dbconfig.php';
require_role('admin', 'index.php');

if (!is_post()) {
    redirect('dashboard.php?info=show_faculty');
}
csrf_verify();

$id = (int) post('id');
if ($id > 0) {
    db_exec('UPDATE faculty SET is_active = 1 - is_active WHERE id = ?', [$id]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Faculty status updated.'];
}
redirect('dashboard.php?info=show_faculty');
