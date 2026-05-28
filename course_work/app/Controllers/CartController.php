<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Http\Response;
use App\Models\ShopModel;

class CartController extends Controller
{
    private ShopModel $shop;
    public function __construct($r) { parent::__construct($r); $this->shop = new ShopModel(); }

    // GET /cart — перегляд кошика
    public function index(array $p): void
    {
        $this->requireAuth();
        Response::noCache();
        $items = $this->shop->getCartItems(Session::userId());
        $total = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $items));
        $this->view('shop/cart', [
            'title' => 'Кошик — ' . APP_NAME,
            'items' => $items,
            'total' => $total,
        ]);
    }

    // POST /api/cart/add — AJAX додати в кошик
    public function add(array $p): void
    {
        $this->requireAuth();
        Response::noCache();
        $data   = $this->request->json();
        $itemId = (int)($data['item_id'] ?? 0);
        $qty    = max(1, (int)($data['qty'] ?? 1));

        if (!hash_equals(Session::csrfToken(), $data[CSRF_TOKEN_NAME] ?? ''))
            $this->json(['success' => false, 'message' => 'CSRF error'], 403);

        $item = $this->shop->findItem($itemId);
        if (!$item || !$item['is_active'])
            $this->json(['success' => false, 'message' => 'Товар не знайдено'], 404);

        $this->shop->addToCart(Session::userId(), $itemId, $qty);
        $count = $this->shop->cartCount(Session::userId());

        // 200 — товар додано
        $this->json(['success' => true, 'cart_count' => $count, 'message' => 'Додано до кошика']);
    }

    // POST /api/cart/update — AJAX оновити кількість
    public function update(array $p): void
    {
        $this->requireAuth();
        Response::noCache();
        $data   = $this->request->json();
        $itemId = (int)($data['item_id'] ?? 0);
        $qty    = (int)($data['qty'] ?? 0);

        if (!hash_equals(Session::csrfToken(), $data[CSRF_TOKEN_NAME] ?? ''))
            $this->json(['success' => false, 'message' => 'CSRF error'], 403);

        if ($qty <= 0) {
            $this->shop->removeFromCart(Session::userId(), $itemId);
        } else {
            $this->shop->updateCart(Session::userId(), $itemId, $qty);
        }

        $items = $this->shop->getCartItems(Session::userId());
        $total = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $items));
        $count = array_sum(array_column($items, 'qty'));

        $this->json(['success' => true, 'total' => $total, 'cart_count' => $count]);
    }

    // POST /api/cart/remove — AJAX видалити з кошика
    public function remove(array $p): void
    {
        $this->requireAuth();
        Response::noCache();
        $data   = $this->request->json();
        $itemId = (int)($data['item_id'] ?? 0);

        if (!hash_equals(Session::csrfToken(), $data[CSRF_TOKEN_NAME] ?? ''))
            $this->json(['success' => false, 'message' => 'CSRF error'], 403);

        $this->shop->removeFromCart(Session::userId(), $itemId);
        $count = $this->shop->cartCount(Session::userId());
        $this->json(['success' => true, 'cart_count' => $count]);
    }

    // POST /api/cart/checkout — оформити замовлення
    public function checkout(array $p): void
    {
        $this->requireAuth();
        Response::noCache();
        $data = $this->request->json();

        if (!hash_equals(Session::csrfToken(), $data[CSRF_TOKEN_NAME] ?? ''))
            $this->json(['success' => false, 'message' => 'CSRF error'], 403);

        $userId    = Session::userId();
        $bookingId = !empty($data['booking_id']) ? (int)$data['booking_id'] : null;
        $items     = $this->shop->getCartItems($userId);

        if (empty($items))
            $this->json(['success' => false, 'message' => 'Кошик порожній'], 400);

        // Перевіряємо ціни з БД (безпека)
        $safeItems = array_map(fn($i) => [
            'id'    => $i['item_id'],
            'price' => $i['price'],
            'qty'   => $i['qty'],
        ], $items);

        $orderId = $this->shop->createOrder($userId, $safeItems, $bookingId);
        $this->shop->clearCart($userId);

        // 201 — Created (замовлення створено)
        Response::status(201);
        $this->json(['success' => true, 'order_id' => $orderId], 201);
    }
}
