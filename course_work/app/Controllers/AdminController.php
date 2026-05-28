<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Controller;
use App\Core\PageBuffer;
use App\Core\Session;
use App\Core\Http\Response;
use App\Models\{MovieModel,SessionModel,BookingModel,UserModel,NewsModel,HallModel,ShopModel,ReviewModel};

class AdminController extends Controller
{
    private MovieModel   $movies;
    private SessionModel $sessions;
    private BookingModel $bookings;
    private UserModel    $users;
    private NewsModel    $news;
    private HallModel    $halls;
    private ShopModel    $shop;
    private ReviewModel  $reviews;

    public function __construct($r)
    {
        parent::__construct($r);
        $this->movies   = new MovieModel();
        $this->sessions = new SessionModel();
        $this->bookings = new BookingModel();
        $this->users    = new UserModel();
        $this->news     = new NewsModel();
        $this->halls    = new HallModel();
        $this->shop     = new ShopModel();
        $this->reviews  = new ReviewModel();
    }


    public function dashboard(array $p): void
    {
        $this->requireAdmin(); Response::noCache();
        $month = $this->request->get('month', date('Y-m'));
        $this->view('admin/dashboard',[
            'title'       => 'Адмін-панель — '.APP_NAME,
            'stats'       => $this->bookings->stats(),
            'monthly'     => $this->bookings->monthlyStats($month),
            'moviesCount' => $this->movies->count(false),
            'usersCount'  => $this->users->count(),
        ], 'partials/admin_layout');
    }

    public function stats(array $p): void
    {
        $this->requireAdmin(); Response::noCache();
        $month = $this->request->get('month', date('Y-m'));
        $this->view('admin/stats/index',[
            'title'   => 'Статистика — '.APP_NAME,
            'monthly' => $this->bookings->monthlyStats($month),
            'month'   => $month,
        ], 'partials/admin_layout');
    }

    public function statsJson(array $p): void
    {
        $this->requireAdmin();
        $month = $this->request->get('month', date('Y-m'));
        $this->json($this->bookings->monthlyStats($month));
    }

    public function movies(array $p): void
    {
        $this->requireAdmin();
        $page = max(1,(int)$this->request->get('page',1));
        $this->view('admin/movies/index',[
            'title'  => 'Фільми — Адмін',
            'movies' => $this->movies->getAll(false,$page),
            'page'   => $page,
            'pages'  => (int)ceil($this->movies->count(false)/PER_PAGE),
        ],'partials/admin_layout');
    }

    public function movieCreate(array $p): void
    {
        $this->requireAdmin();
        $this->view('admin/movies/form',[
            'title'      => 'Новий фільм',
            'movie'      => null,
            'categories' => $this->movies->getCategories(),
        ],'partials/admin_layout');
    }

    public function movieStore(array $p): void
    {
        $this->requireAdmin(); $this->verifyCsrf();
        $data = $this->request->all();
        if (empty(trim($data['title']??''))) {
            $this->view('admin/movies/form',['title'=>'Новий фільм','movie'=>null,'error'=>'Назва обов\'язкова','categories'=>$this->movies->getCategories()],'partials/admin_layout'); return;
        }
        $data['poster'] = $this->handleUpload('poster');
        $this->movies->create($data);
        PageBuffer::invalidate('movies'); PageBuffer::invalidate('home');
        Response::redirect('/admin/movies', 303);
    }

    public function movieEdit(array $p): void
    {
        $this->requireAdmin();
        $movie = $this->movies->find((int)($p['id']??0));
        if (!$movie) { Response::status(404); $this->view('error/404',['title'=>'404','message'=>'']); return; }
        $this->view('admin/movies/form',[
            'title'      => 'Ред. фільму',
            'movie'      => $movie,
            'categories' => $this->movies->getCategories(),
        ],'partials/admin_layout');
    }

    public function movieUpdate(array $p): void
    {
        $this->requireAdmin(); $this->verifyCsrf();
        $id = (int)($p['id']??0); $data = $this->request->all();
        $poster = $this->handleUpload('poster');
        if ($poster) $data['poster'] = $poster;
        $this->movies->update($id,$data);
        PageBuffer::invalidate("movie_d{$id}"); PageBuffer::invalidate('movies');
        Response::redirect('/admin/movies', 303);
    }

    public function movieDelete(array $p): void
    {
        $this->requireAdmin();
        $id = (int)($p['id']??0);
        if ($this->request->isAjax()) {
            $data = $this->request->json();
            if (!hash_equals(Session::csrfToken(),$data[CSRF_TOKEN_NAME]??'')) $this->json(['success'=>false],403);
            $ok = $this->movies->delete($id);
            if ($ok) PageBuffer::invalidate('movies');
            $this->json(['success'=>$ok]);
        }
        $this->movies->delete($id); PageBuffer::invalidate('movies');
        Response::redirect('/admin/movies', 303);
    }

    public function sessions(array $p): void
    {
        $this->requireAdmin();
        $page = max(1,(int)$this->request->get('page',1));
        $this->view('admin/sessions/index',[
            'title'    => 'Сеанси — Адмін',
            'sessions' => $this->sessions->getAll($page),
            'page'     => $page,
            'pages'    => (int)ceil($this->sessions->count()/PER_PAGE),
        ],'partials/admin_layout');
    }

    public function sessionCreate(array $p): void
    {
        $this->requireAdmin();
        $this->view('admin/sessions/form',[
            'title'   => 'Новий сеанс','session'=>null,
            'movies'  => $this->movies->getAll(false),
            'halls'   => $this->halls->getAll(),
        ],'partials/admin_layout');
    }

    public function sessionStore(array $p): void
    {
        $this->requireAdmin(); $this->verifyCsrf();
        $this->sessions->create($this->request->all());
        Response::redirect('/admin/sessions', 303);
    }

    public function sessionEdit(array $p): void
    {
        $this->requireAdmin();
        $session = $this->sessions->find((int)($p['id']??0));
        if (!$session) { Response::status(404); $this->view('error/404',['title'=>'404','message'=>'']); return; }
        $this->view('admin/sessions/form',[
            'title'   => 'Ред. сеансу','session'=>$session,
            'movies'  => $this->movies->getAll(false),
            'halls'   => $this->halls->getAll(),
        ],'partials/admin_layout');
    }

    public function sessionUpdate(array $p): void
    {
        $this->requireAdmin(); $this->verifyCsrf();
        $this->sessions->update((int)($p['id']??0),$this->request->all());
        Response::redirect('/admin/sessions', 303);
    }

    public function sessionDelete(array $p): void
    {
        $this->requireAdmin(); $id=(int)($p['id']??0);
        if ($this->request->isAjax()) {
            $data=$this->request->json();
            if (!hash_equals(Session::csrfToken(),$data[CSRF_TOKEN_NAME]??'')) $this->json(['success'=>false],403);
            $this->json(['success'=>$this->sessions->delete($id)]);
        }
        $this->sessions->delete($id);
        Response::redirect('/admin/sessions', 303);
    }

    public function bookings(array $p): void
    {
        $this->requireAdmin();
        $page = max(1,(int)$this->request->get('page',1));
        $this->view('admin/bookings/index',[
            'title'    => 'Бронювання — Адмін',
            'bookings' => $this->bookings->getAll($page),
            'page'     => $page,
            'pages'    => (int)ceil($this->bookings->count()/PER_PAGE),
        ],'partials/admin_layout');
    }

    public function bookingCancel(array $p): void
    {
        $this->requireAdmin();
        $id = (int)($p['id']??0);
        if ($this->request->isAjax()) {
            $data=$this->request->json();
            if (!hash_equals(Session::csrfToken(),$data[CSRF_TOKEN_NAME]??'')) $this->json(['success'=>false],403);
            $this->json(['success'=>$this->bookings->cancel($id)]);
        }
        $this->bookings->cancel($id);
        Response::redirect('/admin/bookings', 303);
    }

    public function users(array $p): void
    {
        $this->requireAdmin();
        $page=max(1,(int)$this->request->get('page',1));
        $this->view('admin/users/index',[
            'title' => 'Користувачі — Адмін',
            'users' => $this->users->getAll($page),
            'page'  => $page,
            'pages' => (int)ceil($this->users->count()/PER_PAGE),
        ],'partials/admin_layout');
    }

    public function userCreate(array $p): void
    { $this->requireAdmin(); $this->view('admin/users/create',['title'=>'Новий юзер'],'partials/admin_layout'); }

    public function userStore(array $p): void
    {
        $this->requireAdmin(); $this->verifyCsrf();
        $d = $this->request->all();
        // 409 — дублікат email
        if ($this->users->emailExists($d['email']??'')) {
            Response::status(409);
            $this->view('admin/users/create',['title'=>'Новий юзер','error'=>'Email вже існує'],'partials/admin_layout'); return;
        }
        $this->users->create($d['name']??'',$d['email']??'',$d['password']??'',$d['role']??'user',$d['phone']??'');
        Response::redirect('/admin/users', 303);
    }

    public function userEdit(array $p): void
    {
        $this->requireAdmin();
        $user=$this->users->findById((int)($p['id']??0));
        if (!$user) { Response::status(404); $this->view('error/404',['title'=>'404','message'=>'']); return; }
        $this->view('admin/users/form',['title'=>'Ред. юзера','user'=>$user],'partials/admin_layout');
    }

    public function userUpdate(array $p): void
    {
        $this->requireAdmin(); $this->verifyCsrf();
        $id = (int)($p['id']??0); $d = $this->request->all();
        // 409 — дублікат email
        if ($this->users->emailExists($d['email']??'',$id)) {
            Response::status(409);
            $user = $this->users->findById($id);
            $this->view('admin/users/form',['title'=>'Ред. юзера','user'=>$user,'error'=>'Email вже використовується'],'partials/admin_layout'); return;
        }
        $this->users->update($id,$d);
        Response::redirect('/admin/users', 303);
    }

    public function userDelete(array $p): void
    {
        $this->requireAdmin(); $id=(int)($p['id']??0);
        if ($this->request->isAjax()) {
            $data=$this->request->json();
            if (!hash_equals(Session::csrfToken(),$data[CSRF_TOKEN_NAME]??'')) $this->json(['success'=>false],403);
            $this->json(['success'=>$this->users->delete($id)]);
        }
        $this->users->delete($id);
        Response::redirect('/admin/users', 303);
    }

    public function news(array $p): void
    {
        $this->requireAdmin();
        $page=max(1,(int)$this->request->get('page',1));
        $this->view('admin/news/index',[
            'title'  => 'Новини — Адмін',
            'items'  => $this->news->getAll(false,$page),
            'page'   => $page,
            'pages'  => (int)ceil($this->news->count()/PER_PAGE),
        ],'partials/admin_layout');
    }

    public function newsCreate(array $p): void
    { $this->requireAdmin(); $this->view('admin/news/form',['title'=>'Нова новина','item'=>null],'partials/admin_layout'); }

    public function newsStore(array $p): void
    {
        $this->requireAdmin(); $this->verifyCsrf();
        $this->news->create($this->request->all());
        PageBuffer::invalidate('news');
        Response::redirect('/admin/news', 303);
    }

    public function newsEdit(array $p): void
    {
        $this->requireAdmin();
        $item=$this->news->find((int)($p['id']??0));
        if (!$item) { Response::status(404); $this->view('error/404',['title'=>'404','message'=>'']); return; }
        $this->view('admin/news/form',['title'=>'Ред. новини','item'=>$item],'partials/admin_layout');
    }

    public function newsUpdate(array $p): void
    {
        $this->requireAdmin(); $this->verifyCsrf();
        $this->news->update((int)($p['id']??0),$this->request->all());
        PageBuffer::invalidate('news');
        Response::redirect('/admin/news', 303);
    }

    public function newsDelete(array $p): void
    {
        $this->requireAdmin(); $id=(int)($p['id']??0);
        if ($this->request->isAjax()) {
            $data=$this->request->json();
            if (!hash_equals(Session::csrfToken(),$data[CSRF_TOKEN_NAME]??'')) $this->json(['success'=>false],403);
            $this->json(['success'=>$this->news->delete($id)]);
        }
        $this->news->delete($id); PageBuffer::invalidate('news');
        Response::redirect('/admin/news', 303);
    }

    public function halls(array $p): void
    { $this->requireAdmin(); $this->view('admin/halls/index',['title'=>'Зали — Адмін','halls'=>$this->halls->getAll()],'partials/admin_layout'); }

    public function hallCreate(array $p): void
    { $this->requireAdmin(); $this->view('admin/halls/form',['title'=>'Новий зал','hall'=>null],'partials/admin_layout'); }

    public function hallStore(array $p): void
    { $this->requireAdmin(); $this->verifyCsrf(); $this->halls->create($this->request->all()); Response::redirect('/admin/halls', 303); }

    public function hallEdit(array $p): void
    {
        $this->requireAdmin();
        $hall=$this->halls->find((int)($p['id']??0));
        if (!$hall) { Response::status(404); $this->view('error/404',['title'=>'404','message'=>'']); return; }
        $this->view('admin/halls/form',['title'=>'Ред. залу','hall'=>$hall],'partials/admin_layout');
    }

    public function hallUpdate(array $p): void
    { $this->requireAdmin(); $this->verifyCsrf(); $this->halls->update((int)($p['id']??0),$this->request->all()); Response::redirect('/admin/halls', 303); }

    public function hallDelete(array $p): void
    {
        $this->requireAdmin(); $id=(int)($p['id']??0);
        if ($this->request->isAjax()) {
            $data=$this->request->json();
            if (!hash_equals(Session::csrfToken(),$data[CSRF_TOKEN_NAME]??'')) $this->json(['success'=>false],403);
            $this->json(['success'=>$this->halls->delete($id)]);
        }
        $this->halls->delete($id);
        Response::redirect('/admin/halls', 303);
    }
    public function shopItems(array $p): void
    {
        $this->requireAdmin();
        $page=max(1,(int)$this->request->get('page',1));
        $this->view('admin/shop/index',[
            'title' => 'Магазин — Адмін',
            'items' => $this->shop->getAllItems($page),
            'page'  => $page,
            'pages' => (int)ceil($this->shop->countItems()/PER_PAGE),
        ],'partials/admin_layout');
    }

    public function shopItemCreate(array $p): void
    { $this->requireAdmin(); $this->view('admin/shop/form',['title'=>'Новий товар','item'=>null],'partials/admin_layout'); }

    public function shopItemStore(array $p): void
    {
        $this->requireAdmin(); $this->verifyCsrf();
        $data = $this->request->all();
        $data['image'] = $this->handleShopUpload('image');
        $this->shop->createItem($data);
        PageBuffer::invalidate('shop');
        Response::redirect('/admin/shop', 303);
    }

    public function shopItemEdit(array $p): void
    {
        $this->requireAdmin();
        $item=$this->shop->findItem((int)($p['id']??0));
        if (!$item) { Response::status(404); $this->view('error/404',['title'=>'404','message'=>'']); return; }
        $this->view('admin/shop/form',['title'=>'Ред. товару','item'=>$item],'partials/admin_layout');
    }

    public function shopItemUpdate(array $p): void
    {
        $this->requireAdmin(); $this->verifyCsrf();
        $data = $this->request->all();
        $newImage = $this->handleShopUpload('image');
        if ($newImage) $data['image'] = $newImage;
        $this->shop->updateItem((int)($p['id']??0),$data);
        PageBuffer::invalidate('shop');
        Response::redirect('/admin/shop', 303);
    }

    public function shopItemDelete(array $p): void
    {
        $this->requireAdmin(); $id=(int)($p['id']??0);
        if ($this->request->isAjax()) {
            $data=$this->request->json();
            if (!hash_equals(Session::csrfToken(),$data[CSRF_TOKEN_NAME]??'')) $this->json(['success'=>false],403);
            $this->json(['success'=>$this->shop->deleteItem($id)]);
        }
        $this->shop->deleteItem($id);
        Response::redirect('/admin/shop', 303);
    }
    public function reviews(array $p): void
    {
        $this->requireAdmin(); Response::noCache();
        $page = max(1,(int)$this->request->get('page',1));
        $this->view('admin/reviews/index',[
            'title'   => 'Відгуки — Адмін',
            'reviews' => $this->reviews->getAll($page),
            'page'    => $page,
            'pages'   => (int)ceil($this->reviews->count()/PER_PAGE),
        ],'partials/admin_layout');
    }

    public function reviewDelete(array $p): void
    {
        $this->requireAdmin();
        $id = (int)($p['id']??0);
        if ($this->request->isAjax()) {
            $data=$this->request->json();
            if (!hash_equals(Session::csrfToken(),$data[CSRF_TOKEN_NAME]??'')) $this->json(['success'=>false],403);
            $review = $this->reviews->find($id);
            if ($review) PageBuffer::invalidate("movie_d{$review['movie_id']}");
            $this->json(['success'=>$this->reviews->delete($id)]);
        }
        $this->reviews->delete($id);
        Response::redirect('/admin/reviews', 303);
    }

    public function categories(array $p): void
    {
        $this->requireAdmin();
        $this->view('admin/categories/index',[
            'title'      => 'Категорії — Адмін',
            'categories' => $this->movies->getCategories(),
        ],'partials/admin_layout');
    }

    public function categoryStore(array $p): void
    {
        $this->requireAdmin(); $this->verifyCsrf();
        $name = trim($this->request->post('name',''));
        if (empty($name)) { Response::redirect('/admin/categories', 303); }
        if ($this->movies->categoryExists($name)) {
            Response::status(409);
            Session::flash('error', 'Категорія вже існує.');
            Response::redirect('/admin/categories', 303);
        }
        $this->movies->createCategory($name);
        Session::flash('success', 'Категорію додано.');
        Response::redirect('/admin/categories', 303);
    }

    public function categoryDelete(array $p): void
    {
        $this->requireAdmin();
        $id = (int)($p['id']??0);
        if ($this->request->isAjax()) {
            $data=$this->request->json();
            if (!hash_equals(Session::csrfToken(),$data[CSRF_TOKEN_NAME]??'')) $this->json(['success'=>false],403);
            $this->json(['success'=>$this->movies->deleteCategory($id)]);
        }
        $this->movies->deleteCategory($id);
        Response::redirect('/admin/categories', 303);
    }

    private function handleUpload(string $field): ?string
    {
        if (empty($_FILES[$field]['tmp_name'])) return null;
        $file=$_FILES[$field];
        if (!in_array($file['type'],['image/jpeg','image/png','image/webp'],true)) return null;
        $ext=pathinfo($file['name'],PATHINFO_EXTENSION);
        $name=uniqid('poster_',true).'.'.$ext;
        $dir=ROOT_PATH.'/public/images/posters/';
        if (!is_dir($dir)) mkdir($dir,0755,true);
        return move_uploaded_file($file['tmp_name'],$dir.$name) ? '/images/posters/'.$name : null;
    }

    private function handleShopUpload(string $field): ?string
    {
        if (empty($_FILES[$field]['tmp_name'])) return null;
        $file=$_FILES[$field];
        if (!in_array($file['type'],['image/jpeg','image/png','image/webp'],true)) return null;
        $ext=pathinfo($file['name'],PATHINFO_EXTENSION);
        $name=uniqid('shop_',true).'.'.$ext;
        $dir=ROOT_PATH.'/public/images/shop/';
        if (!is_dir($dir)) mkdir($dir,0755,true);
        return move_uploaded_file($file['tmp_name'],$dir.$name) ? '/images/shop/'.$name : null;
    }
}
