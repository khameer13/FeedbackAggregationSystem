<?php
/**
 * Application bootstrap: configuration, database connection, session and
 * shared helpers. Every entry point includes this file.
 *
 * Database settings come from environment variables so that no credentials
 * are committed to the repository. The defaults match a local XAMPP install.
 */

declare(strict_types=1);

if (defined('APP_BOOTSTRAPPED')) {
    return;
}
define('APP_BOOTSTRAPPED', true);
define('APP_ROOT', __DIR__);
define('AVATAR_DIR', APP_ROOT . '/images/avatars');
define('AVATAR_URL', 'images/avatars');
define('AVATAR_MAX_BYTES', 2 * 1024 * 1024);

// ---------------------------------------------------------------------------
// Session (hardened cookie settings, started once for every page)
// ---------------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ---------------------------------------------------------------------------
// Database
// ---------------------------------------------------------------------------
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(
        getenv('DB_HOST') ?: 'localhost',
        getenv('DB_USER') ?: 'root',
        getenv('DB_PASS') ?: '',
        getenv('DB_NAME') ?: 'feedback_system',
        (int) (getenv('DB_PORT') ?: 3306)
    );
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $ex) {
    error_log('Database connection failed: ' . $ex->getMessage());
    http_response_code(500);
    exit('The service is temporarily unavailable. Please try again later.');
}

/**
 * Run a prepared statement and return the mysqli_stmt.
 *
 * @param string $sql    SQL with ? placeholders
 * @param array  $params Values bound in order; types are inferred
 */
function db_exec(string $sql, array $params = []): mysqli_stmt
{
    global $conn;
    $stmt = $conn->prepare($sql);
    if ($params) {
        $types = '';
        foreach ($params as $p) {
            $types .= is_int($p) ? 'i' : (is_float($p) ? 'd' : 's');
        }
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt;
}

/** Fetch all rows as associative arrays. */
function db_all(string $sql, array $params = []): array
{
    return db_exec($sql, $params)->get_result()->fetch_all(MYSQLI_ASSOC);
}

/** Fetch a single row (or null). */
function db_one(string $sql, array $params = []): ?array
{
    $row = db_exec($sql, $params)->get_result()->fetch_assoc();
    return $row ?: null;
}

/** Fetch a single scalar value (or null). */
function db_value(string $sql, array $params = [])
{
    $row = db_exec($sql, $params)->get_result()->fetch_row();
    return $row[0] ?? null;
}

// ---------------------------------------------------------------------------
// Output, input and navigation helpers
// ---------------------------------------------------------------------------

/** HTML-escape a value for output. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Read a trimmed string from POST (never use extract()). */
function post(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

/** Read a positive integer from GET, or 0. */
function get_id(string $key = 'id'): int
{
    $value = filter_var($_GET[$key] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $value ?: 0;
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/** Bootstrap alert markup for flash/status messages. */
function alert(string $message, string $type = 'danger'): string
{
    return '<div class="alert alert-' . e($type) . '">' . e($message) . '</div>';
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

// ---------------------------------------------------------------------------
// CSRF protection
// ---------------------------------------------------------------------------
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Abort the request when the CSRF token is missing or wrong. */
function csrf_verify(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(400);
        exit('Invalid or expired form. Please go back, reload the page and try again.');
    }
}

// ---------------------------------------------------------------------------
// Authentication (one session key per role, so roles never overlap)
// ---------------------------------------------------------------------------
const ROLES = ['admin', 'student', 'faculty'];

function login_as(string $role, int $id, string $email): void
{
    session_regenerate_id(true);
    $_SESSION['auth'] = ['role' => $role, 'id' => $id, 'email' => $email];
}

function current_user(): ?array
{
    $auth = $_SESSION['auth'] ?? null;
    return (is_array($auth) && in_array($auth['role'] ?? '', ROLES, true)) ? $auth : null;
}

/** Redirect to $loginUrl unless the visitor is logged in with $role. */
function require_role(string $role, string $loginUrl): array
{
    $user = current_user();
    if ($user === null || $user['role'] !== $role) {
        redirect($loginUrl);
    }
    return $user;
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/**
 * Check a password against a stored password_hash() value.
 * Runs a dummy verification when the account does not exist so that
 * response timing does not reveal which emails are registered.
 */
function verify_password(string $password, ?string $hash): bool
{
    static $dummy = null;
    if ($hash === null || $hash === '') {
        $dummy ??= password_hash('timing-equaliser', PASSWORD_DEFAULT);
        password_verify($password, $dummy);
        return false;
    }
    return password_verify($password, $hash);
}

const PASSWORD_MIN_LENGTH = 8;

function password_problem(string $password, string $confirm): ?string
{
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        return 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
    }
    if ($password !== $confirm) {
        return 'New password and confirm password do not match.';
    }
    return null;
}

// ---------------------------------------------------------------------------
// Domain constants
// ---------------------------------------------------------------------------
const SEMESTERS  = ['i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii'];
const PROGRAMMES = ['BCA', 'MCA', 'B.Tech', 'M.Tech', 'BE'];
const HOBBIES    = ['reading' => 'Reading', 'singing' => 'Singing', 'playing' => 'Playing'];

/** The 12 rated questions; keys match the q1..q12 columns. */
const FEEDBACK_QUESTIONS = [
    'Course material' => [
        1 => 'Teacher provided the course outline with a weekly content plan and list of required textbooks.',
        2 => 'Course objectives, learning outcomes and grading criteria are clear to me.',
        3 => 'Course integrates theoretical concepts with real-world examples.',
    ],
    'Class teaching' => [
        4  => 'Teacher is punctual, arrives on time and leaves on time.',
        5  => 'Teacher is good at stimulating interest in the course content.',
        6  => 'Teacher is good at explaining the subject matter.',
        7  => "Teacher's presentation was clear, loud and easy to understand.",
        8  => 'Teacher is good at using innovative teaching methods.',
        9  => 'Teacher is available and helpful during counselling hours.',
        10 => 'Teacher completed the whole course as per the course outline.',
    ],
    'Class assessment' => [
        11 => 'Teacher was always fair and impartial.',
        12 => 'Assessments are clearly connected to the learning objectives.',
    ],
];

/** Text of rated question $n (1..12). */
function question_text(int $n): string
{
    foreach (FEEDBACK_QUESTIONS as $questions) {
        if (isset($questions[$n])) {
            return $questions[$n];
        }
    }
    return '';
}

const RATING_LABELS = [5 => 'Strongly agree', 4 => 'Agree', 3 => 'Neutral', 2 => 'Disagree', 1 => 'Strongly disagree'];

function valid_mobile(string $mobile): bool
{
    return (bool) preg_match('/^[0-9]{10}$/', $mobile);
}

// ---------------------------------------------------------------------------
// Avatar uploads: type-checked, size-limited, random file names
// ---------------------------------------------------------------------------

/**
 * Validate and store an uploaded avatar image.
 * Returns [fileName|null, error|null]. A missing file is not an error.
 */
function store_avatar(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return [null, 'Image upload failed. Please try again.'];
    }
    if ($file['size'] > AVATAR_MAX_BYTES) {
        return [null, 'Image must be 2 MB or smaller.'];
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowed[$mime]) || @getimagesize($file['tmp_name']) === false) {
        return [null, 'Only JPG, PNG, GIF or WEBP images are allowed.'];
    }

    if (!is_dir(AVATAR_DIR) && !mkdir(AVATAR_DIR, 0755, true) && !is_dir(AVATAR_DIR)) {
        return [null, 'Could not save the image.'];
    }
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], AVATAR_DIR . '/' . $name)) {
        return [null, 'Could not save the image.'];
    }
    return [$name, null];
}

/** Public URL for a stored avatar, relative to a page $depth folders deep. */
function avatar_url(?string $file, int $depth = 1): string
{
    $prefix = str_repeat('../', $depth);
    if ($file && preg_match('/^[a-f0-9]{32}\.(jpg|png|gif|webp)$/', $file)) {
        return $prefix . AVATAR_URL . '/' . $file;
    }
    return $prefix . 'images/person.jpg';
}
