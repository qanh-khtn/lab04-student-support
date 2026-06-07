# Phần làm thêm — Lab04 Student Support Portal

## Tổng quan

Ngoài các yêu cầu bắt buộc của Câu 1, đã triển khai thêm 4 tính năng bảo mật nâng cao.
Mỗi tính năng được commit riêng với message rõ ràng.

---

## 1. HTTP Security Response Headers

**Commit:** `feat: add HTTP security headers (X-Frame-Options, CSP, nosniff, Referrer-Policy)`

**File thay đổi:** `public/index.php`

**Code thêm vào:**
```php
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline'");
```

**Ý nghĩa từng header:**

| Header | Giá trị | Bảo vệ khỏi |
|--------|---------|-------------|
| X-Frame-Options | DENY | Clickjacking — ngăn site bị nhúng vào iframe |
| X-Content-Type-Options | nosniff | MIME sniffing — trình duyệt không tự đoán content type |
| Referrer-Policy | strict-origin-when-cross-origin | Rò rỉ URL khi chuyển hướng sang site khác |
| Content-Security-Policy | default-src 'self' | XSS — chỉ load tài nguyên từ cùng origin |

**Cách chụp màn hình:**
1. Mở bất kỳ trang nào (VD: `http://localhost:8000/`)
2. F12 → tab **Network** → click vào request đầu tiên (tên `/`)
3. Tab **Headers** → kéo xuống phần **Response Headers**
4. Chụp thấy: `content-security-policy`, `referrer-policy`, `x-content-type-options`, `x-frame-options`

---

## 2. CSRF Token Protection

**Commit:** `feat: add CSRF token protection to all POST forms and handlers`

**Files thay đổi:** `helpers.php`, `views/tickets/create.php`, `views/auth/login.php`, `views/layout.php`, `views/dashboard.php`, `AuthController.php`, `TicketController.php`

**Cơ chế:**
- Khi session bắt đầu, `csrf_token()` tạo token 64-byte ngẫu nhiên (`bin2hex(random_bytes(32))`) lưu vào `$_SESSION['csrf_token']`
- Mỗi form POST chứa hidden field: `<input type="hidden" name="csrf_token" value="...">`
- Đầu mỗi POST handler gọi `csrf_verify()` → dùng `hash_equals()` để so sánh an toàn (chống timing attack)
- Nếu token không khớp → HTTP 419, không xử lý request

**Cách chụp màn hình:**

*Ảnh 1 — Token trong form source:*
1. Vào `/tickets/create`
2. Chuột phải → **View Page Source** (hoặc F12 → Elements)
3. Tìm `<input type="hidden" name="csrf_token"`
4. Chụp thấy value là chuỗi hex 64 ký tự

*Ảnh 2 — Chặn request giả mạo:*
1. F12 → Console → chạy:
```js
fetch('/tickets', {
  method: 'POST',
  headers: {'Content-Type': 'application/x-www-form-urlencoded'},
  body: 'full_name=Hacker&email=x@x.com&phone=0901234567&support_type=academic&priority=normal&description=test+spam+request'
})
.then(r => r.text()).then(t => console.log(t.substring(0, 200)))
```
2. Chụp Console hiện: **"Token bảo mật không hợp lệ. Vui lòng tải lại trang và thử lại."**

---

## 3. Audit Log

**Commit:** `feat: add audit log for security events (login, logout, honeypot, rate-limit, timeout)`

**Files thay đổi:** `helpers.php`, `AuthController.php`, `TicketController.php`, `DashboardController.php`, `views/audit_log.php`, `public/index.php`

**Các sự kiện được ghi lại:**

| Event | Khi nào |
|-------|---------|
| `LOGIN_SUCCESS` | Đăng nhập thành công |
| `LOGIN_FAILED` | Sai email/mật khẩu |
| `LOGOUT` | Nhấn Đăng xuất |
| `SESSION_TIMEOUT` | Phiên hết hạn do idle |
| `HONEYPOT_TRIGGERED` | Bot điền field ẩn |
| `RATE_LIMIT_BLOCKED` | Gửi form quá nhanh |
| `TICKET_SUBMITTED` | Gửi yêu cầu hỗ trợ thành công |
| `REMEMBER_ME_LOGIN` | Auto-login qua cookie Remember Me |

**Format log:**
```
[2026-06-07 14:30:15] LOGIN_SUCCESS ip=127.0.0.1 user_id=1 email=admin@school.edu.vn
[2026-06-07 14:35:02] HONEYPOT_TRIGGERED ip=127.0.0.1
[2026-06-07 14:35:20] RATE_LIMIT_BLOCKED ip=127.0.0.1 email=test@test.com
```

**Cách chụp màn hình:**

*Ảnh 1 — Trang Audit Log:*
1. Đăng nhập bằng `admin@school.edu.vn`
2. Thực hiện vài thao tác: login thất bại 1 lần, honeypot, submit form
3. Vào `http://localhost:8000/audit-log`
4. Chụp trang hiện danh sách log với màu sắc phân loại (xanh = thành công, đỏ = bị chặn, vàng = logout)

*Ảnh 2 — File log trực tiếp:*
- Mở `storage/audit.log` trong IDE/editor
- Chụp nội dung file thô

---

## 4. Remember Me — Rotating Token

**Commit:** `feat: implement Remember Me with rotating token stored server-side (30-day cookie)`

**Files thay đổi:** `helpers.php`, `AuthController.php`, `public/index.php`

**Cơ chế an toàn (không lưu password trong cookie):**
1. User tích "Ghi nhớ đăng nhập" → server tạo token ngẫu nhiên 64-byte
2. Server lưu `SHA-256(token)` vào `storage/remember_tokens.json` kèm `user_id`, `expires_at` (30 ngày)
3. Browser nhận cookie `remember_token=<raw_token>` httponly, 30 ngày
4. Lần sau vào site: server đọc cookie → hash → tra JSON → nếu khớp và chưa hết hạn → auto-login
5. **Rotating token:** mỗi lần dùng, token cũ bị xóa và tạo token mới (giảm rủi ro token bị đánh cắp)
6. Logout: xóa token khỏi JSON + expire cookie ngay lập tức

**Cách chụp màn hình:**

*Ảnh 1 — Đăng nhập với Remember Me:*
1. Vào `/login` → tích checkbox "Ghi nhớ đăng nhập" → đăng nhập
2. Chụp flash xanh: "Đã lưu Ghi nhớ đăng nhập — token an toàn 30 ngày, không lưu mật khẩu trong cookie"

*Ảnh 2 — Cookie được set:*
1. F12 → tab **Application** (Chrome) hoặc **Storage** (Firefox)
2. Cookies → `http://localhost:8000`
3. Chụp thấy cookie `remember_token` với Expires ~30 ngày, **HttpOnly = ✓**

*Ảnh 3 — Token file server-side:*
- Mở `storage/remember_tokens.json`
- Chụp thấy `token_hash` (SHA-256), không phải raw token hay password

*Ảnh 4 — Auto-login hoạt động:*
1. Sau khi có Remember Me cookie, đóng tab/trình duyệt
2. Mở lại `http://localhost:8000/dashboard`
3. Chụp: tự động vào dashboard mà không cần đăng nhập lại

---

## Tổng kết git log

```
feat: implement Remember Me with rotating token stored server-side (30-day cookie)
feat: add audit log for security events (login, logout, honeypot, rate-limit, timeout)
feat: add CSRF token protection to all POST forms and handlers
feat: add HTTP security headers (X-Frame-Options, CSP, nosniff, Referrer-Policy)
```

Mỗi commit độc lập, không trộn lẫn nhiều tính năng.
