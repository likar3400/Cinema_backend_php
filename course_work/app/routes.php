<?php
declare(strict_types=1);
use App\Core\Router;

$router = new Router();

// Public
$router->get('/',            'App\Controllers\HomeController',   'index');
$router->get('/movies',      'App\Controllers\MovieController',  'index');
$router->get('/movies/{id}', 'App\Controllers\MovieController',  'show');
$router->get('/schedule',    'App\Controllers\MovieController',  'schedule');
$router->get('/news',        'App\Controllers\NewsController',   'index');
$router->get('/news/{id}',   'App\Controllers\NewsController',   'show');
$router->get('/shop',        'App\Controllers\ShopController',   'index');
$router->get('/shop/orders', 'App\Controllers\ShopController',   'myOrders');

// Auth
$router->get('/login',      'App\Controllers\AuthController', 'loginForm');
$router->post('/login',     'App\Controllers\AuthController', 'login');
$router->post('/api/login', 'App\Controllers\AuthController', 'loginAjax');
$router->get('/register',   'App\Controllers\AuthController', 'registerForm');
$router->post('/register',  'App\Controllers\AuthController', 'register');
$router->get('/logout',     'App\Controllers\AuthController', 'logout');

// Booking
$router->get('/booking/{id}',      'App\Controllers\BookingController', 'seatMap');
$router->get('/profile/bookings',  'App\Controllers\BookingController', 'myBookings');

// Cart
$router->get('/cart',               'App\Controllers\CartController', 'index');
$router->post('/api/cart/add',      'App\Controllers\CartController', 'add');
$router->post('/api/cart/update',   'App\Controllers\CartController', 'update');
$router->post('/api/cart/remove',   'App\Controllers\CartController', 'remove');
$router->post('/api/cart/checkout', 'App\Controllers\CartController', 'checkout');

// JSON API
$router->post('/api/booking',         'App\Controllers\BookingController', 'book');
$router->post('/api/booking/cancel',  'App\Controllers\BookingController', 'cancel');
$router->get('/api/sessions',         'App\Controllers\MovieController',   'apiSessions');
$router->post('/api/shop/order',      'App\Controllers\ShopController',    'order');

// Reviews API
$router->post('/api/reviews/add',         'App\Controllers\ReviewController', 'add');
$router->post('/api/reviews/{id}/delete', 'App\Controllers\ReviewController', 'delete');

// Admin
$router->get('/admin',           'App\Controllers\AdminController', 'dashboard');
$router->get('/admin/stats',     'App\Controllers\AdminController', 'stats');
$router->get('/api/admin/stats', 'App\Controllers\AdminController', 'statsJson');

$router->get('/admin/movies',                'App\Controllers\AdminController', 'movies');
$router->get('/admin/movies/create',         'App\Controllers\AdminController', 'movieCreate');
$router->post('/admin/movies/create',        'App\Controllers\AdminController', 'movieStore');
$router->get('/admin/movies/{id}/edit',      'App\Controllers\AdminController', 'movieEdit');
$router->post('/admin/movies/{id}/edit',     'App\Controllers\AdminController', 'movieUpdate');
$router->post('/admin/movies/{id}/delete',   'App\Controllers\AdminController', 'movieDelete');

$router->get('/admin/sessions',              'App\Controllers\AdminController', 'sessions');
$router->get('/admin/sessions/create',       'App\Controllers\AdminController', 'sessionCreate');
$router->post('/admin/sessions/create',      'App\Controllers\AdminController', 'sessionStore');
$router->get('/admin/sessions/{id}/edit',    'App\Controllers\AdminController', 'sessionEdit');
$router->post('/admin/sessions/{id}/edit',   'App\Controllers\AdminController', 'sessionUpdate');
$router->post('/admin/sessions/{id}/delete', 'App\Controllers\AdminController', 'sessionDelete');

$router->get('/admin/bookings',              'App\Controllers\AdminController', 'bookings');
$router->post('/admin/bookings/{id}/cancel', 'App\Controllers\AdminController', 'bookingCancel');

$router->get('/admin/users',                 'App\Controllers\AdminController', 'users');
$router->get('/admin/users/create',          'App\Controllers\AdminController', 'userCreate');
$router->post('/admin/users/create',         'App\Controllers\AdminController', 'userStore');
$router->get('/admin/users/{id}/edit',       'App\Controllers\AdminController', 'userEdit');
$router->post('/admin/users/{id}/edit',      'App\Controllers\AdminController', 'userUpdate');
$router->post('/admin/users/{id}/delete',    'App\Controllers\AdminController', 'userDelete');

$router->get('/admin/news',                  'App\Controllers\AdminController', 'news');
$router->get('/admin/news/create',           'App\Controllers\AdminController', 'newsCreate');
$router->post('/admin/news/create',          'App\Controllers\AdminController', 'newsStore');
$router->get('/admin/news/{id}/edit',        'App\Controllers\AdminController', 'newsEdit');
$router->post('/admin/news/{id}/edit',       'App\Controllers\AdminController', 'newsUpdate');
$router->post('/admin/news/{id}/delete',     'App\Controllers\AdminController', 'newsDelete');

$router->get('/admin/halls',                 'App\Controllers\AdminController', 'halls');
$router->get('/admin/halls/create',          'App\Controllers\AdminController', 'hallCreate');
$router->post('/admin/halls/create',         'App\Controllers\AdminController', 'hallStore');
$router->get('/admin/halls/{id}/edit',       'App\Controllers\AdminController', 'hallEdit');
$router->post('/admin/halls/{id}/edit',      'App\Controllers\AdminController', 'hallUpdate');
$router->post('/admin/halls/{id}/delete',    'App\Controllers\AdminController', 'hallDelete');

$router->get('/admin/shop',                  'App\Controllers\AdminController', 'shopItems');
$router->get('/admin/shop/create',           'App\Controllers\AdminController', 'shopItemCreate');
$router->post('/admin/shop/create',          'App\Controllers\AdminController', 'shopItemStore');
$router->get('/admin/shop/{id}/edit',        'App\Controllers\AdminController', 'shopItemEdit');
$router->post('/admin/shop/{id}/edit',       'App\Controllers\AdminController', 'shopItemUpdate');
$router->post('/admin/shop/{id}/delete',     'App\Controllers\AdminController', 'shopItemDelete');

// Admin reviews
$router->get('/admin/reviews',               'App\Controllers\AdminController', 'reviews');
$router->post('/admin/reviews/{id}/delete',  'App\Controllers\AdminController', 'reviewDelete');

// Admin movie categories
$router->get('/admin/categories',            'App\Controllers\AdminController', 'categories');
$router->post('/admin/categories/create',    'App\Controllers\AdminController', 'categoryStore');
$router->post('/admin/categories/{id}/delete','App\Controllers\AdminController','categoryDelete');

return $router;
