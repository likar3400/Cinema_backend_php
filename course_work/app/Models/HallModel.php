<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;

class HallModel extends Model
{
    public function getAll(): array { return $this->db->fetchAll('SELECT * FROM halls ORDER BY name'); }
    public function find(int $id): array|false { return $this->db->fetchOne('SELECT * FROM halls WHERE id=?',[$id]); }

    public function create(array $d): int
    {
        $id = $this->db->insert(
            'INSERT INTO halls (name,row_count,col_count,type) VALUES (?,?,?,?)',
            [$d['name'],(int)$d['row_count'],(int)$d['col_count'],$d['type']]
        );
        $this->fillSeats($id,(int)$d['row_count'],(int)$d['col_count']);
        return $id;
    }

    public function update(int $id, array $d): bool
    {
        return $this->db->execute(
            'UPDATE halls SET name=?,row_count=?,col_count=?,type=?,is_active=? WHERE id=?',
            [$d['name'],(int)$d['row_count'],(int)$d['col_count'],$d['type'],(int)($d['is_active']??1),$id]
        ) > 0;
    }

    public function delete(int $id): bool { return $this->db->execute('DELETE FROM halls WHERE id=?',[$id]) > 0; }

    private function fillSeats(int $hallId, int $rows, int $cols): void
    {
        for ($r=1;$r<=$rows;$r++) for ($c=1;$c<=$cols;$c++) {
            $type = $r<=2 ? 'vip' : (($r===$rows&&($c===1||$c===$cols)) ? 'disabled' : 'standard');
            $this->db->execute('INSERT IGNORE INTO seats (hall_id,row_num,col_num,type) VALUES (?,?,?,?)',[$hallId,$r,$c,$type]);
        }
    }
}
