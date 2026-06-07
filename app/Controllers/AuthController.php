<?php

declare(strict_types=1);

namespace App\Controllers;

class AuthController
{
    private array $users;

    public function __construct()
    {
        $this->users = [
            'admin@school.edu.vn' => [
                'id'            => 1,
                'name'          => 'Nguyễn Văn Admin',
                'role'          => 'admin',
                'password_hash' => password_hash('Admin@123', PASSWORD_DEFAULT),
            ],
            'staff@school.edu.vn' => [
                'id'            => 2,
                'name'          => 'Trần Thị Staff',
                'role'          => 'staff',
                'password_hash' => password_hash('Staff@123', PASSWORD_DEFAULT),
            ],
        ];
    }

    public function login(): void
    {
        if (is_logged_in()) {
            redirect('/dashboard');
        }

        view('auth/login', [
            'view'      => 'auth/login',
            'pageTitle' => 'Đăng nhập',
            'old'       => flash_get('old', []),
            'errors'    => flash_get('errors', []),
        ]);
    }

    public function handleLogin(): void
    {
        csrf_verify();

        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password']      ?? '';
        $remember = ($_POST['remember_me']  ?? '') === '1';

        $errors = [];

        if ($email === '') {
            $errors['email'] = 'Vui lòng nhập email.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không đúng định dạng.';
        }

        if ($password === '') {
            $errors['password'] = 'Vui lòng nhập mật khẩu.';
        }

        $user = null;
        if (empty($errors)) {
            $user = $this->users[$email] ?? null;
            if (!$user || !password_verify($password, $user['password_hash'])) {
                $errors['password'] = 'Email hoặc mật khẩu không chính xác.';
            }
        }

        if (!empty($errors)) {
            flash_set('errors', $errors);
            flash_set('old', ['email' => $email]);
            redirect('/login');
        }

        session_regenerate_id(true);

        $_SESSION['user_id']          = $user['id'];
        $_SESSION['user_name']        = $user['name'];
        $_SESSION['role']             = $user['role'];
        $_SESSION['login_at']         = time();
        $_SESSION['last_activity_at'] = time();
        $_SESSION['user_agent']       = $_SERVER['HTTP_USER_AGENT'] ?? '';

        if ($remember) {
            flash_set('success', 'Đăng nhập thành công! Remember me trong Lab04 chỉ giới thiệu rủi ro, không lưu password trong cookie.');
        } else {
            flash_set('success', 'Đăng nhập thành công! Session ID đã được regenerate sau login.');
        }

        redirect('/dashboard');
    }

    public function logout(): void
    {
        csrf_verify();

        $_SESSION = [];
        session_regenerate_id(true);
        flash_set('success', 'Đã đăng xuất thành công. Phiên làm việc cũ đã bị xóa sạch.');
        redirect('/login');
    }
}
