<?php
declare(strict_types=1);
namespace App\Core\Http;
class Response
{
    private static int $code = 200;

    public static function status(int $code): void
    {
        self::$code = $code;
        http_response_code($code);
    }

    public static function getStatus(): int { return self::$code; }

    public static function json(mixed $data, int $code = 200): never
    {
        self::status($code);
        header('Content-Type: application/json; charset=utf-8');
        // Заголовки залежно від статус-коду
        match(true) {
            $code >= 400 => self::noCache(),
            $code === 201 => self::noCache(),
            default       => null,
        };
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    public static function redirect(string $url, int $code = 302): never
    {
        self::status($code);
        // 301 кешується браузером — додаємо Cache-Control
        if ($code === 301) {
            header('Cache-Control: public, max-age=31536000');
        } else {
            self::noCache();
        }
        $fullUrl = str_starts_with($url, 'http') ? $url : APP_URL . $url;
        header('Location: ' . $fullUrl);
        exit;
    }

    public static function redirectAfterPost(string $url): never
    {
        self::redirect($url, 303);
    }

    public static function unauthorized(string $redirectTo = '/login'): never
    {
        self::status(401);
        self::noCache();
        header('WWW-Authenticate: Bearer realm="CineMax"');
        self::redirect($redirectTo, 303);
    }
    public static function cacheHeaders(string $etag, int $ttl = CACHE_TTL): void
    {
        header('Cache-Control: public, max-age=' . $ttl);
        header('ETag: "' . $etag . '"');
        header('Vary: Accept-Encoding');

        $clientEtag = trim($_SERVER['HTTP_IF_NONE_MATCH'] ?? '', '"');
        if ($clientEtag === $etag) {
            self::status(304);
            ob_end_clean();
            exit;
        }
    }

    public static function lastModified(int $timestamp): void
    {
        $lastModified = gmdate('D, d M Y H:i:s', $timestamp) . ' GMT';
        header('Last-Modified: ' . $lastModified);

        $ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';
        if ($ifModifiedSince && strtotime($ifModifiedSince) >= $timestamp) {
            self::status(304);
            ob_end_clean();
            exit;
        }
    }

    public static function noCache(): void
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
    }
    public static function setLocation(string $url): void
    {
        header('Location: ' . APP_URL . $url);
    }
}
