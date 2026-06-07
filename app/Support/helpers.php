<?php

declare(strict_types=1);

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

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

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    $stored    = $_SESSION['csrf_token'] ?? '';
    if ($stored === '' || !hash_equals($stored, $submitted)) {
        http_response_code(419);
        die('Token bảo mật không hợp lệ. Vui lòng tải lại trang và thử lại.');
    }
}

function check_session_timeout(): void
{
    $idleLimit = (int) ($_ENV['SESSION_IDLE_LIMIT'] ?? 900);

    if (!isset($_SESSION['user_id'])) {
        return;
    }

    $last = $_SESSION['last_activity_at'] ?? 0;
    if ($last > 0 && (time() - $last) > $idleLimit) {
        $_SESSION = [];
        session_regenerate_id(true);
        flash_set('error', 'Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
        redirect('/login');
    }

    $_SESSION['last_activity_at'] = time();
}

function check_session_context(): void
{
    if (!isset($_SESSION['user_id'])) {
        return;
    }

    $currentAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $savedAgent   = $_SESSION['user_agent'] ?? '';

    if ($savedAgent !== '' && $savedAgent !== $currentAgent) {
        $_SESSION = [];
        session_regenerate_id(true);
        flash_set('error', 'Phiên có dấu hiệu bất thường. Vui lòng đăng nhập lại.');
        redirect('/login');
    }
}

function view(string $viewName, array $data = []): void
{
    extract($data);
    require __DIR__ . '/../../views/layout.php';
}

function view_path(string $viewName): string
{
    return __DIR__ . '/../../views/' . $viewName . '.php';
}
