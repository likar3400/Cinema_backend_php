<?php
declare(strict_types=1);
namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params(['lifetime'=>SESSION_LIFETIME,'path'=>'/','httponly'=>true,'samesite'=>'Lax']);
            session_start();
        }
        if (empty($_SESSION['_init'])) { session_regenerate_id(true); $_SESSION['_init'] = true; }
        if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    public static function set(string $k, mixed $v): void   { $_SESSION[$k] = $v; }
    public static function get(string $k, mixed $d = null): mixed { return $_SESSION[$k] ?? $d; }
    public static function has(string $k): bool             { return isset($_SESSION[$k]); }
    public static function remove(string $k): void          { unset($_SESSION[$k]); }

    public static function flash(string $k, string $msg): void       { $_SESSION['flash'][$k] = $msg; }
    public static function getFlash(string $k): ?string
    { $m = $_SESSION['flash'][$k] ?? null; unset($_SESSION['flash'][$k]); return $m; }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function isLoggedIn(): bool { return isset($_SESSION['user_id']); }
    public static function isAdmin(): bool    { return ($_SESSION['user_role'] ?? '') === 'admin'; }
    public static function userId(): ?int     { return $_SESSION['user_id'] ?? null; }
    public static function userName(): string { return $_SESSION['user_name'] ?? ''; }
    public static function csrfToken(): string { return $_SESSION['csrf_token'] ?? ''; }
}
