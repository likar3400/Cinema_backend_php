<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;

class SessionModel extends Model
{
    public function getUpcoming(int $movieId = 0, string $date = ''): array
    {
        $where  = ['s.is_active = 1'];
        $params = [];

        if ($movieId > 0) { $where[] = 's.movie_id = ?'; $params[] = $movieId; }

        if ($date) {
            $where[] = 'DATE(s.starts_at) = ?';
            $params[] = $date;
        } else {
            $where[] = 's.starts_at >= NOW()';
        }

        $sql = "SELECT s.*, m.title AS movie_title, m.poster, m.age_rating,
                   h.name AS hall_name
            FROM sessions s
            JOIN movies m ON m.id = s.movie_id
            JOIN halls  h ON h.id = s.hall_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY s.starts_at
            LIMIT 20";

        return $this->db->fetchAll($sql, $params);
    }

    public function getAll(int $page=1): array
    {
        $offset = ($page-1)*PER_PAGE;
        return $this->db->fetchAll(
            'SELECT s.*,m.title AS movie_title,h.name AS hall_name
             FROM sessions s JOIN movies m ON m.id=s.movie_id JOIN halls h ON h.id=s.hall_id
             ORDER BY s.starts_at DESC LIMIT ? OFFSET ?', [PER_PAGE,$offset]
        );
    }

    public function count(): int { return (int)$this->db->fetchColumn('SELECT COUNT(*) FROM sessions'); }

    public function find(int $id): array|false
    {
        return $this->db->fetchOne(
            'SELECT s.*,m.title AS movie_title,h.name AS hall_name,h.row_count,h.col_count
             FROM sessions s
             JOIN movies m ON m.id=s.movie_id
             JOIN halls  h ON h.id=s.hall_id
             WHERE s.id=?', [$id]
        );
    }

    public function create(array $d): int
    {
        return $this->db->insert(
            'INSERT INTO sessions (movie_id,hall_id,starts_at,ends_at,price,price_vip,language,format)
             VALUES (:movie_id,:hall_id,:starts_at,:ends_at,:price,:price_vip,:language,:format)',
            $this->fields($d)
        );
    }

    public function update(int $id, array $d): bool
    {
        $f = $this->fields($d); $f['id'] = $id;
        return $this->db->execute(
            'UPDATE sessions SET movie_id=:movie_id,hall_id=:hall_id,starts_at=:starts_at,
             ends_at=:ends_at,price=:price,price_vip=:price_vip,language=:language,format=:format
             WHERE id=:id', $f
        ) > 0;
    }

    public function delete(int $id): bool
    { return $this->db->execute('DELETE FROM sessions WHERE id=?',[$id]) > 0; }

    public function getBookedSeatIds(int $sessionId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT seat_id FROM bookings WHERE session_id=? AND status='confirmed'", [$sessionId]
        );
        return array_column($rows, 'seat_id');
    }

    public function getSeats(int $hallId): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM seats WHERE hall_id=? ORDER BY row_num,col_num', [$hallId]
        );
    }

    private function fields(array $d): array
    {
        return [
            'movie_id'  => (int)($d['movie_id']  ?? 0),
            'hall_id'   => (int)($d['hall_id']   ?? 0),
            'starts_at' => $d['starts_at'] ?? '',
            'ends_at'   => $d['ends_at']   ?? '',
            'price'     => (float)($d['price']     ?? 0),
            'price_vip' => (float)($d['price_vip'] ?? 0),
            'language'  => $d['language'] ?? 'uk',
            'format'    => $d['format']   ?? '2D',
        ];
    }
}
