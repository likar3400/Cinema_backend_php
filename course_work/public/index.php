<?php
declare(strict_types=1);
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH',  ROOT_PATH . '/app');
define('VIEW_PATH', APP_PATH  . '/Views');
define('CACHE_PATH',ROOT_PATH . '/cache');

require ROOT_PATH . '/config/config.php';
require APP_PATH  . '/Core/Autoloader.php';

use App\Core\Session;
use App\Core\PageBuffer;
use App\Core\Http\Request;
use App\Core\Http\Response;


set_exception_handler(function (\Throwable $e) {
    Response::status(500);
    Response::noCache();
    if (APP_DEBUG) {
        echo '<pre style="background:#1e1e1e;color:#f8f8f2;padding:20px;margin:0;font-size:13px">';
        echo '<b>'.get_class($e).'</b>: '.htmlspecialchars($e->getMessage())."\n";
        echo 'File: '.htmlspecialchars($e->getFile()).' line '.$e->getLine()."\n\n";
        echo htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    } else {
        require VIEW_PATH . '/error/500.php';
    }
});

set_error_handler(function (int $s, string $m, string $f, int $l): bool {
    if (!(error_reporting() & $s)) return false;
    throw new \ErrorException($m, 0, $s, $f, $l);
});


Session::start();
PageBuffer::start();


$request = new Request();
$router  = require APP_PATH . '/routes.php';
$router->dispatch($request);
$code     = Response::getStatus();
$method   = $request->method();
$cacheKey = '';

if ($code === 200 && $method === 'GET' && !Session::isLoggedIn()) {
    $cacheKey = 'pg_'.md5($request->uri().'?'.($_SERVER['QUERY_STRING'] ?? ''));
}

PageBuffer::flush($cacheKey);
