
USE cinema_db;

CREATE TABLE IF NOT EXISTS movie_categories (
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO movie_categories (name) VALUES
('Екшн'),('Драма'),('Фантастика'),('Комедія'),('Трилер'),('Анімація'),('Документальний');
ALTER TABLE movies
    ADD COLUMN IF NOT EXISTS category_id INT UNSIGNED DEFAULT NULL AFTER genre,
    ADD CONSTRAINT IF NOT EXISTS fk_movie_cat
        FOREIGN KEY (category_id) REFERENCES movie_categories(id) ON DELETE SET NULL;


CREATE TABLE IF NOT EXISTS cart (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    item_id    INT UNSIGNED NOT NULL,
    qty        TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_item(user_id, item_id),
    CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id)       ON DELETE CASCADE,
    CONSTRAINT fk_cart_item FOREIGN KEY (item_id) REFERENCES shop_items(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reviews (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    movie_id   INT UNSIGNED NOT NULL,
    rating     TINYINT UNSIGNED NOT NULL,
    body       TEXT NOT NULL,
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_movie(user_id, movie_id),
    INDEX idx_movie(movie_id),
    CONSTRAINT fk_rev_user  FOREIGN KEY (user_id)  REFERENCES users(id)   ON DELETE CASCADE,
    CONSTRAINT fk_rev_movie FOREIGN KEY (movie_id) REFERENCES movies(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

UPDATE users SET password='$2b$12$HPAoJjt5yAQV8x4MoMKMrO.8DYGdi2O8CgyoKhaJwQc9RCfb0m6iu'
WHERE email='admin@cinema.ua';

UPDATE movies SET title='Майкл: Байопік',
    description='Офіційна біографічна драма про Короля поп-музики Майкла Джексона. Його зліт, слава та приватне життя.',
    genre='Драма/Музика',
    release_date='2026-06-20'
WHERE title IN ('Майстер і Маргарита','Майстер та Маргарита');

UPDATE movies SET title='Зоряні Війни: Сходження Скайвокера',
    description='Фінал саги Скайвокерів. Дейзі Рідлі, Адам Драйвер — остання битва між Сторонами Сили.',
    genre='Фантастика/Пригоди',
    release_date='2026-07-04'
WHERE title IN ('Падіння Імперії','Зоряні Війни');

UPDATE sessions SET
    starts_at = DATE_ADD(starts_at, INTERVAL
        TIMESTAMPDIFF(YEAR, starts_at, '2026-06-15') YEAR),
    ends_at   = DATE_ADD(ends_at, INTERVAL
        TIMESTAMPDIFF(YEAR, ends_at, '2026-06-15') YEAR)
WHERE YEAR(starts_at) < 2026;
UPDATE shop_items SET image='/images/shop/little-pop-corn.png'  WHERE name LIKE '%малий%'  AND (image IS NULL OR image='');
UPDATE shop_items SET image='/images/shop/medium-pop-corn.png'  WHERE name LIKE '%середн%' AND (image IS NULL OR image='');
UPDATE shop_items SET image='/images/shop/large-pop-corn.png'   WHERE name LIKE '%велик%'  AND (image IS NULL OR image='');
UPDATE shop_items SET image='/images/shop/Cola.png'             WHERE name LIKE '%Кола%'   AND (image IS NULL OR image='');
UPDATE shop_items SET image='/images/shop/Pepsi.png'            WHERE name LIKE '%Пепсі%'  AND (image IS NULL OR image='');
UPDATE shop_items SET image='/images/shop/Orange_juice.png'     WHERE name LIKE '%Сік%'    AND (image IS NULL OR image='');
UPDATE shop_items SET image='/images/shop/Nachos.png'           WHERE name LIKE '%Начос%'  AND (image IS NULL OR image='');
UPDATE shop_items SET image='/images/shop/Hot-dog.png'          WHERE name LIKE '%Хот%'    AND (image IS NULL OR image='');
UPDATE shop_items SET image='/images/shop/Combo_1.png'          WHERE name LIKE '%Стандарт%' AND category='combo' AND (image IS NULL OR image='');
UPDATE shop_items SET image='/images/shop/Combo_2.png'          WHERE name LIKE '%VIP%'    AND category='combo' AND (image IS NULL OR image='');

SELECT 'Migration v2 completed ✓' AS status;
