<div style="max-width:460px;margin:0 auto;">
    <div class="page-header" style="text-align:center;">
        <h1>Đăng nhập hệ thống</h1>
        <p>Tài khoản demo:<br>
            <strong>admin@school.edu.vn</strong> / <strong>Admin@123</strong><br>
            <strong>staff@school.edu.vn</strong> / <strong>Staff@123</strong>
        </p>
    </div>

    <form method="post" action="/login" class="card" novalidate>
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

        <div class="form-group">
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="<?= h($old['email'] ?? '') ?>"
                placeholder="admin@school.edu.vn"
                class="<?= !empty($errors['email']) ? 'input-error' : '' ?>"
                autocomplete="email"
            >
            <?php if (!empty($errors['email'])): ?>
                <div class="error-text"><?= h($errors['email']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="password">Mật khẩu</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="••••••••"
                class="<?= !empty($errors['password']) ? 'input-error' : '' ?>"
                autocomplete="current-password"
            >
            <?php if (!empty($errors['password'])): ?>
                <div class="error-text"><?= h($errors['password']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label style="font-weight:400;display:flex;align-items:flex-start;gap:8px;cursor:pointer;">
                <input type="checkbox" name="remember_me" value="1" style="width:auto;margin-top:3px;">
                <span>Ghi nhớ đăng nhập (Remember me)</span>
            </label>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;padding:13px;">Đăng nhập</button>

    </form>
</div>
