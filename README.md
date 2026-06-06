#  CineMax — Інформаційна система онлайн-продажу квитків для кінотеатру
---

##  Про проєкт

**CineMax** — це повнофункціональна інформаційна система для кінотеатру, розроблена з нуля на чистому PHP без використання фреймворків. Система реалізує повний цикл онлайн-продажу квитків: від перегляду афіші до підтвердження оплати та отримання електронного квитка.

Проєкт демонструє практичне застосування:
- архітектурного патерну **MVC** (Model-View-Controller)
- принципів **ООП** (абстрактні класи, інтерфейси, Singleton, інкапсуляція)
- **AJAX**-асинхронності через Fetch API без бібліотек
- **буферизації** вихідного потоку та серверного кешування HTML
- системи **HTTP статус-кодів** (200, 201, 303, 401, 403, 404, 409, 422, 500)
- **CRUD**-операцій для всіх сутностей через адмін-панель
- безпечної автентифікації з **bcrypt** хешуванням паролів
- захисту від **SQL-ін'єкцій**, **XSS**, **CSRF** і **race condition**

---

##  Швидкий старт (MAMP)

### Вимоги
- MAMP (Apache + MySQL + PHP 8.2+)
- Браузер з підтримкою ES2022

### Крок 1 — Розмістіть файли
```
/Applications/MAMP/htdocs/course_work_CINEMA/course_work/
```

### Крок 2 — Налаштуйте конфігурацію

Відкрийте `config/config.php` і вкажіть свої параметри:

```php
// База даних
define('DB_HOST', 'localhost');
define('DB_PORT', 8889);          // порт MySQL у MAMP (зазвичай 8889)
define('DB_NAME', 'cinema_db');
define('DB_USER', 'root');
define('DB_PASS', 'root');

// URL застосунку (БЕЗ слеша в кінці)
define('APP_URL', 'http://localhost:8888/course_work_CINEMA/course_work/public');

// Пагінація
define('PER_PAGE', 12);

// Кеш (секунди)
define('CACHE_TTL', 3600);
```

> **Порти MAMP:** Apache зазвичай 8888, MySQL зазвичай 8889.
> Перевірте у MAMP → Preferences → Ports.

### Крок 3 — Створіть базу даних

Відкрийте **phpMyAdmin** → `http://localhost:8888/phpMyAdmin`

**Перший запуск** (нова БД):
1. Натисніть "Новa" → назвіть `cinema_db` → кодування `utf8mb4_unicode_ci` → Створити
2. Відкрийте вкладку **SQL** → вставте вміст `sql/schema.sql` → Вперед

**Якщо БД вже існує** (оновлення):
```sql
-- Запустіть тільки цей файл:
sql/migrate_v2.sql
-- Він додає: cart, reviews, movie_categories
-- Оновлює паролі та назви фільмів
```

### Крок 4 — Відкрийте у браузері

```
http://localhost:8888/course_work_CINEMA/course_work/public
```

### Облікові дані

| Роль | Email | Пароль |
|------|-------|--------|
|  Адміністратор | admin@cinema.ua | admin123! |
|  Звичайний користувач | Реєстрація на сайті | Вільний вибір |

---

##  Структура проєкту

```
course_work/
│
├──  app/                          # Весь PHP-код застосунку
│   │
│   ├──  Controllers/              # C — Контролери (обробка запитів)
│   │   ├── AdminController.php      # Адмін-панель: 35+ методів CRUD
│   │   ├── AuthController.php       # Авторизація, реєстрація, вихід
│   │   ├── BookingController.php    # Бронювання: карта місць, оплата
│   │   ├── CartController.php       # Кошик: AJAX add/update/remove/checkout
│   │   ├── HomeController.php       # Головна сторінка з анонсами
│   │   ├── MovieController.php      # Афіша, розклад, сторінка фільму
│   │   ├── NewsController.php       # Новини кінотеатру
│   │   ├── ReviewController.php     # Відгуки: AJAX add/delete
│   │   └── ShopController.php       # Магазин снеків і напоїв
│   │
│   ├──  Models/                   # M — Моделі (робота з БД)
│   │   ├── BookingModel.php         # Бронювання + транзакції FOR UPDATE
│   │   ├── HallModel.php            # Зали + автогенерація місць
│   │   ├── MovieModel.php           # Фільми + категорії
│   │   ├── NewsModel.php            # Новини
│   │   ├── ReviewModel.php          # Відгуки + середній рейтинг AVG()
│   │   ├── SessionModel.php         # Сеанси + карта місць + бронювання
│   │   ├── ShopModel.php            # Товари + кошик + замовлення
│   │   └── UserModel.php            # Користувачі + bcrypt
│   │
│   ├──  Views/                    # V — Шаблони (HTML + PHP)
│   │   ├──  admin/                # Адмін-панель (8 розділів)
│   │   │   ├──  bookings/         # Список бронювань з фільтром
│   │   │   ├──  categories/       # Категорії фільмів
│   │   │   ├──  halls/            # Зали
│   │   │   ├──  movies/           # Фільми (з постером, жанром, рейтингом)
│   │   │   ├──  news/             # Новини
│   │   │   ├──  reviews/          # Відгуки
│   │   │   ├──  sessions/         # Сеанси
│   │   │   ├──  shop/             # Товари магазину
│   │   │   ├──  stats/            # Статистика з AJAX-оновленням
│   │   │   ├──  users/            # Користувачі
│   │   │   └── dashboard.php        # Головний дашборд
│   │   ├──  auth/                 # Вхід і реєстрація
│   │   ├──  booking/              # Карта місць + мої квитки
│   │   ├──  error/                # 404, 403, 500 сторінки
│   │   ├──  movies/               # Афіша, розклад, деталі фільму
│   │   ├──  shop/                 # Магазин, кошик, замовлення
│   │   └──  partials/             # layout.php, admin_layout.php
│   │
│   ├──  Core/                     # Ядро фреймворку
│   │   ├──  Contracts/            # ООП інтерфейси
│   │   │   ├── CrudInterface.php    # Контракт: count() + RepositoryInterface
│   │   │   └── RepositoryInterface.php  # Контракт: find(), delete()
│   │   ├──  Http/
│   │   │   ├── Request.php          # Інкапсуляція HTTP-запиту
│   │   │   └── Response.php         # Статус коди, редіректи, ETag
│   │   ├── Autoloader.php           # PSR-4 автозавантаження класів
│   │   ├── Controller.php           # abstract: view(), json(), requireAuth()
│   │   ├── Database.php             # final Singleton PDO з'єднання
│   │   ├── Model.php                # abstract: $db, offset() для пагінації
│   │   ├── PageBuffer.php           # Файловий кеш HTML (ob_start/ob_get_clean)
│   │   ├── Router.php               # Regex маршрутизатор {param}
│   │   └── Session.php              # Сесія, авторизація, flash-повідомлення
│   │
│   └── routes.php                   # Реєстрація всіх 60+ маршрутів
│
├──  config/
│   └── config.php                   # Всі константи системи
│
├──  public/                       # Публічна директорія (document root)
│   ├── index.php                    # Front Controller — єдина точка входу
│   ├── .htaccess                    # mod_rewrite: всі запити → index.php
│   ├── css/
│   │   └── style.css                # Власні стилі (темна тема)
│   ├── js/
│   │   └── app.js                   # JavaScript (Fetch API, DOM маніпуляції)
│   └── images/
│       ├── posters/                 # Постери фільмів (JPG/PNG/WEBP)
│       └── shop/                    # Зображення товарів магазину
│
├──  sql/
│   ├── schema.sql                   # Повна схема БД + seed-дані
│   └── migrate_v2.sql               # Міграція: cart, reviews, categories
│
├──  cache/                        # Серверний кеш HTML-сторінок
│   └── .gitkeep
│
└── README.md                        # Цей файл
```

---

## Архітектура та патерни

### MVC — поділ відповідальності

```
HTTP запит
    ↓
public/.htaccess → public/index.php (Front Controller)
    ↓
App\Core\Router::dispatch()   ← знаходить маршрут за regex
    ↓
App\Controllers\XxxController ← обробляє запит
    ↓              ↓
App\Models\Xxx    Controller::view()
(SQL через PDO)   (ob_start → шаблон → ob_get_clean → layout)
    ↓
HTTP відповідь (HTML / JSON)
```

### ООП — класи та інтерфейси

```php
// Абстрактний базовий клас — не можна створити напряму
abstract class Model {
    protected Database $db;              // Dependency Injection
    protected function offset(int $page): int { ... } // шаблонний метод
}

// Фінальний Singleton — єдиний екземпляр PDO
final class Database {
    private static ?self $instance = null;
    private function __construct() { /* PDO */ }
    public static function getInstance(): self { ... }
}

// Інтерфейси — програмний контракт
interface RepositoryInterface {
    public function find(int $id): array|false;
    public function delete(int $id): bool;
}
interface CrudInterface extends RepositoryInterface {
    public function count(): int;
}

// Конкретна модель наслідує абстрактний клас
class MovieModel extends Model {
    public function getAll(int $page): array { ... }
    public function create(array $d): int    { ... }
}
```

### PRG — Post/Redirect/Get

Кожна форма після POST повертає **303 See Other** і перенаправляє на GET.
Це запобігає повторному відправленню форми при натисканні F5.

```
POST /admin/movies/create  → 303 See Other → GET /admin/movies
POST /api/booking          → 201 + window.location (JS redirect)
POST /login                → 303 See Other → GET /
```

### Output Buffering — буферизація

```php
// Controller::view() — рівень 1
ob_start();              // починаємо перехоплення
require $viewFile;       // весь echo/print іде в буфер
$content = ob_get_clean(); // забираємо HTML
require $layoutFile;     // вставляємо в шаблон

// PageBuffer — рівень 2 (серверний кеш)
if ($cached = PageBuffer::check($key)) {
    echo $cached; return; // без PHP і SQL
}
// ... рендеринг ...
PageBuffer::store($key, $html); // зберігаємо у cache/pg_xxx.html
```

---

## AJAX та асинхронність

Всі асинхронні запити через стандартний **Fetch API** з JSON. Єдиний спільний патерн:

```javascript
fetch(`${APP_URL_JS}/api/...`, {
    method:  'POST',
    headers: {
        'Content-Type':     'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify({ ...дані, _csrf: CSRF_TOKEN })
})
.then(r  => r.json())
.then(d  => { if (d.success) { /* оновлюємо DOM */ } })
.catch(() => { /* мережева помилка */ });
```

### Де реалізована асинхронність

| Місце | Функція | Ендпоінт | Статус |
|-------|---------|----------|--------|
| Розклад | `loadSched()` — фільтр по даті/фільму | `GET /api/sessions` | 200 |
| Сторінка фільму | Зміна дати → нові сеанси | `GET /api/sessions` | 200 |
| Відгук | `submitReview()` → картка в DOM | `POST /api/reviews/add` | 201/409 |
| Бронювання | `confirmBooking()` → PRG | `POST /api/booking` | 201/409 |
| Кошик | `addToCart()`, `updateQty()`, `removeItem()` | `POST /api/cart/*` | 201/200 |
| Кошик checkout | `checkout()` → redirect | `POST /api/cart/checkout` | 201 |
| Статистика | `loadStats()` → всі блоки | `GET /api/admin/stats` | 200 |
| Видалення адмін | `delMovie()`, `delUser()` і т.д. | `POST /admin/*/delete` | 200 |
| Скасування броні | `cancelBk()` → зміна статусу | `POST /api/booking/cancel` | 200 |

---

## База даних

### Схема таблиць

```
users ──────────────────────────────────────────────────────────┐
  id, name, email, password(bcrypt), role, phone, created_at    │
                                                                  │
movies ─────────────────────────────────────────────────────┐   │
  id, title, description, genre, category_id, duration,     │   │
  rating, age_rating, poster, trailer_url, release_date      │   │
  └─── movie_categories (id, name, slug)                    │   │
                                                             │   │
halls ──────────────────────────────────────────────┐       │   │
  id, name, row_count, col_count, type              │       │   │
  └─── seats (id, hall_id, row_num, col_num, type) │       │   │
                                                    │       │   │
sessions ───────────────────────────────────────────┘       │   │
  id, movie_id, hall_id, starts_at, ends_at,                │   │
  price, price_vip, language, format, is_active             │   │
                                                             │   │
bookings ────────────────────────────────────────────────────┘   │
  id, user_id, session_id, seat_id,                              │
  price_paid, ticket_code, status, created_at                    │
  UNIQUE KEY (session_id, seat_id)  ← захист від дублювання     │
                                                                  │
reviews ──────────────────────────────────────────────────────────┘
  id, user_id, movie_id, rating, body, is_active
  UNIQUE KEY (user_id, movie_id)  ← один відгук на фільм

news: id, title, body, image, is_active, created_at

shop_items: id, name, description, price, category, image, is_active
cart: id, user_id, item_id, qty  UNIQUE KEY (user_id, item_id)
shop_orders: id, user_id, booking_id, total, status
shop_order_items: id, order_id, item_id, qty, price
```

### Генерація місць

При створенні залу через адмін-панель місця генеруються автоматично:
```php
for ($row = 1; $row <= $rowCount; $row++) {
    for ($col = 1; $col <= $colCount; $col++) {
        $type = $row <= 2 ? 'vip' : 'standard';
        // INSERT INTO seats ...
    }
}
// Перші 2 ряди — VIP, решта — стандарт
```

---

##  Маршрути (routes.php)

```
GET  /                           → HomeController::index
GET  /movies                     → MovieController::index       (афіша)
GET  /movies/{id}                → MovieController::show        (фільм)
GET  /schedule                   → MovieController::schedule    (розклад)
GET  /news, /news/{id}           → NewsController
GET  /shop                       → ShopController::index
GET  /cart                       → CartController::index

GET  /login                      → AuthController::loginForm
POST /login                      → AuthController::login
POST /api/login                  → AuthController::loginAjax (JSON)
GET  /register                   → AuthController::registerForm
POST /register                   → AuthController::register
GET  /logout                     → AuthController::logout → 303 /

GET  /booking/{id}               → BookingController::seatMap
GET  /profile/bookings           → BookingController::myBookings
POST /api/booking                → BookingController::book   → 201/409
POST /api/booking/cancel         → BookingController::cancel → 200/404

POST /api/reviews/add            → ReviewController::add    → 201/409/422
POST /api/reviews/{id}/delete    → ReviewController::delete → 200 (адмін)

POST /api/cart/add               → CartController::add      → 201
POST /api/cart/update            → CartController::update   → 200
POST /api/cart/remove            → CartController::remove   → 200
POST /api/cart/checkout          → CartController::checkout → 201

GET  /api/sessions               → MovieController::apiSessions → 200
GET  /api/admin/stats            → AdminController::statsJson   → 200

GET  /admin                      → AdminController::dashboard
GET  /admin/stats                → AdminController::stats
GET  /admin/movies               → AdminController::movies
... (60+ маршрутів загалом)
```

---

##  Безпека — детально

### Bcrypt хешування
```php
// Реєстрація — хешуємо пароль
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
// cost=12 → 4096 ітерацій → ~250мс → брутфорс практично неможливий

// Вхід — порівнюємо
password_verify($inputPassword, $hashFromDB); // → true/false
// Зворотнє розшифрування неможливе
```

### CSRF захист
```php
// Генерація одноразового токена в Session::start()
$_SESSION['_csrf'] = bin2hex(random_bytes(32));

// Перевірка в кожному POST
$token = $data[CSRF_TOKEN_NAME] ?? $data['_csrf'] ?? '';
if (!hash_equals(Session::csrfToken(), $token))
    $this->json(['success' => false], 403);
```

### SQL-ін'єкції (PDO Prepared Statements)
```php
// НЕБЕЗПЕЧНО (не використовуємо):
$db->query("SELECT * FROM users WHERE id = $id");

// БЕЗПЕЧНО (PDO Prepared Statements):
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]); // параметри окремо від SQL
```

### Race condition при бронюванні
```php
$this->db->beginTransaction();
// FOR UPDATE блокує рядок — другий запит чекає
$existing = $this->db->fetchOne(
    "SELECT id FROM bookings WHERE session_id=? AND seat_id=? FOR UPDATE",
    [$sessionId, $seatId]
);
if ($existing) { $this->db->rollback(); return ['success'=>false]; }
// ... INSERT ...
$this->db->commit();
```

---
##  Пагінація

Серверна пагінація через SQL `LIMIT`/`OFFSET`:

```php
// config.php
define('PER_PAGE', 12);

// Model::offset()
protected function offset(int $page): int {
    return max(0, ($page - 1) * PER_PAGE);
}

// Приклад запиту
SELECT * FROM movies WHERE is_active=1
ORDER BY release_date DESC
LIMIT 12 OFFSET 24   -- сторінка 3: (3-1)*12 = 24
```

Навігація: посилання `?page=N`, кількість сторінок: `ceil(count / PER_PAGE)`.
Реалізована у: Афіша, Бронювання, Користувачі, Новини, Магазин, Відгуки, Сеанси.

---

##  Кешування

```
Запит GET /movies?page=1
         ↓
PageBuffer::check('movies_p1')
         ↓ Є кеш?
    ТАК ─────────────────────────► echo $cached + 304 Not Modified
         │                          (без PHP і SQL)
    НІ
         ↓
MovieModel::getAll()             ← SQL запит
Controller::view()               ← рендеринг PHP шаблону
         ↓
PageBuffer::store('movies_p1')   ← зберігаємо у cache/pg_xxx.html
         ↓
echo $html                       ← відповідаємо браузеру

Після змін (Admin зберіг фільм):
PageBuffer::invalidate('movies') ← видаляємо файл кешу
```

**Не кешується:** адмін-панель, кошик, квитки, бронювання, будь-що після авторизації.

---

##  HTTP статус коди у проєкті

| Код | Назва | Де і коли |
|-----|-------|-----------|
| **200** | OK | Успішний GET будь-якої сторінки |
| **201** | Created | Після бронювання, відгуку, замовлення кошика |
| **303** | See Other | PRG після всіх POST-форм в адміні, після входу/виходу |
| **304** | Not Modified | Кеш ETag — браузер використовує локальний кеш |
| **401** | Unauthorized | Спроба відкрити `/profile/bookings` без входу |
| **403** | Forbidden | CSRF помилка, не-адмін намагається зайти в `/admin` |
| **404** | Not Found | Неіснуючий маршрут, фільм з is_active=0 |
| **409** | Conflict | Місце вже зайняте, дублікат email, повторний відгук |
| **422** | Unprocessable | Рейтинг не в діапазоні 1-10, текст відгуку < 10 символів |
| **500** | Server Error | Непередбачена помилка (логується в error_log) |

---

## Як додати зображення

### Постери фільмів
**Спосіб 1 — через адмін-панель (рекомендовано):**
1. Адмін → Фільми → Редагувати фільм
2. Поле "Постер (файл)" → завантажте JPG/PNG/WEBP
3. Файл збережеться у `public/images/posters/` автоматично

**Спосіб 2 — вручну:**
```bash
# Скопіюйте файл
public/images/posters/your_movie.jpg

# Оновіть БД (phpMyAdmin → SQL)
UPDATE movies SET poster='/images/posters/your_movie.jpg'
WHERE title = 'Назва фільму';
```

### Зображення товарів магазину

Файли вже є у `public/images/shop/`:
```
Cola.png          Pepsi.png         Orange_juice.png
Nachos.png        Hot-dog.png       little-pop-corn.png
medium-pop-corn.png  large-pop-corn.png  Combo_1.png  Combo_2.png
```

Запустіть `sql/migrate_v2.sql` — зображення з'являться автоматично.
Або через адмін: Магазин → Редагувати товар → поле "Зображення".

---

##  Технічний стек

| Технологія | Версія | Призначення |
|-----------|--------|-------------|
| PHP | 8.2+ | Серверна мова, бізнес-логіка, шаблонізація |
| MySQL | 8.0 | Реляційна БД, транзакції, індекси |
| PDO | — | Безпечний доступ до БД (Prepared Statements) |
| JavaScript | ES2022 | AJAX (Fetch API), DOM-маніпуляції |
| CSS | 3 | Власні стилі, темна тема, responsive |
| Apache | 2.4 | Веб-сервер, mod_rewrite |
| MAMP | 6+ | Локальне середовище розробки |

---

