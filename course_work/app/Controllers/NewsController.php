<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Controller;
use App\Core\PageBuffer;
use App\Core\Http\Response;
use App\Models\NewsModel;

class NewsController extends Controller
{
    private NewsModel $news;
    public function __construct($r) { parent::__construct($r); $this->news=new NewsModel(); }

    public function index(array $p): void
    {
        $page = max(1,(int)$this->request->get('page',1));
        $key  = "news_p{$page}";
        if ($cached = PageBuffer::check($key)) { Response::cacheHeaders(md5($cached)); echo $cached; return; }
        $this->view('movies/news',[
            'title'=>'Новини — '.APP_NAME,
            'items'=>$this->news->getAll(true,$page),
            'page'=>$page,'pages'=>(int)ceil($this->news->count(true)/PER_PAGE),
        ]);
    }

    public function show(array $p): void
    {
        $id   = (int)($p['id']??0);
        $item = $this->news->find($id);
        if (!$item||!$item['is_active']) {
            Response::status(404); $this->view('error/404',['title'=>'404','message'=>'Новину не знайдено']); return;
        }
        $key = "news_d{$id}";
        if ($cached = PageBuffer::check($key)) { Response::cacheHeaders(md5($cached)); echo $cached; return; }
        $this->view('movies/news_show',['title'=>$item['title'].' — '.APP_NAME,'item'=>$item]);
    }
}
