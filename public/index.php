<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\TicketController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;

// -------------------------------------------------------
// Session cookie security params - MUST run before session_start()
// -------------------------------------------------------
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

session_name('SUPPORT_SESSID');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => $isHttps,   // false on local HTTP, true on production HTTPS
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

// Check idle timeout and user-agent context after session starts
check_session_timeout();
check_session_context();

// -------------------------------------------------------
// Router setup
// -------------------------------------------------------
$router = new Router();

// Home
$router->get('/', [HomeController::class, 'index']);

// Support Tickets
$router->get('/tickets',        [TicketController::class, 'index']);
$router->get('/tickets/create', [TicketController::class, 'create']);
$router->post('/tickets',       [TicketController::class, 'store']);

// Auth
$router->get('/login',    [AuthController::class, 'login']);
$router->post('/login',   [AuthController::class, 'handleLogin']);
$router->post('/logout',  [AuthController::class, 'logout']);

// Dashboard (protected)
$router->get('/dashboard',    [DashboardController::class, 'index']);
$router->get('/session-demo', [DashboardController::class, 'sessionDemo']);

// Dispatch
$method = $_SERVER['REQUEST_METHOD'];
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$router->dispatch($method, $path);
