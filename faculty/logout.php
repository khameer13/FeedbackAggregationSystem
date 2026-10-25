<?php
require_once __DIR__ . '/../dbconfig.php';

if (is_post()) {
    csrf_verify();
    logout();
}
redirect('../index.php');
