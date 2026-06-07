# Mini Student Support Request Portal — Lab04

PHP Secure Forms · PRG Pattern · Anti-spam · Session Security · CSRF · Audit Log · Remember Me

---

## Khởi chạy

### Yêu cầu

- PHP >= 8.0
- Composer
- Git

### Cài đặt & chạy

```bash
cd lab04-student-support
composer install
php -S localhost:8000 -t public
```

Mở trình duyệt: `http://localhost:8000/`

### Tài khoản demo

| Email | Mật khẩu | Role |
|---|---|---|
| admin@school.edu.vn | Admin@123 | admin |
| staff@school.edu.vn | Staff@123 | staff |

Mật khẩu được lưu bằng `password_hash(PASSWORD_DEFAULT)` — không lưu plaintext.

---

## Danh sách route

| Method | URL | Chức năng | Bảo vệ |
|---|---|---|---|
| GET | `/` | Trang chủ | — |
| GET | `/tickets` | Danh sách yêu cầu hỗ trợ | — |
| GET | `/tickets/create` | Form gửi yêu cầu | — |
| POST | `/tickets` | Xử lý form (validate + anti-spam + PRG) | CSRF |
| GET | `/login` | Form đăng nhập | — |
| POST | `/login` | Xử lý đăng nhập | CSRF |
| POST | `/logout` | Đăng xuất sạch | CSRF |
| GET | `/dashboard` | Bảng điều khiển | require_login |
| GET | `/session-demo` | Debug session JSON | require_login |
| GET | `/audit-log` | Nhật ký bảo mật | require_login + admin only |
| ANY | URL không tồn tại | 404 Not Found | — |
| Sai method | Route có nhưng method sai | 405 Method Not Allowed | — |

---

## Cấu trúc thư mục

```
lab04-student-support/
├── app/
│   ├── Controllers/
│   │   ├── HomeController.php
│   │   ├── TicketController.php
│   │   ├── AuthController.php
│   │   └── DashboardController.php
│   ├── Core/
│   │   └── Router.php
│   └── Support/
│       └── helpers.php
├── public/
│   ├── index.php                   ← Front Controller
│   └── assets/style.css
├── storage/
│   ├── tickets.json                ← dữ liệu yêu cầu
│   ├── remember_tokens.json        ← token Remember Me (server-side)
│   └── audit.log                   ← nhật ký sự kiện bảo mật
├── views/
│   ├── layout.php
│   ├── home.php
│   ├── dashboard.php
│   ├── audit_log.php
│   ├── tickets/create.php
│   ├── tickets/index.php
│   ├── auth/login.php
│   └── errors/404.php, 405.php
├── docs/
│   ├── problem_solving_cau2.md
│   └── bonus_features_log.md
└── composer.json
```

---

## Tính năng bắt buộc (Câu 1)

| Nhóm | Chi tiết |
|---|---|
| GET / POST | GET chỉ hiển thị, POST gửi dữ liệu — không tạo dữ liệu bằng GET |
| Input safety | `$_POST` + `trim()` + `?? ''` — không tin user input |
| Escape output | Tất cả output qua `h()` / `htmlspecialchars()` — ngăn XSS |
| Server-side validation | required, FILTER_VALIDATE_EMAIL, phone pattern, in-list, length |
| PRG | POST thành công → redirect GET `/tickets` |
| Flash message | Hiện 1 lần sau redirect, tự động xóa |
| Honeypot | Field `website` ẩn bằng CSS — bot điền → bị chặn |
| Rate limit | Session timestamp: không cho submit 2 lần trong 5 giây |
| Session cookie | `HttpOnly=true`, `SameSite=Lax`, `Secure` theo môi trường |
| Login | `session_regenerate_id(true)` sau login — ngăn session fixation |
| Password hash | `password_hash()` / `password_verify()` — không lưu plaintext |
| Dashboard | `require_login()` redirect về `/login` nếu chưa đăng nhập |
| Idle timeout | 15 phút mặc định — xem hướng dẫn T15 bên dưới |
| Logout sạch | `$_SESSION = []` + `session_regenerate_id(true)` + xóa cookie |
| 404 / 405 | Router phân biệt URL không tồn tại vs method sai |

---

## Tính năng làm thêm (Bonus)

| Tính năng | Mô tả |
|---|---|
| HTTP Security Headers | X-Frame-Options, X-Content-Type-Options, Referrer-Policy, CSP |
| CSRF Token | Hidden token trong mọi form POST, verify bằng `hash_equals()` |
| Audit Log | Ghi log `LOGIN_SUCCESS/FAILED`, `LOGOUT`, `HONEYPOT`, `RATE_LIMIT`, `TIMEOUT`, `TICKET_SUBMITTED` |
| Remember Me | Rotating token SHA-256, lưu server-side 30 ngày, không lưu password trong cookie |

---

## Test T15 — Session Timeout

Mở `app/Support/helpers.php`, tìm:

```php
$idleLimit = (int) ($_ENV['SESSION_IDLE_LIMIT'] ?? 900);
```

Đổi `900` thành `6` (6 giây):

```php
$idleLimit = (int) ($_ENV['SESSION_IDLE_LIMIT'] ?? 6);
```

Đăng nhập → chờ 10+ giây không làm gì → vào `/dashboard` → bị redirect kèm flash lỗi. Sau khi test, đổi lại `900`.

---

## Test bằng curl

```bash
# Test honeypot
curl -s -X POST http://localhost:8000/tickets \
  -d "full_name=Test&email=a@b.com&phone=0901234567&support_type=academic&priority=normal&description=Test+description&website=spam"

# Test 404
curl -i http://localhost:8000/khong-ton-tai

# Test 405 (GET /logout chỉ nhận POST)
curl -i http://localhost:8000/logout

# Xem security headers
curl -I http://localhost:8000/
```

---

## Test CSRF (F12 Console)

Thử gửi POST không có CSRF token:

```js
fetch('/tickets', {
  method: 'POST',
  headers: {'Content-Type': 'application/x-www-form-urlencoded'},
  body: 'full_name=Hacker&email=x@x.com&phone=0901234567&support_type=academic&priority=normal&description=test'
}).then(r => r.text()).then(t => console.log(t.substring(0, 100)))
```

Kết quả: `Token bảo mật không hợp lệ. Vui lòng tải lại trang và thử lại.`
