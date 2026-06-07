<div class="hero">
    <h1>Cổng tiếp nhận yêu cầu hỗ trợ sinh viên</h1>
    <p>Hệ thống tiếp nhận và quản lý yêu cầu hỗ trợ sinh viên - Lab04 PHP Secure Forms, PRG, chống spam và luồng đăng nhập phiên.</p>
    <a href="/tickets/create" class="btn btn-primary" style="margin-right:10px;">Gửi yêu cầu hỗ trợ</a>
    <a href="/tickets" class="btn" style="background:rgba(255,255,255,.2);color:#fff;">Xem danh sách</a>
</div>

<div class="grid-4" style="margin-bottom:28px;">
    <div class="feature-card">
        <div class="feature-icon" style="background:#2563eb;">1</div>
        <h3>Biểu mẫu an toàn</h3>
        <p>Đọc dữ liệu nhập an toàn, escape output, giữ dữ liệu cũ khi có lỗi.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon" style="background:#16a34a;">2</div>
        <h3>Kiểm tra dữ liệu + PRG</h3>
        <p>Kiểm tra phía server, báo lỗi tạm thời, chuyển hướng để tránh gửi trùng.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon" style="background:#d97706;">3</div>
        <h3>Chống spam</h3>
        <p>Honeypot và giới hạn tần suất đơn giản bằng session.</p>
    </div>
    <div class="feature-card">
        <div class="feature-icon" style="background:#7c3aed;">4</div>
        <h3>Đăng nhập/Phiên</h3>
        <p>Cấu hình cookie an toàn, tạo lại session ID, timeout và đăng xuất sạch.</p>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <h3 style="margin-top:0;">Các tính năng chính</h3>
        <ul style="margin:0;padding-left:20px;line-height:2;">
            <li>Form gửi yêu cầu hỗ trợ sinh viên</li>
            <li>Kiểm tra dữ liệu phía server đầy đủ (bắt buộc, email, số điện thoại, danh sách hợp lệ, độ dài)</li>
            <li>PRG - chuyển hướng sau khi POST thành công</li>
            <li>Thông báo flash chỉ hiển thị một lần sau chuyển hướng</li>
            <li>Honeypot và giới hạn tần suất để chống spam</li>
            <li>Lưu dữ liệu JSON, không cần database</li>
        </ul>
    </div>
    <div class="card">
        <h3 style="margin-top:0;">Các route có sẵn</h3>
        <table style="width:100%;font-size:13px;border-collapse:collapse;">
            <tr><td style="padding:4px 8px;"><code>GET /</code></td><td style="padding:4px 8px;color:#64748b;">Trang chủ</td></tr>
            <tr><td style="padding:4px 8px;"><code>GET /tickets</code></td><td style="padding:4px 8px;color:#64748b;">Danh sách yêu cầu</td></tr>
            <tr><td style="padding:4px 8px;"><code>GET /tickets/create</code></td><td style="padding:4px 8px;color:#64748b;">Form gửi yêu cầu</td></tr>
            <tr><td style="padding:4px 8px;"><code>POST /tickets</code></td><td style="padding:4px 8px;color:#64748b;">Xử lý gửi yêu cầu</td></tr>
            <tr><td style="padding:4px 8px;"><code>GET /login</code></td><td style="padding:4px 8px;color:#64748b;">Form đăng nhập</td></tr>
            <tr><td style="padding:4px 8px;"><code>POST /login</code></td><td style="padding:4px 8px;color:#64748b;">Xử lý đăng nhập</td></tr>
            <tr><td style="padding:4px 8px;"><code>POST /logout</code></td><td style="padding:4px 8px;color:#64748b;">Đăng xuất</td></tr>
            <tr><td style="padding:4px 8px;"><code>GET /dashboard</code></td><td style="padding:4px 8px;color:#64748b;">Bảng điều khiển (cần đăng nhập)</td></tr>
            <tr><td style="padding:4px 8px;"><code>GET /session-demo</code></td><td style="padding:4px 8px;color:#64748b;">Debug session dạng JSON</td></tr>
        </table>
    </div>
</div>
