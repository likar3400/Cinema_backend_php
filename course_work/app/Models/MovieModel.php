<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;

class MovieModel extends Model
{
    public function getAll(bool $activeOnly=true, int $page=1): array
    {
        $where  = $activeOnly ? 'WHERE is_active=1' : '';
        $offset = ($page-1)*PER_PAGE;
        return $this->db->fetchAll(
            "SELECT * FROM movies {$where} ORDER BY release_date DESC LIMIT ? OFFSET ?",
            [PER_PAGE, $offset]
        );
    }

    public function count(bool $activeOnly=true): int
    {
        $w = $activeOnly ? 'WHERE is_active=1' : '';
        return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM movies {$w}");
    }

    public function find(int $id): array|false
    { return $this->db->fetchOne('SELECT * FROM movies WHERE id=?',[$id]); }

    public function getNowShowing(): array
    {
        return $this->db->fetchAll(
            'SELECT DISTINCT m.* FROM movies m
             JOIN sessions s ON s.movie_id=m.id
             WHERE m.is_active=1 AND s.starts_at>=NOW() AND s.is_active=1
             ORDER BY m.title'
        );
    }

    public function create(array $d): int
    {
        return $this->db->insert(
            'INSERT INTO movies (title,description,genre,category_id,duration,rating,age_rating,poster,trailer_url,release_date,is_active)
             VALUES (:title,:description,:genre,:category_id,:duration,:rating,:age_rating,:poster,:trailer_url,:release_date,:is_active)',
            $this->fields($d)
        );
    }

    public function update(int $id, array $d): bool
    {
        $f = $this->fields($d); $f['id'] = $id;
        return $this->db->execute(
            'UPDATE movies SET title=:title,description=:description,genre=:genre,category_id=:category_id,
             duration=:duration,rating=:rating,age_rating=:age_rating,poster=:poster,trailer_url=:trailer_url,
             release_date=:release_date,is_active=:is_active WHERE id=:id', $f
        ) > 0;
    }

    public function delete(int $id): bool
    { return $this->db->execute('DELETE FROM movies WHERE id=?',[$id]) > 0; }

    public function getCategories(): array
    {
        return $this->db->fetchAll('SELECT * FROM movie_categories ORDER BY name');
    }

    public function categoryExists(string $name): bool
    {
        return (int)$this->db->fetchColumn(
            'SELECT COUNT(*) FROM movie_categories WHERE name=?', [trim($name)]
        ) > 0;
    }

    public function createCategory(string $name): int
    {
        $slug = mb_strtolower(preg_replace('/\s+/', '-', trim($name)));
        return $this->db->insert(
            'INSERT INTO movie_categories (name, slug) VALUES (?, ?)',
            [trim($name), $slug]
        );
    }

    public function deleteCategory(int $id): bool
    {
        $this->db->execute('UPDATE movies SET category_id=NULL WHERE category_id=?', [$id]);
        return $this->db->execute('DELETE FROM movie_categories WHERE id=?', [$id]) > 0;
    }
    public function updateRating(int $id, float $rating): void
    {
        $this->db->execute(
            'UPDATE movies SET rating = ? WHERE id = ?',
            [$rating, $id]
        );
    }
    private function fields(array $d): array
    {
        return [
            'title'       => $d['title']        ?? '',
            'description' => $d['description']  ?? '',
            'genre'       => $d['genre']         ?? '',
            'category_id' => !empty($d['category_id']) ? (int)$d['category_id'] : null,
            'duration'    => (int)($d['duration'] ?? 90),
            'rating'      => !empty($d['rating']) ? (float)$d['rating'] : null,
            'age_rating'  => $d['age_rating']    ?? '0+',
            'poster'      => $d['poster']         ?? null,
            'trailer_url' => $d['trailer_url']   ?? null,
            'release_date'=> !empty($d['release_date']) ? $d['release_date'] : null,
            'is_active'   => isset($d['is_active']) ? (int)$d['is_active'] : 1,
        ];
    }
}
