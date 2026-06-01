<?php
declare(strict_types=1);
namespace App\Core;
use App\Core\Http\Request;
use App\Core\Http\Response;

class Router
{
    private array $routes = [];

    public function get(string $p, string $c, string $a): void  { $this->add('GET',  $p,$c,$a); }
    public function post(string $p, string $c, string $a): void { $this->add('POST', $p,$c,$a); }

    private function add(string $m, string $p, string $c, string $a): void
    { $this->routes[$m][$p] = ['controller'=>$c,'action'=>$a]; }

    public function dispatch(Request $req): void
    {
        $method = $req->method();
        if ($method === 'POST' && $req->post('_method'))
            $method = strtoupper($req->post('_method'));

        $uri = $req->uri();
        foreach ($this->routes[$method] ?? [] as $pattern => $target) {
            $params = $this->match($pattern, $uri);
            if ($params !== null) { $this->run($target, $params, $req); return; }
        }
        Response::status(404);
        $this->run(['controller'=>'App\\Controllers\\ErrorController','action'=>'notFound'], [], $req);
    }

    private function match(string $pattern, string $uri): ?array
    {
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        if (preg_match('#^' . $regex . '$#u', $uri, $m))
            return array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
        return null;
    }

    private function run(array $t, array $p, Request $req): void
    {
        $class  = $t['controller'];
        $action = $t['action'];

        if (!class_exists($class)) {
            Response::status(500);
            error_log("Router: Controller {$class} not found");
            $this->showError(500, 'Внутрішня помилка сервера.');
            return;
        }

        $ctrl = new $class($req);

        if (!method_exists($ctrl, $action)) {
            Response::status(500);
            error_log("Router: Action {$action} not found in {$class}");
            $this->showError(500, 'Внутрішня помилка сервера.');
            return;
        }

        $ctrl->{$action}($p);
    }

    private function showError(int $code, string $message): void
    {
        $isAjax = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            || str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/');

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }

        $view = defined('APP_PATH')
            ? APP_PATH . '/Views/error/' . $code . '.php'
            : __DIR__ . '/../Views/error/' . $code . '.php';

        if (file_exists($view)) {
            require $view;
        } else {
            echo '<h1>' . $code . '</h1><p>' . htmlspecialchars($message) . '</p>';
        }
        exit;
    }
}