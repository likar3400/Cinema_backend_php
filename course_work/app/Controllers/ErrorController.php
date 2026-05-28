<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Http\Response;

class ErrorController extends Controller
{
    public function notFound(array $p): void
    { Response::status(404); Response::noCache(); $this->view('error/404',['title'=>'404','message'=>'Сторінку не знайдено']); }
    public function serverError(array $p): void
    { Response::status(500); Response::noCache(); $this->view('error/500',['title'=>'500','message'=>'Помилка сервера']); }
}
