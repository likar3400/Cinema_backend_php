<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Controller;
use App\Core\PageBuffer;
use App\Core\Session;
use App\Core\Http\Response;
use App\Models\{MovieModel,SessionModel,ReviewModel};

class MovieController extends Controller
{
    private MovieModel   $movies;
    private SessionModel $sessions;
    private ReviewModel  $reviews;

    public function __construct($r)
    {
        parent::__construct($r);
        $this->movies   = new MovieModel();
        $this->sessions = new SessionModel();
        $this->reviews  = new ReviewModel();
    }

    public function index(array $p): void
    {
        $page = max(1,(int)$this->request->get('page',1));
        $key  = "movies_p{$page}";
        if ($cached = PageBuffer::check($key)) { Response::cacheHeaders(md5($cached)); echo $cached; return; }
        // 200 OK
        $this->view('movies/index',[
            'title'  => 'Афіша — '.APP_NAME,
            'movies' => $this->movies->getAll(true,$page),
            'page'   => $page,
            'pages'  => (int)ceil($this->movies->count(true)/PER_PAGE),
        ]);
    }

    public function show(array $p): void
    {
        $id    = (int)($p['id']??0);
        $movie = $this->movies->find($id);
        if (!$movie || !$movie['is_active']) {
            // 404 — фільм видалено → редірект на список
            Response::status(404);
            $this->view('error/404',['title'=>'404','message'=>'Фільм не знайдено']);
            return;
        }
        $key = "movie_d{$id}";
        if ($cached = PageBuffer::check($key)) { Response::cacheHeaders(md5($cached)); echo $cached; return; }

        $alreadyReviewed = Session::isLoggedIn()
            ? $this->reviews->userAlreadyReviewed(Session::userId(), $id)
            : false;

        // 200 OK
        $this->view('movies/show',[
            'title'           => $movie['title'].' — '.APP_NAME,
            'movie'           => $movie,
            'sessions'        => $this->sessions->getUpcoming($id),
            'reviews'         => $this->reviews->getByMovie($id),
            'alreadyReviewed' => $alreadyReviewed,
        ]);
    }

    public function schedule(array $p): void
    {
        $date    = $this->request->get('date', date('Y-m-d'));
        $movieId = (int)$this->request->get('movie', 0);
        // 200 OK
        $this->view('movies/schedule',[
            'title'    => 'Розклад — '.APP_NAME,
            'sessions' => $this->sessions->getUpcoming($movieId, $date),
            'movies'   => $this->movies->getAll(true, 1), // всі активні для фільтру
            'date'     => $date,
            'movieId'  => $movieId,
        ]);
    }

    public function apiSessions(array $p): void
    {
        $data = $this->sessions->getUpcoming(
            (int)$this->request->get('movie_id', 0),
            $this->request->get('date', date('Y-m-d'))
        );
        // 200
        $this->json(['sessions' => $data]);
    }
}
