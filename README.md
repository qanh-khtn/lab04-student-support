# Mini Student Support Request Portal — Lab04

**PHP Secure Forms, PRG, Anti-spam & Session Login Flow**

Bai toan: **Mini Student Support Request Portal** — cong gui yeu cau ho tro sinh vien.

---

## Chay project

### Yeu cau

- PHP >= 8.0 (`php -v`)
- Composer (`composer --version`)
- Git (`git --version`)

### Cai dat

```bash
# 1. Clone hoac giai nen project
cd lab04-student-support

# 2. Cai dat autoload
composer install
# hoac neu chua co vendor/
composer dump-autoload

# 3. Khoi dong built-in PHP server
php -S localhost:8000 -t public

# 4. Mo trinh duyet
# http://localhost:8000/
```

### Tai khoan demo

| Email | Mat khau | Role |
|---|---|---|
| admin@school.edu.vn | Admin@123 | admin |
| staff@school.edu.vn | Staff@123 | staff |

---

## Cac route

| Method | URL | Chuc nang |
|---|---|---|
| GET | `/` | Trang chu |
| GET | `/tickets` | Danh sach yeu cau ho tro |
| GET | `/tickets/create` | Form gui yeu cau |
| POST | `/tickets` | Xu ly gui yeu cau (validate + anti-spam + PRG) |
| GET | `/login` | Form dang nhap |
| POST | `/login` | Xu ly dang nhap (regenerate session) |
| POST | `/logout` | Dang xuat sach |
| GET | `/dashboard` | Dashboard (chi user da dang nhap) |
| GET | `/session-demo` | Debug session (JSON) |
| ANY | URL khong ton tai | 404 Not Found |
| Sai method | Route co nhung method sai | 405 Method Not Allowed |

---

## Cau truc thu muc

```
lab04-student-support/
├── app/
│   ├── Controllers/
│   │   ├── HomeController.php
│   │   ├── TicketController.php    # main resource
│   │   ├── AuthController.php
│   │   └── DashboardController.php
│   ├── Core/
│   │   └── Router.php
│   └── Support/
│       └── helpers.php
├── public/
│   ├── index.php                   # Front Controller
│   └── assets/
│       └── style.css
├── storage/
│   └── tickets.json                # JSON storage (no database)
├── views/
│   ├── layout.php
│   ├── home.php
│   ├── dashboard.php
│   ├── tickets/
│   │   ├── index.php
│   │   └── create.php
│   ├── auth/
│   │   └── login.php
│   └── errors/
│       ├── 404.php
│       └── 405.php
├── composer.json
└── README.md
```

---

## Tinh nang ky thuat

| Nhom | Chi tiet |
|---|---|
| GET/POST | GET hien thi, POST gui du lieu, khong tao du lieu bang GET |
| Input safety | `$_POST` + `??` + `trim()`, khong tin user input |
| Escape output | Tat ca output qua `h()` / `htmlspecialchars()` |
| Server-side validation | required, email, phone pattern, in-list, length |
| PRG | POST thanh cong -> redirect GET |
| Flash message | Hien 1 lan sau redirect, tu dong xoa |
| Honeypot | Field `website` an, bot dien -> bi chan |
| Rate limit | Session: khong cho gui 2 lan trong 5 giay |
| Session cookie | `HttpOnly=true`, `SameSite=Lax`, `Secure` theo moi truong |
| Login | `session_regenerate_id(true)`, luu `user_id/role/login_at/last_activity_at` |
| Dashboard protection | `require_login()` redirect ve /login |
| Timeout | Idle 15 phut (doi 60s trong helpers.php de demo T15) |
| Logout sach | Xoa `$_SESSION`, `session_destroy()`, xoa cookie |
| 404/405 | Router phan biet URL khong ton tai vs method sai |

---

## Doi timeout de test T15

Mo file `app/Support/helpers.php`, tim dong:

```php
$idleLimit = (int) ($_ENV['SESSION_IDLE_LIMIT'] ?? 900); // 900 = 15 phut
```

Doi thanh:

```php
$idleLimit = 60; // 60 giay cho demo T15
```

Sau khi test xong, doi lai 900.

---

## Test bang curl

```bash
# Test honeypot (gui field website co gia tri)
curl -X POST http://localhost:8000/tickets \
  -d "full_name=Test&email=a@b.com&phone=0901234567&support_type=academic&priority=normal&description=Test description di&website=spam"

# Test 404
curl -i http://localhost:8000/khong-ton-tai

# Test 405 (GET /logout)
curl -i http://localhost:8000/logout
```
