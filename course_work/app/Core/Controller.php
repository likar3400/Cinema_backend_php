<?php
declare(strict_types=1);
namespace App\Core;
use App\Core\Http\Request;
use App\Core\Http\Response;

abstract class Controller
{
    public function __construct(protected readonly Request $request) {}

    protected function render(string $view, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        $file = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($file)) return "View [{$view}] not found.";
        ob_start(); require $file; return (string)ob_get_clean();
    }

    protected function view(string $view, array $data = [], string $layout = 'partials/layout'): void
    {
        $layout_use = $layout;
        extract($data, EXTR_SKIP);
        $file = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';
        if (file_exists($file)) {
            ob_start(); include $file;
            $data['content'] = ob_get_clean();
            if (isset($layout) && is_string($layout)) $layout_use = $layout;
        } else {
            $data['content'] = "View [{$view}] not found.";
        }
        echo $this->render($layout_use, $data);
    }

    protected function json(mixed $data, int $code = 200): never
    {
        Response::json($data, $code);
    }

    /** 302 redirect (GET) */
    protected function redirect(string $url): never
    {
        Response::redirect($url, 302);
    }

    /** 303 See Other — правильний після POST (PRG pattern) */
    protected function redirectAfterPost(string $url): never
    {
        Response::redirect($url, 303);
    }

    /** 301 Permanent redirect */
    protected function redirectPermanent(string $url): never
    {
        Response::redirect($url, 301);
    }

    protected function verifyCsrf(): void
    {
        $token = $this->request->post(CSRF_TOKEN_NAME)
            ?? $this->request->json()[CSRF_TOKEN_NAME]
            ?? '';
        if (!hash_equals(Session::csrfToken(), $token)) {
            Response::status(403);
            if ($this->request->isAjax()) {
                $this->json(['error' => 'CSRF token mismatch'], 403);
            }
            $this->view('error/403', ['title'=>'403','message'=>'Невалідний CSRF-токен']);
            exit;
        }
    }

    /** 401 Unauthorized → редірект на /login */
    protected function requireAuth(): void
    {
        if (!Session::isLoggedIn()) {
            Response::status(401);
            Response::noCache();
            // Якщо AJAX — повертаємо JSON 401
            if ($this->request->isAjax()) {
                $this->json(['error' => 'Unauthorized', 'redirect' => APP_URL . '/login'], 401);
            }
            // Зберігаємо куди повернутись після логіну
            Session::set('redirect_after_login', $this->request->uri());
            Response::redirect('/login', 303);
        }
    }

    /** 403 Forbidden */
    protected function requireAdmin(): void
    {
        $this->requireAuth();
        if (!Session::isAdmin()) {
            Response::status(403);
            Response::noCache();
            if ($this->request->isAjax()) {
                $this->json(['error' => 'Forbidden'], 403);
            }
            $this->view('error/403', ['title'=>'403 — Доступ заборонено','message'=>'Доступ заборонено']);
            exit;
        }
    }

    protected function h(string $v): string
    { return htmlspecialchars($v, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
}
