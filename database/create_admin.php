<?php
/**
 * Create (or reset the password of) an admin account from the command line:
 *
 *     php database/create_admin.php admin@example.com
 *
 * The password is read interactively so it never lands in shell history.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../dbconfig.php';

$email = $argv[1] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Usage: php database/create_admin.php <email>\n");
    exit(1);
}

$readHidden = static function (string $prompt): string {
    fwrite(STDOUT, $prompt);
    $isTty = function_exists('posix_isatty') && posix_isatty(STDIN);
    if ($isTty) {
        shell_exec('stty -echo');
    }
    $line = rtrim((string) fgets(STDIN), "\r\n");
    if ($isTty) {
        shell_exec('stty echo');
    }
    fwrite(STDOUT, PHP_EOL);
    return $line;
};

$password = $readHidden('Password: ');
$confirm  = $readHidden('Confirm password: ');
if ($problem = password_problem($password, $confirm)) {
    fwrite(STDERR, $problem . PHP_EOL);
    exit(1);
}

db_exec(
    'INSERT INTO admins (email, password_hash) VALUES (?, ?)
     ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)',
    [$email, password_hash($password, PASSWORD_DEFAULT)]
);
echo "Admin account ready: $email" . PHP_EOL;
