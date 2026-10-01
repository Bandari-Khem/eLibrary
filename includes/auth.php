<?php

declare(strict_types=1);
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}
function require_login(): void
{
    if (!current_user()) {
        flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
    if (!empty($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > 1800) {
        logout_user();
        flash('error', 'Your session expired. Please log in again.');
        redirect('login.php');
    }
    $_SESSION['last_activity'] = time();
}
function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user'] = ['id' => (int)$user['id'], 'full_name' => $user['full_name'], 'email' => $user['email'], 'role' => $user['role']];
    $_SESSION['last_activity'] = time();
}
function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', ['expires' => time() - 42000, 'path' => $p['path'], 'domain' => $p['domain'], 'secure' => $p['secure'], 'httponly' => $p['httponly'], 'samesite' => $p['samesite'] ?? 'Lax']);
    }
    session_destroy();
}
function dashboard_for_role(string $role): string
{
    return match ($role) {
        'admin' => 'admin/dashboard.php',
        'librarian' => 'librarian/dashboard.php',
        default => 'user/dashboard.php'
    };
}
function require_role(string ...$roles): void
{
    require_login();
    if (!in_array(current_user()['role'] ?? '', $roles, true)) {
        redirect('403.php');
    }
}
