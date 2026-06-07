<?php

declare(strict_types=1);

// -------------------------------------------------------
// Output escaping
// -------------------------------------------------------
function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// -------------------------------------------------------
// Redirect
// -------------------------------------------------------
function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

// -------------------------------------------------------
// Flash messages (one-time session values)
// -------------------------------------------------------
function flash_set(string $key, mixed $value): void
{
    $_SESSION['_flash'][$key] = $value;
}

function flash_get(string $key, mixed $default = null): mixed
{
    $value = $_SESSION['_flash'][$key] ?? $default;
    unset($_SESSION['_flash'][$key]);
    return $value;
}

// -------------------------------------------------------
// Auth helpers
// -------------------------------------------------------
function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash_set('error', 'Vui lòng đăng nhập để truy cập trang này.');
        redirect('/login');
    }
}

// -------------------------------------------------------
// Session timeout (idle-based)
// Adjust IDLE_LIMIT for demo: default 15 minutes; set 60 sec for T15 test
// -------------------------------------------------------
function check_session_timeout(): void
{
    $idleLimit = (int) ($_ENV['SESSION_IDLE_LIMIT'] ?? 900); // 900 = 15 minutes

    if (!isset($_SESSION['user_id'])) {
        return;
    }

    $last = $_SESSION['last_activity_at'] ?? time();
    if (time() - $last > $idleLimit) {
        logout_clean();
        session_start();
        flash_set('error', 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
        redirect('/login');
    }

    $_SESSION['last_activity_at'] = time();
}

// -------------------------------------------------------
// Basic user-agent context check (demo-level session binding)
// -------------------------------------------------------
function check_session_context(): void
{
    if (!isset($_SESSION['user_id'])) {
        return;
    }

    $currentAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $savedAgent   = $_SESSION['user_agent'] ?? '';

    if ($savedAgent !== '' && $savedAgent !== $currentAgent) {
        logout_clean();
        session_start();
        flash_set('error', 'Phiên có dấu hiệu bất thường. Vui lòng đăng nhập lại.');
        redirect('/login');
    }
}

// -------------------------------------------------------
// Clean logout: wipe session data, destroy session, clear cookie
// -------------------------------------------------------
function logout_clean(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

// -------------------------------------------------------
// View renderer
// -------------------------------------------------------
function view(string $viewName, array $data = []): void
{
    extract($data);
    require __DIR__ . '/../../views/layout.php';
}

function view_path(string $viewName): string
{
    return __DIR__ . '/../../views/' . $viewName . '.php';
}
