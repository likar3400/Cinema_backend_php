<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;

class UserModel extends Model
{
    public function findByEmail(string $email): array|false
    { return $this->db->fetchOne('SELECT * FROM users WHERE email=?', [$email]); }

    public function findById(int $id): array|false
    { return $this->db->fetchOne('SELECT * FROM users WHERE id=?', [$id]); }
    public function create(string $name, string $email, string $password, string $role='user', string $phone=''): int
    {
        return $this->db->insert(
            'INSERT INTO users (name,email,password,role,phone) VALUES (?,?,?,?,?)',
            [$name, $email, password_hash($password, PASSWORD_BCRYPT, ['cost'=>BCRYPT_COST]), $role, $phone]
        );
    }

    public function update(int $id, array $fields): bool
    {
        $allowed = ['name','email','phone','role'];
        $set = []; $params = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $fields)) { $set[] = "{$f}=?"; $params[] = $fields[$f]; }
        }
        if (!empty($fields['password'])) {
            $set[] = 'password=?';
            $params[] = password_hash($fields['password'], PASSWORD_BCRYPT, ['cost'=>BCRYPT_COST]);
        }
        if (empty($set)) return false;
        $params[] = $id;
        $this->db->execute('UPDATE users SET '.implode(',',$set).' WHERE id=?', $params);
        return true;
    }

    public function getAll(int $page=1): array
    {
        $offset = ($page-1)*PER_PAGE;
        return $this->db->fetchAll(
            'SELECT id,name,email,role,phone,created_at FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?',
            [PER_PAGE, $offset]
        );
    }

    public function count(): int { return (int)$this->db->fetchColumn('SELECT COUNT(*) FROM users'); }

    public function delete(int $id): bool
    { return $this->db->execute('DELETE FROM users WHERE id=?',[$id]) > 0; }

    public function emailExists(string $email, int $excludeId=0): bool
    { return (int)$this->db->fetchColumn('SELECT COUNT(*) FROM users WHERE email=? AND id!=?',[$email,$excludeId]) > 0; }

    public function verifyPassword(string $plain, string $hash): bool
    { return password_verify($plain, $hash); }
}
