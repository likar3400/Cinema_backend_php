<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Http\Response;
use App\Core\PageBuffer;
use App\Models\ReviewModel;
use App\Models\MovieModel;

class ReviewController extends Controller
{
    private ReviewModel $reviews;
    private MovieModel  $movies;

    public function __construct($r)
    {
        parent::__construct($r);
        $this->reviews = new ReviewModel();
        $this->movies  = new MovieModel();
    }

    public function add(array $p): void
    {
        $this->requireAuth();
        Response::noCache();

        $data    = $this->request->json();
        $movieId = (int)($data['movie_id'] ?? 0);
        $rating  = (int)($data['rating']   ?? 0);
        $body    = trim((string)($data['body'] ?? ''));

        if (!hash_equals(Session::csrfToken(), $data[CSRF_TOKEN_NAME] ?? $data['_csrf'] ?? ''))
            $this->json(['success' => false, 'message' => 'CSRF error'], 403);
        if ($rating < 1 || $rating > 10)
            $this->json(['success' => false, 'message' => 'Рейтинг від 1 до 10.'], 422);
        if (mb_strlen($body) < 10)
            $this->json(['success' => false, 'message' => 'Відгук мінімум 10 символів.'], 422);

        $movie = $this->movies->find($movieId);
        if (!$movie)
            $this->json(['success' => false, 'message' => 'Фільм не знайдено.'], 404);

        if ($this->reviews->userAlreadyReviewed(Session::userId(), $movieId))
            $this->json(['success' => false, 'message' => 'Ви вже залишили відгук на цей фільм.'], 409);

        $id  = $this->reviews->create(Session::userId(), $movieId, $rating, $body);
        $avg = $this->reviews->avgRating($movieId);

        $this->movies->updateRating($movieId, $avg);

        PageBuffer::invalidate("movie_d{$movieId}");

        $this->json([
            'success'    => true,
            'review_id'  => $id,
            'user_name'  => Session::get('user_name'),
            'rating'     => $rating,
            'body'       => htmlspecialchars($body, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            'avg_rating' => $avg,
            'created_at' => date('d.m.Y H:i'),
        ], 201);
    }

    public function delete(array $p): void
    {
        $this->requireAdmin();
        Response::noCache();

        $data = $this->request->json();
        if (!hash_equals(Session::csrfToken(), $data[CSRF_TOKEN_NAME] ?? $data['_csrf'] ?? ''))
            $this->json(['success' => false], 403);

        $id     = (int)($p['id'] ?? 0);
        $review = $this->reviews->find($id);

        if ($review) {
            PageBuffer::invalidate("movie_d{$review['movie_id']}");
            $this->reviews->delete($id);
            $avg = $this->reviews->avgRating($review['movie_id']);
            $this->movies->updateRating($review['movie_id'], $avg);
            $this->json(['success' => true]);
        }

        $this->json(['success' => false]);
    }
}