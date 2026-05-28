<?php
declare(strict_types=1);



define('DB_HOST',    'localhost');
define('DB_NAME',    'cinema_db');
define('DB_USER',    'root');
define('DB_PASS',    'root');
define('DB_PORT',    8889);
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME',  'CineMax');
define('APP_URL',   'http://localhost:8888/course_work_CINEMA/course_work/public');
define('APP_DEBUG', true);

define('SESSION_NAME',     'cinemax_sess');
define('SESSION_LIFETIME',  7200);

define('CACHE_ENABLED', true);
define('CACHE_TTL',     300);

define('BCRYPT_COST',     12);
define('CSRF_TOKEN_NAME', '_csrf');

define('PER_PAGE', 12);
