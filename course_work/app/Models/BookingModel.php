<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;
use RuntimeException;

class BookingModel extends Model
{
    public function book(int $userId, int $sessionId, int $seatId, float $price): array
    {
        $this->db->beginTransaction();
        try {
            $existing = $this->db->fetchOne(
                "SELECT id FROM bookings WHERE session_id=? AND seat_id=? AND status='confirmed' FOR UPDATE",
                [$sessionId, $seatId]
            );
            if ($existing) {
                $this->db->rollback();
                return ['success'=>false,'message'=>'Це місце вже заброньоване.'];
            }
            $code = strtoupper(substr(md5(uniqid((string)$userId, true)), 0, 8));
            $id   = $this->db->insert(
                'INSERT INTO bookings (user_id,session_id,seat_id,price_paid,ticket_code) VALUES (?,?,?,?,?)',
                [$userId, $sessionId, $seatId, $price, $code]
            );
            $this->db->commit();
            return ['success'=>true,'booking_id'=>$id,'ticket_code'=>$code,'price'=>$price];
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw new RuntimeException('Booking failed: '.$e->getMessage());
        }
    }

    public function find(int $id): array|false
    { return $this->db->fetchOne('SELECT * FROM bookings WHERE id=?',[$id]); }

    public function cancel(int $id, int $userId=0): bool
    {
        $sql = $userId > 0
            ? "UPDATE bookings SET status='cancelled' WHERE id=? AND user_id=?"
            : "UPDATE bookings SET status='cancelled' WHERE id=?";
        $params = $userId > 0 ? [$id,$userId] : [$id];
        return $this->db->execute($sql,$params) > 0;
    }

    public function getByUser(int $userId): array
    {
        return $this->db->fetchAll(
            'SELECT b.*,s.starts_at,s.format,s.language,m.title AS movie_title,
                    m.poster,h.name AS hall_name,st.row_num,st.col_num,st.type AS seat_type
             FROM bookings b
             JOIN sessions s ON s.id=b.session_id
             JOIN movies   m ON m.id=s.movie_id
             JOIN halls    h ON h.id=s.hall_id
             JOIN seats   st ON st.id=b.seat_id
             WHERE b.user_id=? ORDER BY b.created_at DESC', [$userId]
        );
    }

    public function getAll(int $page=1): array
    {
        $offset = ($page-1)*PER_PAGE;
        return $this->db->fetchAll(
            'SELECT b.*,u.name AS user_name,u.email AS user_email,
                    m.title AS movie_title,s.starts_at,h.name AS hall_name,
                    st.row_num,st.col_num,st.type AS seat_type
             FROM bookings b
             JOIN users    u  ON u.id=b.user_id
             JOIN sessions s  ON s.id=b.session_id
             JOIN movies   m  ON m.id=s.movie_id
             JOIN halls    h  ON h.id=s.hall_id
             JOIN seats   st  ON st.id=b.seat_id
             ORDER BY b.created_at DESC LIMIT ? OFFSET ?', [PER_PAGE,$offset]
        );
    }

    public function count(): int
    { return (int)$this->db->fetchColumn('SELECT COUNT(*) FROM bookings'); }

    public function stats(): array
    {
        return [
            'total'     => (int)$this->db->fetchColumn('SELECT COUNT(*) FROM bookings'),
            'confirmed' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM bookings WHERE status='confirmed'"),
            'cancelled' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM bookings WHERE status='cancelled'"),
            'revenue'   => (float)$this->db->fetchColumn("SELECT COALESCE(SUM(price_paid),0) FROM bookings WHERE status='confirmed'"),
        ];
    }

    public function monthlyStats(string $month = ''): array
    {
        if ($month === '') $month = date('Y-m');
        $start = $month.'-01';
        $end   = date('Y-m-t', strtotime($start));
        $halls = $this->db->fetchAll(
            "SELECT h.name AS hall_name, h.type AS hall_type,
                    COUNT(b.id)                                   AS total,
                    SUM(s.type='vip')                             AS vip,
                    SUM(s.type='standard')                        AS standard,
                    COALESCE(SUM(b.price_paid),0)                 AS revenue
             FROM bookings b
             JOIN sessions ss ON ss.id=b.session_id
             JOIN halls    h  ON h.id=ss.hall_id
             JOIN seats    s  ON s.id=b.seat_id
             WHERE b.status='confirmed' AND DATE(b.created_at) BETWEEN ? AND ?
             GROUP BY h.id ORDER BY total DESC",
            [$start,$end]
        );


        $topMovies = $this->db->fetchAll(
            "SELECT m.title, COUNT(b.id) AS cnt, COALESCE(SUM(b.price_paid),0) AS revenue
             FROM bookings b
             JOIN sessions s ON s.id=b.session_id
             JOIN movies   m ON m.id=s.movie_id
             WHERE b.status='confirmed' AND DATE(b.created_at) BETWEEN ? AND ?
             GROUP BY m.id ORDER BY cnt DESC LIMIT 10",
            [$start,$end]
        );

        $daily = $this->db->fetchAll(
            "SELECT DATE(created_at) AS day, COUNT(*) AS cnt, SUM(price_paid) AS rev
             FROM bookings WHERE status='confirmed' AND DATE(created_at) BETWEEN ? AND ?
             GROUP BY DATE(created_at) ORDER BY day",
            [$start,$end]
        );

        return [
            'total_seats'    => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM bookings WHERE status='confirmed' AND DATE(created_at) BETWEEN ? AND ?",
                [$start,$end]
            ),
            'vip_seats'      => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM bookings b JOIN seats s ON s.id=b.seat_id
                 WHERE b.status='confirmed' AND s.type='vip' AND DATE(b.created_at) BETWEEN ? AND ?",
                [$start,$end]
            ),
            'standard_seats' => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM bookings b JOIN seats s ON s.id=b.seat_id
                 WHERE b.status='confirmed' AND s.type='standard' AND DATE(b.created_at) BETWEEN ? AND ?",
                [$start,$end]
            ),
            'revenue'        => (float)$this->db->fetchColumn(
                "SELECT COALESCE(SUM(price_paid),0) FROM bookings
                 WHERE status='confirmed' AND DATE(created_at) BETWEEN ? AND ?",
                [$start,$end]
            ),
            'shop_revenue'   => (float)$this->db->fetchColumn(
                "SELECT COALESCE(SUM(total),0) FROM shop_orders
                 WHERE status='paid' AND DATE(created_at) BETWEEN ? AND ?",
                [$start,$end]
            ),
            'halls'          => $halls,
            'top_movies'     => $topMovies,
            'daily'          => $daily,
            'month'          => $month,
            'start'          => $start,
            'end'            => $end,
        ];
    }
}
