<div style="text-align:center;padding:60px 20px;">
    <div style="font-size:80px;font-weight:800;color:#fef9c3;">405</div>
    <h1 style="color:#374151;margin:0 0 12px;">Phương thức không được phép</h1>
    <p style="color:#64748b;margin:0 0 12px;">URL này tồn tại nhưng HTTP method bạn sử dụng không được phép.</p>
    <?php if (!empty($allowed)): ?>
        <p style="color:#64748b;">Method được chấp nhận: <strong><?= h($allowed) ?></strong></p>
    <?php endif; ?>
    <div style="margin-top:24px;">
        <a href="/" class="btn btn-primary">Về trang chủ</a>
    </div>
    <hr class="divider" style="max-width:400px;margin:30px auto;">
    <p style="font-size:13px;color:#94a3b8;">
        <strong>405 Method Not Allowed</strong>: Route được đăng ký nhưng method sai.<br>
        Ví dụ: truy cập <code>GET /logout</code> trong khi chỉ có <code>POST /logout</code>.<br>
        (Khác với 404 Not Found - URL không tồn tại)
    </p>
</div>
