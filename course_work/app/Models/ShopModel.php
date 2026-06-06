<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\Model;

class ShopModel extends Model
{
    public function getItems(string $category=''): array
    {
        $w = $category ? 'WHERE is_active=1 AND category=?' : 'WHERE is_active=1';
        $p = $category ? [$category] : [];
        return $this->db->fetchAll("SELECT * FROM shop_items {$w} ORDER BY category,price", $p);
    }

    public function getAllItems(int $page=1): array
    {
        $offset=($page-1)*PER_PAGE;
        return $this->db->fetchAll('SELECT * FROM shop_items ORDER BY category,name LIMIT ? OFFSET ?',[PER_PAGE,$offset]);
    }

    public function countItems(): int { return (int)$this->db->fetchColumn('SELECT COUNT(*) FROM shop_items'); }

    public function findItem(int $id): array|false { return $this->db->fetchOne('SELECT * FROM shop_items WHERE id=?',[$id]); }

    public function createItem(array $d): int
    {
        $image = $d['image'] ?? null;
        return $this->db->insert(
            'INSERT INTO shop_items (name,description,price,category,image,is_active) VALUES (?,?,?,?,?,?)',
            [$d['name'],$d['description']??'',(float)$d['price'],$d['category']??'popcorn',$image,(int)($d['is_active']??1)]
        );
    }

    public function updateItem(int $id, array $d): bool
    {
        $image = $d['image'] ?? null;
        return $this->db->execute(
            'UPDATE shop_items SET name=?,description=?,price=?,category=?,image=?,is_active=? WHERE id=?',
            [$d['name'],$d['description']??'',(float)$d['price'],$d['category']??'popcorn',$image,(int)($d['is_active']??1),$id]
        ) > 0;
    }

    public function deleteItem(int $id): bool
    { return $this->db->execute('DELETE FROM shop_items WHERE id=?',[$id]) > 0; }

    public function createOrder(int $userId, array $items, ?int $bookingId=null): int
    {
        $total = 0;
        foreach ($items as $item) $total += (float)$item['price'] * (int)($item['qty']??1);

        $orderId = $this->db->insert(
            'INSERT INTO shop_orders (user_id,booking_id,total,status) VALUES (?,?,?,?)',
            [$userId,$bookingId,$total,'paid']
        );
        foreach ($items as $item) {
            $this->db->execute(
                'INSERT INTO shop_order_items (order_id,item_id,qty,price) VALUES (?,?,?,?)',
                [$orderId,(int)$item['id'],(int)($item['qty']??1),(float)$item['price']]
            );
        }
        return $orderId;
    }

    public function getOrdersByUser(int $userId): array
    {
        return $this->db->fetchAll(
            'SELECT o.*,GROUP_CONCAT(si.name ORDER BY si.name SEPARATOR ", ") AS item_names
             FROM shop_orders o
             JOIN shop_order_items oi ON oi.order_id=o.id
             JOIN shop_items si ON si.id=oi.item_id
             WHERE o.user_id=? GROUP BY o.id ORDER BY o.created_at DESC', [$userId]
        );
    }
    public function getCartItems(int $userId): array
    {
        return $this->db->fetchAll(
            'SELECT c.*, si.name, si.price, si.image, si.category, si.description
             FROM cart c
             JOIN shop_items si ON si.id = c.item_id
             WHERE c.user_id = ? AND si.is_active = 1
             ORDER BY c.created_at',
            [$userId]
        );
    }

    public function addToCart(int $userId, int $itemId, int $qty = 1): void
    {
        $this->db->execute(
            'INSERT INTO cart (user_id, item_id, qty) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE qty = qty + VALUES(qty)',
            [$userId, $itemId, $qty]
        );
    }

    public function updateCart(int $userId, int $itemId, int $qty): void
    {
        $this->db->execute(
            'UPDATE cart SET qty = ? WHERE user_id = ? AND item_id = ?',
            [$qty, $userId, $itemId]
        );
    }

    public function removeFromCart(int $userId, int $itemId): void
    {
        $this->db->execute(
            'DELETE FROM cart WHERE user_id = ? AND item_id = ?',
            [$userId, $itemId]
        );
    }

    public function clearCart(int $userId): void
    {
        $this->db->execute('DELETE FROM cart WHERE user_id = ?', [$userId]);
    }

    public function cartCount(int $userId): int
    {
        return (int)$this->db->fetchColumn(
            'SELECT COALESCE(SUM(qty), 0) FROM cart WHERE user_id = ?',
            [$userId]
        );
    }
}
