<?php
declare(strict_types=1);
namespace App\Core;
use App\Core\Http\Response;

class PageBuffer
{
    private static bool $started = false;
    private static string $currentKey = '';

    public static function start(): void
    {
        if (!self::$started) {
            ob_start();
            self::$started = true;
        }
    }

    public static function flush(string $key = ''): void
    {
        if (!self::$started) return;

        $code    = Response::getStatus();
        $content = (string) ob_get_clean();
        self::$started = false;
        match(true) {
            $code === 200 && CACHE_ENABLED && $key !== '' => self::write($key, $content),
            $code >= 400 && $key !== '' => self::delete($key),

            default => null,
        };

        echo $content;
    }

    public static function check(string $key, int $ttl = CACHE_TTL): string|false
    {
        if (!CACHE_ENABLED) return false;
        $f = self::path($key);
        if (!file_exists($f)) return false;
        if ((time() - filemtime($f)) > $ttl) { unlink($f); return false; }
        return (string) file_get_contents($f);
    }


    public static function invalidate(string $prefix): void
    {
        if (!is_dir(CACHE_PATH)) return;
        foreach (glob(CACHE_PATH . '/' . $prefix . '*.cache') ?: [] as $f) {
            unlink($f);
        }
    }

    public static function clearAll(): int
    {
        if (!is_dir(CACHE_PATH)) return 0;
        $count = 0;
        foreach (glob(CACHE_PATH . '/*.cache') ?: [] as $f) {
            unlink($f); $count++;
        }
        return $count;
    }

    private static function write(string $key, string $content): void
    {
        if (!is_dir(CACHE_PATH)) mkdir(CACHE_PATH, 0755, true);
        file_put_contents(self::path($key), $content, LOCK_EX);
    }

    private static function delete(string $key): void
    {
        $f = self::path($key);
        if (file_exists($f)) unlink($f);
    }

    private static function path(string $key): string
    {
        return CACHE_PATH . '/' . md5($key) . '.cache';
    }
}
