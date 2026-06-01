SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS cinema_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cinema_db;
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(200) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    phone VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email(email), INDEX idx_role(role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO users (name, email, password, role) VALUES
('Адміністратор','admin@cinema.ua','$2b$12$HPAoJjt5yAQV8x4MoMKMrO.8DYGdi2O8CgyoKhaJwQc9RCfb0m6iu','admin'),

CREATE TABLE IF NOT EXISTS movie_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO movie_categories (name) VALUES
('Екшн'),('Драма'),('Фантастика'),('Комедія'),('Трилер'),('Анімація'),('Документальний');

CREATE TABLE IF NOT EXISTS movies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    genre VARCHAR(100),
    category_id INT UNSIGNED DEFAULT NULL,
    duration SMALLINT UNSIGNED NOT NULL DEFAULT 90,
    rating DECIMAL(3,1) DEFAULT NULL,
    age_rating VARCHAR(6) DEFAULT '0+',
    poster VARCHAR(300) DEFAULT NULL,
    trailer_url VARCHAR(500) DEFAULT NULL,
    release_date DATE DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active(is_active),
    CONSTRAINT fk_movie_cat FOREIGN KEY (category_id) REFERENCES movie_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Фільми — літо 2026
INSERT INTO movies (title,description,genre,duration,rating,age_rating,release_date,poster) VALUES
('Дюна: Частина Друга','Пол Атрейдес веде армію фрименів проти змовників на Арракісі. Епічна сага про долю, владу та любов.','Наукова фантастика',166,8.8,'12+','2026-06-05','/images/posters/dune2.jpg'),
('Оппенгеймер','Історія батька атомної бомби — Роберта Оппенгеймера. Кристофер Нолан, Кілліан Мерфі.','Драма/Біографія',180,8.9,'16+','2026-06-10','/images/posters/oppenheimer.jpg'),
('Майкл: Байопік','Офіційна біографічна драма про Короля поп-музики Майкла Джексона. Його зліт, слава та приватне життя.','Драма/Музика',148,8.2,'12+','2026-06-20','/images/posters/master.jpg'),
('Зоряні Війни: Сходження Скайвокера','Фінал саги Скайвокерів. Дейзі Рідлі, Адам Драйвер — остання битва між Сторонами Сили.','Фантастика/Пригоди',142,7.1,'6+','2026-07-04','/images/posters/civil_war.jpg'),
('Гладіатор 2','Рідлі Скотт повертається до Риму. Новий герой на арені Колізею. Пол Мескал, Деніел Крейг.','Бойовик/Пригоди',148,7.4,'16+','2026-07-12','/images/posters/gladiator2.jpg');

CREATE TABLE IF NOT EXISTS halls (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    row_count TINYINT UNSIGNED NOT NULL,
    col_count TINYINT UNSIGNED NOT NULL,
    type ENUM('standard','vip','imax') NOT NULL DEFAULT 'standard',
    is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO halls (name,row_count,col_count,type) VALUES
('Зала 1 — IMAX',8,12,'imax'),
('Зала 2 — VIP',6,10,'vip'),
('Зала 3 — Стандарт',10,14,'standard');

CREATE TABLE IF NOT EXISTS seats (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hall_id INT UNSIGNED NOT NULL,
    row_num TINYINT UNSIGNED NOT NULL,
    col_num TINYINT UNSIGNED NOT NULL,
    type ENUM('standard','vip','disabled') NOT NULL DEFAULT 'standard',
    UNIQUE KEY uq_seat(hall_id,row_num,col_num),
    CONSTRAINT fk_seat_hall FOREIGN KEY (hall_id) REFERENCES halls(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO seats (hall_id,row_num,col_num,type)
SELECT h.id,r.n,c.n,
    CASE WHEN r.n<=2 THEN 'vip'
         WHEN r.n=h.row_count AND (c.n=1 OR c.n=h.col_count) THEN 'disabled'
         ELSE 'standard' END
FROM halls h
JOIN (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
      UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) r ON r.n<=h.row_count
JOIN (SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
      UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10
      UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14) c ON c.n<=h.col_count
ORDER BY h.id,r.n,c.n;

CREATE TABLE IF NOT EXISTS sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    movie_id INT UNSIGNED NOT NULL,
    hall_id INT UNSIGNED NOT NULL,
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NOT NULL,
    price DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    price_vip DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    language ENUM('uk','en','dub') NOT NULL DEFAULT 'uk',
    format ENUM('2D','3D','IMAX','4DX') NOT NULL DEFAULT '2D',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_movie(movie_id), INDEX idx_hall(hall_id), INDEX idx_starts(starts_at),
    CONSTRAINT fk_sess_movie FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    CONSTRAINT fk_sess_hall  FOREIGN KEY (hall_id)  REFERENCES halls(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO sessions (movie_id,hall_id,starts_at,ends_at,price,price_vip,language,format) VALUES

(1,1,'2026-06-06 10:00:00','2026-06-06 12:46:00',320.00,0,'uk','IMAX'),
(1,3,'2026-06-07 15:00:00','2026-06-07 17:46:00',180.00,0,'uk','2D'),
(1,2,'2026-06-14 11:00:00','2026-06-14 13:46:00',280.00,500.00,'uk','2D'),
(1,1,'2026-07-01 21:00:00','2026-07-01 23:46:00',340.00,0,'uk','IMAX'),

(2,2,'2026-06-11 12:00:00','2026-06-11 15:00:00',250.00,450.00,'dub','2D'),
(2,1,'2026-06-20 19:00:00','2026-06-20 22:00:00',350.00,0,'dub','IMAX'),
(2,3,'2026-07-05 14:00:00','2026-07-05 17:00:00',200.00,0,'uk','2D'),

(3,3,'2026-06-21 18:00:00','2026-06-21 20:28:00',200.00,0,'uk','2D'),
(3,2,'2026-07-06 20:00:00','2026-07-06 22:28:00',260.00,480.00,'dub','2D'),
(3,1,'2026-07-20 17:00:00','2026-07-20 19:28:00',300.00,0,'dub','IMAX'),

(4,1,'2026-07-05 10:00:00','2026-07-05 12:22:00',280.00,0,'uk','IMAX'),
(4,3,'2026-07-12 16:00:00','2026-07-12 18:22:00',190.00,0,'uk','2D'),
(4,2,'2026-08-01 14:00:00','2026-08-01 16:22:00',220.00,400.00,'dub','2D'),

(5,2,'2026-07-13 14:00:00','2026-07-13 16:28:00',300.00,550.00,'dub','2D'),
(5,3,'2026-07-19 16:00:00','2026-07-19 18:28:00',200.00,0,'uk','2D'),
(5,1,'2026-08-10 19:00:00','2026-08-10 21:28:00',360.00,0,'dub','IMAX');


CREATE TABLE IF NOT EXISTS bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    session_id INT UNSIGNED NOT NULL,
    seat_id INT UNSIGNED NOT NULL,
    status ENUM('confirmed','cancelled') NOT NULL DEFAULT 'confirmed',
    price_paid DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    ticket_code VARCHAR(12) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_session_seat(session_id,seat_id),  -- запобігає подвійному бронюванню
    INDEX idx_user(user_id), INDEX idx_session(session_id),
    CONSTRAINT fk_book_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_book_session FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
    CONSTRAINT fk_book_seat    FOREIGN KEY (seat_id)    REFERENCES seats(id)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-
CREATE TABLE IF NOT EXISTS news (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    image VARCHAR(300) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO news (title,body) VALUES
('Відкриття нового IMAX-залу!','Раді повідомити про відкриття оновленого IMAX-залу з найкращою акустикою та 4K-екраном!'),
('Акція: знижка 20% щовівторка','Кожен вівторок — день знижок! Купуйте квитки зі знижкою 20% на всі сеанси.'),
('VIP-зала з сервісом попкорну','У нашій VIP-залі тепер доступний преміум-сервіс: попкорн та напої до місця!'),
('Літній кінофестиваль 2026','Цього літа CineMax проводить спеціальний кінофестиваль! Кращі фільми сезону щодня.'),
('Гладіатор 2 у IMAX!','Рідлі Скотт повертається! Дивіться найгучніший блокбастер сезону у форматі IMAX.');


CREATE TABLE IF NOT EXISTS shop_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    description VARCHAR(300) DEFAULT NULL,
    price DECIMAL(8,2) NOT NULL,
    category ENUM('popcorn','drink','snack','combo') NOT NULL DEFAULT 'popcorn',
    image VARCHAR(300) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO shop_items (name,description,price,category,image) VALUES
('Попкорн малий','Класичний солоний попкорн 200г',55.00,'popcorn','/images/shop/little-pop-corn.png'),
('Попкорн середній','Класичний або карамельний попкорн 350г',85.00,'popcorn','/images/shop/medium-pop-corn.png'),
('Попкорн великий','Великий відерко попкорну 500г',115.00,'popcorn','/images/shop/large-pop-corn.png'),
('Кола 0.5л','Coca-Cola з льодом',65.00,'drink','/images/shop/Cola.png'),
('Пепсі 0.5л','Pepsi з льодом',65.00,'drink','/images/shop/Pepsi.png'),
('Сік апельсиновий','Свіжовичавлений апельсиновий сік',75.00,'drink','/images/shop/Orange_juice.png'),
('Начос з соусом','Хрусткі начос із сальсою та сиром',90.00,'snack','/images/shop/Nachos.png'),
('Хот-дог','Класичний хот-дог з гірчицею',95.00,'snack','/images/shop/Hot-dog.png'),
('Комбо Стандарт','Попкорн середній + Кола 0.5л',135.00,'combo','/images/shop/Combo_1.png'),
('Комбо VIP','Попкорн великий + Кола 1л + Начос',280.00,'combo','/images/shop/Combo_2.png');


CREATE TABLE IF NOT EXISTS cart (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    qty TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_item(user_id,item_id),
    CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_item FOREIGN KEY (item_id) REFERENCES shop_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Shop orders ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS shop_orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    booking_id INT UNSIGNED DEFAULT NULL,
    total DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    status ENUM('pending','paid','cancelled') NOT NULL DEFAULT 'paid',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user(user_id),
    CONSTRAINT fk_order_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_order_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS shop_order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    item_id INT UNSIGNED NOT NULL,
    qty TINYINT UNSIGNED NOT NULL DEFAULT 1,
    price DECIMAL(8,2) NOT NULL,
    CONSTRAINT fk_oi_order FOREIGN KEY (order_id) REFERENCES shop_orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_oi_item  FOREIGN KEY (item_id)  REFERENCES shop_items(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    movie_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL CHECK(rating BETWEEN 1 AND 10),
    body TEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_movie(user_id,movie_id), 
    INDEX idx_movie(movie_id),
    CONSTRAINT fk_rev_user  FOREIGN KEY (user_id)  REFERENCES users(id)   ON DELETE CASCADE,
    CONSTRAINT fk_rev_movie FOREIGN KEY (movie_id) REFERENCES movies(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
