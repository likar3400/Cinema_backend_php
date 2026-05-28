<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;

class NewsModel extends Model
{
    public function getAll(bool $active=true, int $page=1): array
    {
        $w = $active ? 'WHERE is_active=1' : '';
        $offset = ($page-1)*PER_PAGE;
        return $this->db->fetchAll("SELECT * FROM news {$w} ORDER BY created_at DESC LIMIT ? OFFSET ?", [PER_PAGE,$offset]);
    }
    public function count(bool $active=true): int
    { $w=$active?'WHERE is_active=1':''; return (int)$this->db->fetchColumn("SELECT COUNT(*) FROM news {$w}"); }
    public function find(int $id): array|false { return $this->db->fetchOne('SELECT * FROM news WHERE id=?',[$id]); }
    public function create(array $d): int
    { return $this->db->insert('INSERT INTO news (title,body,is_active) VALUES (?,?,?)',[$d['title'],$d['body'],(int)($d['is_active']??1)]); }
    public function update(int $id, array $d): bool
    { return $this->db->execute('UPDATE news SET title=?,body=?,is_active=? WHERE id=?',[$d['title'],$d['body'],(int)($d['is_active']??1),$id]) > 0; }
    public function delete(int $id): bool
    { return $this->db->execute('DELETE FROM news WHERE id=?',[$id]) > 0; }
}
