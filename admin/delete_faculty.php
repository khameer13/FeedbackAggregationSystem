<?php
require_once __DIR__ . '/../dbconfig.php';
require_role('admin', 'index.php');

if (!is_post()) {
    redirect('dashboard.php?info=show_faculty');
}
csrf_verify();

$id = (int) post('id');
if ($id > 0) {
    db_exec('DELETE FROM faculty WHERE id = ?', [$id]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Faculty member deleted.'];
}
redirect('dashboard.php?info=show_faculty');
