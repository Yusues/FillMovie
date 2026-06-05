<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

// Escape a value for safe HTML output. Use this on everything that came from a
// user or the database.
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Send the browser to another page and stop. Paths are relative to the public
// folder, e.g. redirect('feed.php').
function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

// --- CSRF ---------------------------------------------------------------

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

// Verify the token on a POST request. Stops with 400 if it doesn't match.
function csrf_check(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(400);
        exit('Invalid request token. Go back and try again.');
    }
}

// --- Flash messages -----------------------------------------------------

function flash(string $message, string $type = 'info'): void
{
    $_SESSION['flashes'][] = ['message' => $message, 'type' => $type];
}

function take_flashes(): array
{
    $flashes = $_SESSION['flashes'] ?? [];
    unset($_SESSION['flashes']);
    return $flashes;
}

// --- Formatting ---------------------------------------------------------

function format_date(?string $datetime): string
{
    if (!$datetime) {
        return '';
    }
    $ts = strtotime($datetime);
    return $ts ? date('M j, Y', $ts) : '';
}

function full_name(array $user): string
{
    return trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
}
