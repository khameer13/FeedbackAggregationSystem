<?php
require_once __DIR__ . '/../dbconfig.php';
require_role('admin', 'index.php');

if (!is_post()) {
    redirect('dashboard.php?info=feedback');
}
csrf_verify();

$id = (int) post('id');
if ($id > 0) {
    db_exec('DELETE FROM feedback WHERE id = ?', [$id]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Feedback deleted.'];
}
redirect('dashboard.php?info=feedback');
