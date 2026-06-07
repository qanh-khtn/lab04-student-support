<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($pageTitle) ? h($pageTitle) . ' - ' : '' ?>Cổng hỗ trợ sinh viên</title>
    <link rel="stylesheet" href="/assets/style.css">
    <script>(function(){var t=localStorage.getItem('theme')||(window.matchMedia&&window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t);}());</script>
</head>
<body>

<nav class="navbar">
    <span class="brand">🎓 Cổng hỗ trợ sinh viên</span>
    <a href="/" <?= (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) === '/') ? 'class="active"' : '' ?>>Trang chủ</a>
    <?php $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>
    <a href="/tickets" <?= ($currentPath === '/tickets') ? 'class="active"' : '' ?>>Danh sách yêu cầu</a>
    <a href="/tickets/create" <?= ($currentPath === '/tickets/create') ? 'class="active"' : '' ?>>Gửi yêu cầu</a>
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


<button id="theme-toggle" class="theme-toggle" aria-label="Chuyển giao diện sáng/tối" title="Chuyển giao diện">
    <span class="theme-toggle-inner">
        <span class="toggle-sun">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"/>
                <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
        </span>
        <span class="toggle-moon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
        </span>
    </span>
</button>

<script>
(function () {
    var root = document.documentElement;
    var btn  = document.getElementById('theme-toggle');
    if (btn) {
        btn.addEventListener('click', function () {
            var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            document.body.classList.add('theme-transitioning');
            root.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
            setTimeout(function () { document.body.classList.remove('theme-transitioning'); }, 350);
        });
    }
}());
</script>
</body>
</html>
