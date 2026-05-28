<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;

class ReviewModel extends Model
{

    public function getByMovie(int $movieId): array
    {
        return $this->db->fetchAll(
            'SELECT r.id, r.rating, r.comment, r.created_at, r.is_active, u.name AS user_name
         FROM reviews r
         JOIN users u ON u.id = r.user_id
         WHERE r.movie_id = ? AND r.is_active = 1
         ORDER BY r.created_at DESC',
            [$movieId]
        );
    }

    public function getAll(int $page = 1): array
    {
        $offset = ($page - 1) * PER_PAGE;
        return $this->db->fetchAll(
            'SELECT r.*, u.name AS user_name, m.title AS movie_title
             FROM reviews r
             JOIN users u  ON u.id  = r.user_id
             JOIN movies m ON m.id  = r.movie_id
             ORDER BY r.created_at DESC LIMIT ? OFFSET ?',
            [PER_PAGE, $offset]
        );
    }

    public function count(): int
    { return (int)$this->db->fetchColumn('SELECT COUNT(*) FROM reviews'); }

    public function userAlreadyReviewed(int $userId, int $movieId): bool
    {
        return (int)$this->db->fetchColumn(
            'SELECT COUNT(*) FROM reviews WHERE user_id=? AND movie_id=?',
            [$userId, $movieId]
        ) > 0;
    }

    public function avgRating(int $movieId): float
    {
        return round((float)$this->db->fetchColumn(
            'SELECT COALESCE(AVG(rating),0) FROM reviews WHERE movie_id=? AND is_active=1',
            [$movieId]
        ), 1);
    }

    public function create(int $userId, int $movieId, int $rating, string $body): int
    {
        return $this->db->insert(
            'INSERT INTO reviews (user_id, movie_id, rating, comment) VALUES (?,?,?,?)',
            [$userId, $movieId, $rating, $body]
        );
    }

    public function delete(int $id): bool
    { return $this->db->execute('DELETE FROM reviews WHERE id=?', [$id]) > 0; }

    public function setActive(int $id, bool $active): bool
    {
        return $this->db->execute(
            'UPDATE reviews SET is_active=? WHERE id=?',
            [(int)$active, $id]
        ) > 0;
    }

    public function find(int $id): array|false
    { return $this->db->fetchOne('SELECT * FROM reviews WHERE id=?', [$id]); }
}
