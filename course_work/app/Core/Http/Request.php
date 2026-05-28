<?php
declare(strict_types=1);
namespace App\Core\Http;

class Request
{
    private string $uri;
    private string $method;

    public function __construct()
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $scriptDir  = dirname($_SERVER['SCRIPT_NAME']);

        $path = parse_url($requestUri, PHP_URL_PATH) ?? '/';
        if ($scriptDir !== '/' && str_starts_with($path, $scriptDir)) {
            $path = substr($path, strlen($scriptDir));
        }

        $this->uri    = '/' . trim($path, '/');
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function uri(): string    { return $this->uri; }
    public function method(): string { return $this->method; }
    public function isAjax(): bool   { return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'; }
    public function get(string $k, mixed $d = null): mixed  { return $_GET[$k] ?? $d; }
    public function post(string $k, mixed $d = null): mixed { return $_POST[$k] ?? $d; }
    public function all(): array { return array_merge($_GET, $_POST); }
    public function json(): array
    {
        $raw = file_get_contents('php://input');
        return json_decode($raw ?: '{}', true) ?? [];
    }
    public function sanitize(string $v): string
    { return htmlspecialchars(trim($v), ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
}
