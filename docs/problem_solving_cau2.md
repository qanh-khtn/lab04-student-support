# Câu 2 — Problem Solving
## Mini Student Support Request Portal — Lab04 PHP

---

## 1. Vì sao server-side validation là bắt buộc?

Client-side validation (HTML `required`, `type="email"`) chỉ chạy trên trình duyệt. Bất kỳ ai cũng có thể:
- Mở DevTools → xóa `required` attribute → submit form rỗng
- Dùng curl/Postman gửi thẳng đến server, bỏ qua hoàn toàn HTML form

Khi gửi `curl -X POST http://localhost:8000/tickets -d "full_name=&email=abc@"`, server xử lý trong `TicketController::validate()` và trả lỗi từng field — không có record nào được lưu.

Ví dụ field `email`:
```php
if ($data['email'] === '') {
    $errors['email'] = 'Vui lòng nhập email.';
} elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Email không đúng định dạng.';
}
```

`FILTER_VALIDATE_EMAIL` là PHP built-in, không thể bị bypass từ client.

---

## 2. Vì sao phải phân biệt GET và POST?

| Method | Route | Lý do |
|--------|-------|-------|
| GET | `/`, `/tickets`, `/tickets/create`, `/login`, `/dashboard` | Chỉ đọc và hiển thị |
| POST | `/tickets`, `/login`, `/logout` | Gửi/thay đổi dữ liệu |

GET parameters hiển thị trên URL → lộ email, phone trong browser history và server log. Nếu logout là GET, một img tag hay prefetch trình duyệt trỏ đến `/logout` có thể logout người dùng mà không cần click. `Router::dispatch()` trong `app/Core/Router.php` trả 405 khi gọi sai method.

---

## 3. PRG giải quyết vấn đề gì?

Nếu POST xong render trực tiếp: nhấn F5 → trình duyệt hỏi "Resubmit?" → có thể tạo ticket trùng.

Trong bài:
```php
// TicketController::store()
$this->saveTicket($data);
flash_set('success', '...');
redirect('/tickets');  // POST → Redirect → GET
```

Refresh GET `/tickets` chỉ tải lại trang, không gửi POST. Test T05 xác nhận: submit 1 lần → refresh 5 lần → số ticket không tăng thêm.

---

## 4. Validation và anti-spam khác nhau như thế nào?

**Validation** kiểm tra "dữ liệu đúng định dạng": email hợp lệ không, support_type có trong danh sách không, description đủ 10 ký tự không.

**Anti-spam** kiểm tra "hành vi đáng ngờ": honeypot phát hiện bot tự điền form, rate limit chặn submit quá nhanh.

Bot gửi đủ `full_name`, `email`, `phone` hợp lệ thì validation PASS — chỉ honeypot và rate limit mới chặn được.

---

## 5. Honeypot có giới hạn gì?

Field ẩn `<input name="website">` với CSS `display:none`. Bot đơn giản tự điền tất cả fields kể cả hidden → bị chặn tại:
```php
if ($data['website'] !== '') {
    audit_log('HONEYPOT_TRIGGERED');
    $errors['_global'] = 'Yêu cầu bị từ chối do phát hiện hành vi tự động (honeypot).';
    return $errors;
}
```

Bot tinh vi vượt qua được bằng cách parse CSS để phát hiện `display:none`, hoặc chạy headless browser render như user thật. Hệ thống thực tế cần thêm CAPTCHA, IP-based rate limiting, email verification.

---

## 6. Rate limit bằng session có ưu và nhược điểm gì?

Session lưu `last_ticket_submit_at`. Nếu submit lại trong 5 giây → bị chặn.

Nhược điểm: user xóa cookie hoặc mở tab ẩn danh → session mới → bypass. Hệ thống thực tế cần rate limit theo IP hoặc theo email (server-side, không thể bypass bằng xóa cookie).

---

## 7. Vì sao `session_set_cookie_params()` phải chạy trước `session_start()`?

`session_start()` gửi `Set-Cookie` header ngay khi chạy. Nếu gọi `session_set_cookie_params()` sau, cookie đã được gửi với tham số mặc định — không có `HttpOnly`, `SameSite`.

Ý nghĩa từng flag:
- **HttpOnly**: `document.cookie` không đọc được cookie session → giảm rủi ro XSS đánh cắp session
- **SameSite=Lax**: không gửi cookie trong cross-site sub-resource request → giảm rủi ro CSRF
- **Secure**: chỉ gửi qua HTTPS → ngăn sniff cookie trên HTTP

---

## 8. Vì sao login thành công phải `session_regenerate_id(true)`?

Session fixation: attacker biết session ID trước khi user login (VD: gửi link chứa session ID sẵn). Nếu server không đổi ID sau login, attacker dùng ID cũ để truy cập với quyền của user đã login.

Trong bài (`AuthController::handleLogin()`):
```php
session_regenerate_id(true);  // true = xóa session file cũ
$_SESSION['user_id']   = $user['id'];
$_SESSION['user_name'] = $user['name'];
```

`true` đảm bảo session file với ID cũ bị xóa trên server.

---

## 9. Logout sạch nghĩa là gì?

Chỉ `unset($_SESSION['user_id'])` chưa đủ: session file vẫn tồn tại, cookie vẫn trong browser.

Trong bài (`AuthController::logout()`):
```php
csrf_verify();
audit_log('LOGOUT', ...);
clear_remember_token();   // xóa remember_me cookie + server record
$_SESSION = [];           // xóa toàn bộ session data
session_regenerate_id(true);  // tạo session ID mới, xóa file cũ
flash_set('success', '...');
redirect('/login');
```

Sau logout, vào `/dashboard` → `require_login()` → không có `user_id` → redirect `/login`.

---

## 10. Flash message giúp tránh "kẹt trạng thái" như thế nào?

Nếu không xóa, mỗi lần user vào trang đều thấy "Gửi thành công!" dù đã qua từ lâu.

```php
// helpers.php
function flash_get(string $key, mixed $default = null): mixed
{
    $value = $_SESSION['_flash'][$key] ?? $default;
    unset($_SESSION['_flash'][$key]);  // xóa ngay sau khi đọc
    return $value;
}
```

Set trong controller trước `redirect()` → đọc 1 lần trong `views/layout.php` → biến mất.

---

## 11. CSRF Token bảo vệ điều gì? *(tính năng làm thêm)*

CSRF (Cross-Site Request Forgery): attacker tạo trang web có form ẩn trỏ đến `/tickets`, dụ user click → trình duyệt tự gửi POST kèm session cookie → server không phân biệt được request từ site nào.

CSRF token ngăn chặn bằng cách thêm giá trị bí mật mà chỉ site gốc biết:
```php
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
        die('Token bảo mật không hợp lệ.');
    }
}
```

`hash_equals()` so sánh constant-time để chống timing attack. Site của attacker không biết giá trị token trong session → không thể giả mạo request hợp lệ.

---

## 12. HTTP Security Headers bảo vệ điều gì? *(tính năng làm thêm)*

4 header được thêm vào mỗi response trong `public/index.php`:

| Header | Bảo vệ khỏi |
|--------|-------------|
| `X-Frame-Options: DENY` | Clickjacking — site không bị nhúng vào iframe |
| `X-Content-Type-Options: nosniff` | MIME sniffing — trình duyệt không tự đoán content type |
| `Referrer-Policy: strict-origin-when-cross-origin` | Rò rỉ URL khi navigate sang domain khác |
| `Content-Security-Policy: default-src 'self'` | XSS — chỉ load script/style từ cùng origin |

CSP là lớp bảo vệ thứ hai sau output escaping: dù attacker inject được `<script>`, CSP vẫn chặn script đó chạy nếu không có nonce hoặc không từ 'self'.

---

## 13. Remember Me an toàn cần điều kiện gì? *(tính năng làm thêm)*

Không được lưu password trong cookie — attacker đọc được cookie là bypass hoàn toàn.

Triển khai đúng trong bài:
1. Tạo token ngẫu nhiên 64-byte (`bin2hex(random_bytes(32))`)
2. Lưu `SHA-256(token)` vào `storage/remember_tokens.json` — không lưu raw token
3. Set cookie `remember_token=<raw_token>` httponly, 30 ngày
4. Khi auto-login: đọc cookie → hash → tra file → nếu khớp → login + **rotate token** (tạo token mới, xóa cũ)
5. Logout: xóa record trong JSON, expire cookie ngay

Rotating token đảm bảo nếu token bị đánh cắp từ một thiết bị, sau khi dùng 1 lần token đó vô hiệu hóa.

---

## 14. Audit Log có ích gì trong thực tế? *(tính năng làm thêm)*

Audit log ghi lại `[timestamp] EVENT ip=X key=value` cho mọi sự kiện bảo mật. Ích lợi:

- **Phát hiện tấn công**: nhiều `LOGIN_FAILED` từ cùng IP → brute force
- **Điều tra sự cố**: biết chính xác khi nào và ai logout, timeout, honeypot bị kích
- **Compliance**: nhiều quy định (GDPR, ISO 27001) yêu cầu audit trail cho hệ thống xử lý dữ liệu cá nhân
- **Gỡ lỗi production**: khi có báo cáo "tôi bị logout tự động", kiểm tra log thấy ngay `SESSION_TIMEOUT`

Trong bài, log được ghi vào `storage/audit.log` và hiển thị tại `GET /audit-log` (chỉ admin). Màu sắc phân loại: xanh = thành công, đỏ = bị chặn/lỗi, vàng = logout.

---

## 15. Nếu mở rộng thành dự án thật, nên cải tiến gì?

- **Database**: JSON file không có locking, race condition khi concurrent request. Chuyển sang MySQL/PostgreSQL với PDO prepared statements
- **Middleware layer**: tách `require_login()`, `csrf_verify()` ra middleware thay vì gọi thủ công trong mỗi controller
- **Role-based access**: phân quyền admin (xem/cập nhật tất cả tickets) vs staff (chỉ xem tickets được giao)
- **Email notification**: gửi email xác nhận khi ticket được tạo hoặc cập nhật trạng thái
- **CAPTCHA**: reCAPTCHA v3 để thay thế/bổ sung cho honeypot chống bot tinh vi
- **IP-based rate limiting**: limit theo IP thay vì session — không bypass được bằng xóa cookie
- **Ticket detail + status update**: `GET /tickets/{id}`, `PATCH /tickets/{id}/status`
- **MVC**: tách Model class riêng (TicketModel), Controller chỉ điều phối — hiện tại TicketController vừa validate vừa lưu file
