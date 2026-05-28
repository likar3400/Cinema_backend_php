#  CineMax — Система керування кінотеатром

PHP MVC-застосунок без фреймворків. Курсова робота.

---

##  Встановлення (MAMP)

1. Скопіюйте папку `course_work` у `htdocs/course_work_CINEMA/`
2. Відкрийте phpMyAdmin → вкладка **SQL**
3. **Перший запуск** — виконайте `sql/schema.sql` (повна БД)
4. **Якщо БД вже є** — виконайте тільки `sql/migrate_v2.sql` (додає таблиці cart, reviews, movie_categories + оновлює паролі)
5. Відредагуйте `config/config.php` — вкажіть порти MAMP і ваш шлях

```php
define('DB_PORT', 8889);   // порт MySQL у MAMP
define('APP_URL', 'http://localhost:8888/course_work_CINEMA/course_work/public');
```

6. Відкрийте `http://localhost:8888/course_work_CINEMA/course_work/public`

**Дані для входу:**
| Email | Пароль | Роль |
| admin@cinema.ua | admin123! | Адмін |

---

##  Структура проєкту (MVC)

```
course_work/
├── app/
│   ├── Controllers/          ← C: обробляють запити
│   │   ├── AdminController.php
│   │   ├── AuthController.php
│   │   ├── BookingController.php
│   │   ├── CartController.php
│   │   ├── HomeController.php
│   │   ├── MovieController.php
│   │   ├── NewsController.php
│   │   ├── ReviewController.php
│   │   └── ShopController.php
│   ├── Models/               ← M: робота з БД
│   │   ├── BookingModel.php
│   │   ├── HallModel.php
│   │   ├── MovieModel.php
│   │   ├── NewsModel.php
│   │   ├── ReviewModel.php
│   │   ├── SessionModel.php
│   │   ├── ShopModel.php
│   │   └── UserModel.php
│   ├── Views/                ← V: шаблони PHP
│   │   ├── admin/            ← адмін-панель
│   │   ├── auth/             ← вхід/реєстрація
│   │   ├── booking/          ← карта місць, квитки
│   │   ├── movies/           ← афіша, розклад, фільм
│   │   ├── shop/             ← магазин, кошик
│   │   └── partials/         ← layout, admin_layout
│   ├── Core/
│   │   ├── Contracts/        ← OOP інтерфейси
│   │   │   ├── CrudInterface.php
│   │   │   └── RepositoryInterface.php
│   │   ├── Controller.php    ← abstract base controller
│   │   ├── Database.php      ← Singleton PDO
│   │   ├── Model.php         ← abstract base model
│   │   ├── PageBuffer.php    ← буферизація сторінок
│   │   ├── Router.php        ← маршрутизатор
│   │   └── Session.php       ← управління сесією
│   └── routes.php            ← усі маршрути
├── config/config.php         ← налаштування
├── public/
│   ├── index.php             ← Front Controller
│   ├── .htaccess             ← mod_rewrite
│   ├── css/style.css         ← стилі
│   ├── js/app.js             ← JS фронтенд
│   └── images/
│       ├── shop/             ← зображення магазину
│       └── posters/          ← постери фільмів (завантажуй сюди)
├── sql/
│   ├── schema.sql            ← повна схема БД
│   └── migrate_v2.sql        ← міграція для існуючої БД
└── cache/                    ← кеш буферизованих сторінок
```

---

##  Архітектурні паттерни

### MVC (Model-View-Controller)
- **Model** — клас, що наслідує `abstract Model`, працює з PDO
- **View** — PHP-шаблони у `app/Views/`
- **Controller** — наслідує `abstract Controller`, викликає Model і View

### OOP: Abstract Classes + Interfaces
```php
// Абстрактний базовий клас (abstract class)
abstract class Model {
    protected Database $db;
    protected function offset(int $page): int { ... }
}

// Інтерфейси (interface)
interface RepositoryInterface {
    public function find(int $id): array|false;
    public function delete(int $id): bool;
}
interface CrudInterface extends RepositoryInterface {
    public function count(): int;
}
```

### Singleton — Database
```php
Database::getInstance() // один з'єднання на весь запит
```

### PRG (Post/Redirect/Get)
Після кожного POST (форми, оплата, бронювання) → **303 See Other** редірект.
Запобігає повторному відправленню форми при F5.
```php
// Після бронювання квитка:
window.location.href = `/booking/{id}?booked=1&code=ABC123`;
// Після POST в адміні:
Response::redirect('/admin/movies', 303);
```

---

##  Пагінація

**Сервер (PHP):** `LIMIT PER_PAGE OFFSET (page-1)*PER_PAGE`  
**Навігація:** посилання `?page=N` у всіх адмін-списках  
**AJAX:** відгуки додаються без перезавантаження (JS fetch → 201 Created)

Константа `PER_PAGE = 12` у `config/config.php`.

---

##  Буферизація (Output Buffering)

`PageBuffer` кешує HTML-відповідь у файл `cache/pg_<hash>.html`.

```
Запит GET → перевірка кешу → є? → 200 + Etag з кешу
                            → нема? → рендер → зберегти → відповісти
```

**Кеш скидається** (PageBuffer::invalidate) після:
- Додавання/зміни фільму, новини, товару
- Публікації або видалення відгуку

**Не кешується** (Response::noCache):
- Авторизовані сторінки
- Всі POST-запити
- Адмін-панель
- Відповіді зі статусами 201, 303, 401, 403, 404, 409, 500

---

##  HTTP Status Codes

| Код | Де використовується |
|-----|---------------------|
| 200 | Успішний GET |
| 201 | Нове бронювання, нове замовлення, новий відгук |
| 301 | Постійний редірект (застаріла URL) |
| 303 | PRG після POST (форми, logout, admin) |
| 304 | Not Modified (ETag кеш) |
| 401 | Неавторизований (AJAX → JSON, web → /login) |
| 403 | Заборонено (не адмін, CSRF помилка) |
| 404 | Сторінка/запис не знайдено → редірект на список |
| 409 | Конфлікт: місце вже зайняте, email вже існує, дублікат відгуку |
| 422 | Помилка валідації (форми входу, реєстрації) |
| 500 | Серверна помилка |

---

##  AJAX (Асинхронність)

Всі AJAX-запити через `fetch()` з JSON:

| Endpoint | Метод | Опис |
|----------|-------|------|
| `/api/login` | POST | Вхід (JSON) |
| `/api/booking` | POST | Бронювання місця → 200/409 |
| `/api/booking/cancel` | POST | Скасування |
| `/api/sessions` | GET | Фільтр розкладу |
| `/api/cart/add` | POST | Додати в кошик |
| `/api/cart/update` | POST | Змінити кількість |
| `/api/cart/remove` | POST | Видалити з кошика |
| `/api/cart/checkout` | POST | Оформити замовлення → 201 |
| `/api/reviews/add` | POST | Новий відгук → 201/409 |
| `/api/reviews/{id}/delete` | POST | Видалити відгук (адмін) |
| `/api/admin/stats` | GET | Статистика (AJAX фільтр) |

---

##  Безпека

- **CSRF-токен** у кожній формі та AJAX-запиті
- **Bcrypt** (cost=12) для паролів
- **PDO prepared statements** — захист від SQL-ін'єкцій
- **htmlspecialchars** — захист від XSS
- **Session::requireAuth()** — захист сторінок (→ 401)
- **Session::requireAdmin()** — захист адміну (→ 403)
- **FOR UPDATE** у транзакції бронювання — захист від race condition

---

##  Як додати картинки до фільмів і магазину

### Постери фільмів
1. Скопіюйте зображення у `public/images/posters/`
2. Назвіть файл: `dune2.jpg`, `gladiator2.jpg`, тощо
3. В адмін-панелі: **Фільми → Редагувати → поле "Постер (файл)"** — завантажте через форму
   - АБО вручну в БД: `UPDATE movies SET poster='/images/posters/dune2.jpg' WHERE title='Дюна: Частина Друга';`

### Зображення магазину
- Файли вже є у `public/images/shop/`:
  `Cola.png`, `Pepsi.png`, `Orange_juice.png`, `Nachos.png`,
  `Hot-dog.png`, `little-pop-corn.png`, `medium-pop-corn.png`,
  `large-pop-corn.png`, `Combo_1.png`, `Combo_2.png`
- В адмін-панелі: **Магазин → Редагувати товар → поле "Зображення"**
- Або запустіть `sql/migrate_v2.sql` — він автоматично заповнить поле `image`

### Швидко призначити всі постери через SQL
```sql
UPDATE movies SET poster='/images/posters/dune2.jpg'        WHERE title LIKE '%Дюна%';
UPDATE movies SET poster='/images/posters/oppenheimer.jpg'  WHERE title LIKE '%Оппен%';
UPDATE movies SET poster='/images/posters/master.jpg'       WHERE title LIKE '%Майкл%';
UPDATE movies SET poster='/images/posters/gladiator2.jpg'   WHERE title LIKE '%Гладіатор%';
UPDATE movies SET poster='/images/posters/civil_war.jpg'    WHERE title LIKE '%Скайвокера%';
```
Після цього покладіть відповідні JPG/PNG у `public/images/posters/`.

---

*CineMax — курсова робота, 2026*
