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

function audit_log(string $event, array $context = []): void
{
    $logFile = __DIR__ . '/../../storage/audit.log';
    $ip      = $_SERVER['REMOTE_ADDR'] ?? '-';
    $parts   = ['[' . date('Y-m-d H:i:s') . ']', $event, 'ip=' . $ip];
    foreach ($context as $k => $v) {
        $parts[] = $k . '=' . $v;
    }
    file_put_contents($logFile, implode(' ', $parts) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function check_session_timeout(): void
{
    $idleLimit = (int) ($_ENV['SESSION_IDLE_LIMIT'] ?? 900);

    if (!isset($_SESSION['user_id'])) {
        return;
    }

    $last = $_SESSION['last_activity_at'] ?? 0;
    if ($last > 0 && (time() - $last) > $idleLimit) {
        audit_log('SESSION_TIMEOUT', ['user_id' => $_SESSION['user_id'] ?? '-']);
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

function remember_tokens_file(): string
{
    return __DIR__ . '/../../storage/remember_tokens.json';
}

function check_remember_me(): void
{
    if (isset($_SESSION['user_id'])) {
        return;
    }

    $token = $_COOKIE['remember_token'] ?? '';
    if ($token === '') {
        return;
    }

    $file   = remember_tokens_file();
    $tokens = file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];
    $hash   = hash('sha256', $token);
    $matched  = null;
    $matchIdx = -1;

    foreach ($tokens as $i => $record) {
        if (hash_equals($record['token_hash'] ?? '', $hash)) {
            if ($record['expires_at'] > time()) {
                $matched  = $record;
                $matchIdx = $i;
            }
            break;
        }
    }

    if ($matched === null) {
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        $tokens = array_values(array_filter($tokens, fn($r) => ($r['expires_at'] ?? 0) > time()));
        file_put_contents($file, json_encode($tokens, JSON_PRETTY_PRINT));
        return;
    }

    session_regenerate_id(true);
    $_SESSION['user_id']          = $matched['user_id'];
    $_SESSION['user_name']        = $matched['user_name'];
    $_SESSION['role']             = $matched['role'];
    $_SESSION['login_at']         = time();
    $_SESSION['last_activity_at'] = time();
    $_SESSION['user_agent']       = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $newToken          = bin2hex(random_bytes(32));
    $tokens[$matchIdx] = [
        'token_hash' => hash('sha256', $newToken),
        'user_id'    => $matched['user_id'],
        'user_name'  => $matched['user_name'],
        'role'       => $matched['role'],
        'expires_at' => time() + 30 * 24 * 3600,
    ];

    file_put_contents($file, json_encode(array_values($tokens), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    setcookie('remember_token', $newToken, time() + 30 * 24 * 3600, '/', '', false, true);
    audit_log('REMEMBER_ME_LOGIN', ['user_id' => $matched['user_id']]);
}

function set_remember_token(int $userId, string $userName, string $role): void
{
    $token  = bin2hex(random_bytes(32));
    $file   = remember_tokens_file();
    $tokens = file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];

    $tokens = array_values(array_filter($tokens, fn($r) => ($r['user_id'] ?? 0) !== $userId));

    $tokens[] = [
        'token_hash' => hash('sha256', $token),
        'user_id'    => $userId,
        'user_name'  => $userName,
        'role'       => $role,
        'expires_at' => time() + 30 * 24 * 3600,
    ];

    file_put_contents($file, json_encode($tokens, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    setcookie('remember_token', $token, time() + 30 * 24 * 3600, '/', '', false, true);
}

function clear_remember_token(): void
{
    $token = $_COOKIE['remember_token'] ?? '';
    if ($token === '') {
        return;
    }

    $file   = remember_tokens_file();
    $tokens = file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];
    $hash   = hash('sha256', $token);
    $tokens = array_values(array_filter($tokens, fn($r) => !hash_equals($r['token_hash'] ?? '', $hash)));

    file_put_contents($file, json_encode($tokens, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
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
