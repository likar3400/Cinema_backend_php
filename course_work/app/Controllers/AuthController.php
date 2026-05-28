<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Http\Response;
use App\Models\UserModel;

class AuthController extends Controller
{
    private UserModel $users;
    public function __construct($r) { parent::__construct($r); $this->users = new UserModel(); }

    public function loginForm(array $p): void
    {
        if (Session::isLoggedIn()) {
            Response::redirect('/', 303);
        }
        Response::noCache();
        $this->view('auth/login', ['title' => 'Вхід — ' . APP_NAME]);
    }
    public function login(array $p): void
    {
        Response::noCache();
        $this->verifyCsrf();
        $email    = trim($this->request->post('email', ''));
        $password = $this->request->post('password', '');
        $errors   = [];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Невірний формат email.';
        if (strlen($password) < 6) $errors[] = 'Пароль занадто короткий.';

        if (empty($errors)) {
            $user = $this->users->findByEmail($email);
            if ($user && $this->users->verifyPassword($password, $user['password'])) {
                session_regenerate_id(true);
                Session::set('user_id',   (int)$user['id']);
                Session::set('user_name', $user['name']);
                Session::set('user_role', $user['role']);
                Session::flash('success', 'Ласкаво просимо, ' . $user['name'] . '!');
                $redirectTo = Session::get('redirect_after_login', '');
                Session::remove('redirect_after_login');

                if (empty($redirectTo) || $redirectTo === '/login') {
                    $redirectTo = $user['role'] === 'admin' ? '/admin' : '/';
                }
                Response::redirect($redirectTo, 303);
            }
            $errors[] = 'Невірний email або пароль.';
        }
        Response::status(422);
        $this->view('auth/login', [
            'title'  => 'Вхід — ' . APP_NAME,
            'errors' => $errors,
            'old'    => ['email' => $this->h($email)],
        ]);
    }
    public function loginAjax(array $p): void
    {
        Response::noCache();
        $this->verifyCsrf();

        $email    = trim($this->request->post('email', ''));
        $password = $this->request->post('password', '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // 422 Unprocessable Entity
            $this->json(['success' => false, 'message' => 'Невірний формат email.'], 422);
        }
        if (strlen($password) < 6) {
            $this->json(['success' => false, 'message' => 'Пароль занадто короткий.'], 422);
        }

        $user = $this->users->findByEmail($email);
        if ($user && $this->users->verifyPassword($password, $user['password'])) {
            session_regenerate_id(true);
            Session::set('user_id',   (int)$user['id']);
            Session::set('user_name', $user['name']);
            Session::set('user_role', $user['role']);

            $redirectTo = Session::get('redirect_after_login', '');
            Session::remove('redirect_after_login');
            if (empty($redirectTo) || $redirectTo === '/login') {
                $redirectTo = $user['role'] === 'admin' ? '/admin' : '/';
            }
            $this->json([
                'success'  => true,
                'redirect' => APP_URL . $redirectTo,
                'role'     => $user['role'],
            ]);
        }
        $this->json(['success' => false, 'message' => 'Невірний email або пароль.'], 401);
    }
    public function registerForm(array $p): void
    {
        if (Session::isLoggedIn()) Response::redirect('/', 303);
        Response::noCache();
        $this->view('auth/register', ['title' => 'Реєстрація — ' . APP_NAME]);
    }

    public function register(array $p): void
    {
        Response::noCache();
        $this->verifyCsrf();
        $name     = trim($this->request->post('name', ''));
        $email    = trim($this->request->post('email', ''));
        $phone    = trim($this->request->post('phone', ''));
        $password = $this->request->post('password', '');
        $confirm  = $this->request->post('confirm', '');
        $errors   = [];

        if (mb_strlen($name) < 2)                        $errors[] = 'Ім\'я мінімум 2 символи.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))  $errors[] = 'Невірний формат email.';
        if (strlen($password) < 8)                       $errors[] = 'Пароль мінімум 8 символів.';
        if ($password !== $confirm)                      $errors[] = 'Паролі не збігаються.';
        if (empty($errors) && $this->users->emailExists($email)) $errors[] = 'Цей email вже зареєстровано.';

        if (empty($errors)) {
            $id = $this->users->create($name, $email, $password, 'user', $phone);
            session_regenerate_id(true);
            Session::set('user_id',   $id);
            Session::set('user_name', $name);
            Session::set('user_role', 'user');
            Session::flash('success', 'Реєстрацію завершено! Ласкаво просимо, ' . $name . '!');
            Response::redirect('/', 303);
        }
        Response::status(422);
        $this->view('auth/register', [
            'title'  => 'Реєстрація — ' . APP_NAME,
            'errors' => $errors,
            'old'    => ['name' => $this->h($name), 'email' => $this->h($email), 'phone' => $this->h($phone)],
        ]);
    }

    public function logout(array $p): void
    {
        Session::destroy();
        Response::redirect('/', 303);
    }
}
