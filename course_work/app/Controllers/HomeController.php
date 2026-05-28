<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Controller;
use App\Core\PageBuffer;
use App\Core\Http\Response;
use App\Models\{MovieModel,NewsModel,SessionModel};

class HomeController extends Controller
{
    public function index(array $p): void
    {
        $cacheKey = 'home_index';
        if ($cached = PageBuffer::check($cacheKey)) {
            Response::cacheHeaders(md5($cached)); echo $cached; return;
        }
        $this->view('movies/home',[
            'title'    => APP_NAME.' — Онлайн-продаж квитків',
            'movies'   => (new MovieModel())->getNowShowing(),
            'news'     => (new NewsModel())->getAll(true,1),
            'sessions' => (new SessionModel())->getUpcoming(0,date('Y-m-d')),
        ]);
    }
}
