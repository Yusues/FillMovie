<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

// The currently logged-in user row, or null. Cached for the request.
function current_user(): ?array
{
    static $user = null;
    static $loaded = false;

    if ($loaded) {
        return $user;
    }
    $loaded = true;

    $id = $_SESSION['user_id'] ?? null;
    if (!$id) {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch() ?: null;
    return $user;
}

function require_login(): void
{
    if (!current_user()) {
        flash('Please sign in to continue.', 'info');
        redirect('login.php');
    }
}

// Check credentials and start a session. Returns true on success.
function attempt_login(string $email, string $password): bool
{
    $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    return true;
}

// Validate and create an account. Returns a list of error messages; empty means
// the user was created and logged in.
function register_user(array $input): array
{
    $errors = [];

    $first = trim($input['first_name'] ?? '');
    $last  = trim($input['last_name'] ?? '');
    $email = trim($input['email'] ?? '');
    $pass  = (string) ($input['password'] ?? '');
    $confirm = (string) ($input['confirm'] ?? '');
    $bio   = trim($input['bio'] ?? '');
    $birth = trim($input['birth_date'] ?? '');

    if ($first === '' || $last === '') {
        $errors[] = 'First and last name are required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email address is required.';
    }
    if (strlen($pass) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($pass !== $confirm) {
        $errors[] = 'The two passwords do not match.';
    }

    if ($email !== '') {
        $stmt = db()->prepare('SELECT 1 FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'That email is already registered.';
        }
    }

    if ($errors) {
        return $errors;
    }

    $stmt = db()->prepare(
        'INSERT INTO users (first_name, last_name, email, password_hash, bio, birth_date)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $first,
        $last,
        $email,
        password_hash($pass, PASSWORD_DEFAULT),
        $bio,
        $birth !== '' ? $birth : null,
    ]);

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) db()->lastInsertId();
    return [];
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
