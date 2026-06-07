<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($pageTitle) ? h($pageTitle) . ' - ' : '' ?>Cổng hỗ trợ sinh viên</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<nav class="navbar">
    <span class="brand">🎓 Cổng hỗ trợ sinh viên</span>
    <a href="/" <?= (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/') ? 'class="active"' : '' ?>>Trang chủ</a>
    <a href="/tickets" <?= (strpos($_SERVER['REQUEST_URI'], '/tickets') === 0) ? 'class="active"' : '' ?>>Danh sách yêu cầu</a>
    <a href="/tickets/create">Gửi yêu cầu</a>
    <a href="/dashboard" <?= (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/dashboard') ? 'class="active"' : '' ?>>Bảng điều khiển</a>
    <?php if (is_logged_in()): ?>
        <a href="/session-demo">Demo phiên</a>
        <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
            <a href="/audit-log">Audit Log</a>
        <?php endif; ?>
        <form method="post" action="/logout" class="inline-form">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <button type="submit" class="link-btn">Đăng xuất</button>
        </form>
    <?php else: ?>
        <a href="/login" <?= (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/login') ? 'class="active"' : '' ?>>Đăng nhập</a>
    <?php endif; ?>
</nav>

<main class="container">
    <?php
    $successMsg = flash_get('success');
    $errorMsg   = flash_get('error');
    ?>
    <?php if ($successMsg): ?>
        <div class="alert alert-success"><?= h($successMsg) ?></div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
        <div class="alert alert-error"><?= h($errorMsg) ?></div>
    <?php endif; ?>

    <?php require view_path($view); ?>
</main>

</body>
</html>
