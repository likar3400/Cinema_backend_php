<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Http\Response;
use App\Core\PageBuffer;
use App\Models\ShopModel;

class ShopController extends Controller
{
    private ShopModel $shop;
    public function __construct($r) { parent::__construct($r); $this->shop = new ShopModel(); }

    public function index(array $p): void
    {
        $key = 'shop_index';
        if (!Session::isLoggedIn() && $cached = PageBuffer::check($key)) {
            Response::cacheHeaders(md5($cached)); echo $cached; return;
        }
        $this->view('shop/index', [
            'title' => 'Магазин — ' . APP_NAME,
            'items' => $this->shop->getItems(),
            'cartCount' => Session::isLoggedIn() ? $this->shop->cartCount(Session::userId()) : 0,
        ]);
    }

    public function order(array $p): void
    {
        $this->requireAuth(); Response::noCache();
        $data = $this->request->json();
        if (!hash_equals(Session::csrfToken(), $data[CSRF_TOKEN_NAME] ?? ''))
            $this->json(['success'=>false,'message'=>'CSRF'],403);

        $items = $data['items'] ?? [];
        $bookingId = !empty($data['booking_id']) ? (int)$data['booking_id'] : null;
        if (empty($items)) $this->json(['success'=>false,'message'=>'Кошик порожній'],400);

        $safeItems = [];
        foreach ($items as $itm) {
            $found = $this->shop->findItem((int)($itm['id']??0));
            if ($found && $found['is_active']) {
                $safeItems[] = ['id'=>$found['id'],'price'=>$found['price'],'qty'=>max(1,(int)($itm['qty']??1))];
            }
        }
        if (empty($safeItems)) $this->json(['success'=>false,'message'=>'Товари не знайдено'],404);

        $orderId = $this->shop->createOrder(Session::userId(), $safeItems, $bookingId);
        $this->json(['success'=>true,'order_id'=>$orderId], 201);
    }

    public function myOrders(array $p): void
    {
        $this->requireAuth(); Response::noCache();
        $this->view('shop/orders', [
            'title'  => 'Мої замовлення — ' . APP_NAME,
            'orders' => $this->shop->getOrdersByUser(Session::userId()),
        ]);
    }
}
