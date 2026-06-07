<div class="page-header">
    <h1>Bảng điều khiển - Quản lý hỗ trợ sinh viên</h1>
    <p>Xin chào, <strong><?= h($_SESSION['user_name'] ?? '') ?></strong>
       &nbsp;|&nbsp; Vai trò: <strong><?= h($_SESSION['role'] ?? '') ?></strong>
    </p>
</div>

<div class="grid-4" style="margin-bottom:24px;">
    <div class="card" style="text-align:center;padding:20px;">
        <div style="font-size:32px;font-weight:800;color:#2563eb;"><?= (int)($stats['total'] ?? 0) ?></div>
        <div style="color:#64748b;font-size:14px;margin-top:4px;">Tổng yêu cầu</div>
    </div>
    <div class="card" style="text-align:center;padding:20px;">
        <div style="font-size:32px;font-weight:800;color:#16a34a;"><?= (int)($stats['new'] ?? 0) ?></div>
        <div style="color:#64748b;font-size:14px;margin-top:4px;">Yêu cầu mới</div>
    </div>
    <div class="card" style="text-align:center;padding:20px;">
        <div style="font-size:32px;font-weight:800;color:#dc2626;"><?= (int)($stats['urgent'] ?? 0) ?></div>
        <div style="color:#64748b;font-size:14px;margin-top:4px;">Khẩn cấp</div>
    </div>
    <div class="card" style="text-align:center;padding:20px;">
        <div style="font-size:32px;font-weight:800;color:#7c3aed;"><?= (int)($stats['resolved'] ?? 0) ?></div>
        <div style="color:#64748b;font-size:14px;margin-top:4px;">Đã xử lý</div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <h3 style="margin-top:0;">Thông tin phiên làm việc</h3>
        <div class="info-card" style="margin-bottom:12px;">
            <h4>Người dùng</h4>
            <p><?= h($_SESSION['user_name'] ?? '') ?></p>
            <p style="color:#64748b;">Vai trò: <?= h($_SESSION['role'] ?? '') ?></p>
        </div>
        <div class="info-card" style="margin-bottom:12px;">
            <h4>Trạng thái phiên</h4>
            <p style="color:#16a34a;font-weight:700;">Đang đăng nhập</p>
            <p style="color:#64748b;">Tự động hết phiên sau 15 phút không hoạt động</p>
        </div>
        <div class="info-card">
            <h4>Kiểm tra bảo mật</h4>
            <p>Cookie phiên: <strong>Bảo vệ khỏi truy cập bằng JavaScript</strong></p>
            <p>Yêu cầu từ website khác: <strong>Được giới hạn</strong></p>
            <p>Phiên đăng nhập: <strong>Được làm mới sau khi đăng nhập</strong></p>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-top:0;">Thời gian phiên</h3>
        <div class="info-card" style="margin-bottom:12px;">
            <h4>Đăng nhập lúc</h4>
            <p><?= h(date('d/m/Y H:i:s', $_SESSION['login_at'] ?? time())) ?></p>
        </div>
        <div class="info-card" style="margin-bottom:12px;">
            <h4>Hoạt động cuối</h4>
            <p><?= h(date('d/m/Y H:i:s', $_SESSION['last_activity_at'] ?? time())) ?></p>
        </div>
        <div class="info-card" style="margin-bottom:12px;">
            <h4>Session ID (rút gọn)</h4>
            <p style="font-family:monospace;word-break:break-all;"><?= h(substr(session_id(), 0, 16)) ?>...</p>
        </div>

        <div style="display:flex;gap:10px;margin-top:16px;flex-wrap:wrap;">
            <a href="/session-demo" class="btn btn-secondary btn-sm" target="_blank">Xem Session JSON Debug</a>
            <a href="/tickets" class="btn btn-success btn-sm">Xem yêu cầu hỗ trợ</a>
        </div>

        <hr class="divider">

        <form method="post" action="/logout">
            <button type="submit" class="btn btn-danger">Đăng xuất</button>
        </form>
    </div>
</div>
