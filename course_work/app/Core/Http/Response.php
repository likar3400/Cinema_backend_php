<?php
declare(strict_types=1);
namespace App\Core\Http;

/**
 * Response — управління HTTP-відповіддю та буферизацією за статус-кодами.
 *
 * Логіка буферизації:
 *  200 → кеш на диск + ETag → 304 Not Modified (не завантажуємо вдруге)
 *  201 → Created (після POST, без кешу)
 *  301 → Permanent Redirect (кешується браузером назавжди)
 *  303 → See Other (після POST → GET, без кешу)
 *  304 → Not Modified (відправляємо без тіла)
 *  401 → Unauthorized (не кешується, редірект на /login)
 *  403 → Forbidden (не кешується)
 *  404 → Not Found (не кешується)
 *  409 → Conflict (не кешується — напр. місце вже зайняте)
 *  422 → Unprocessable Entity (валідаційна помилка)
 *  500 → Server Error (не кешується)
 */
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

    /**
     * Редірект з правильним статус-кодом:
     *  301 — постійний (змінилась URL назавжди)
     *  302 — тимчасовий (за замовчуванням)
     *  303 — після POST (See Other → GET)
     *  307 — тимчасовий зі збереженням методу
     */
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

    /** 303 See Other — правильний редірект після POST (PRG патерн) */
    public static function redirectAfterPost(string $url): never
    {
        self::redirect($url, 303);
    }

    /** 401 — потрібна авторизація */
    public static function unauthorized(string $redirectTo = '/login'): never
    {
        self::status(401);
        self::noCache();
        header('WWW-Authenticate: Bearer realm="CineMax"');
        self::redirect($redirectTo, 303);
    }

    /** ETag + кешування для 200 відповідей */
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

    /** Перевірка If-Modified-Since для 304 */
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

    /** Забороняємо кешування */
    public static function noCache(): void
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
    }

    /** Заголовок Location без зупинки (для інформаційних відповідей) */
    public static function setLocation(string $url): void
    {
        header('Location: ' . APP_URL . $url);
    }
}
