<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\TicketController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'");

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

session_name('SUPPORT_SESSID');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

check_session_timeout();
check_session_context();

$router = new Router();

$router->get('/', [HomeController::class, 'index']);

$router->get('/tickets',        [TicketController::class, 'index']);
$router->get('/tickets/create', [TicketController::class, 'create']);
$router->post('/tickets',       [TicketController::class, 'store']);

$router->get('/login',    [AuthController::class, 'login']);
$router->post('/login',   [AuthController::class, 'handleLogin']);
$router->post('/logout',  [AuthController::class, 'logout']);

$router->get('/dashboard',    [DashboardController::class, 'index']);
$router->get('/session-demo', [DashboardController::class, 'sessionDemo']);
$router->get('/audit-log',    [DashboardController::class, 'auditLog']);

$method = $_SERVER['REQUEST_METHOD'];
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$router->dispatch($method, $path);
