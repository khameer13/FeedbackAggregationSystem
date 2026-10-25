<?php
require_once __DIR__ . '/../dbconfig.php';
require_role('admin', 'index.php');

if (!is_post()) {
    redirect('dashboard.php?info=contact');
}
csrf_verify();

$id = (int) post('id');
if ($id > 0) {
    db_exec('DELETE FROM contact WHERE id = ?', [$id]);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Message deleted.'];
}
redirect('dashboard.php?info=contact');
