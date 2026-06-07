<?php

declare(strict_types=1);

namespace App\Controllers;

class TicketController
{
    // In-list validation values
    private const SUPPORT_TYPES = [
        'academic'  => 'Hỗ trợ học tập',
        'financial' => 'Hỗ trợ tài chính / học phí',
        'dormitory' => 'Hỗ trợ ký túc xá',
        'career'    => 'Hỗ trợ việc làm / thực tập',
        'health'    => 'Hỗ trợ y tế / sức khỏe',
        'it'        => 'Hỗ trợ kỹ thuật CNTT',
    ];

    private const PRIORITY_LEVELS = ['normal', 'urgent'];

    // -------------------------------------------------------
    // GET /tickets — list all tickets
    // -------------------------------------------------------
    public function index(): void
    {
        view('tickets/index', [
            'view'         => 'tickets/index',
            'pageTitle'    => 'Danh sách yêu cầu',
            'items'        => $this->loadTickets(),
            'supportTypes' => self::SUPPORT_TYPES,
        ]);
    }

    // -------------------------------------------------------
    // GET /tickets/create — show form
    // -------------------------------------------------------
    public function create(): void
    {
        $old    = flash_get('old', []);
        $errors = flash_get('errors', []);

        view('tickets/create', [
            'view'          => 'tickets/create',
            'pageTitle'     => 'Gửi yêu cầu hỗ trợ',
            'old'           => $old,
            'errors'        => $errors,
            'supportTypes'  => self::SUPPORT_TYPES,
            'priorityLevels' => self::PRIORITY_LEVELS,
        ]);
    }

    // -------------------------------------------------------
    // POST /tickets — validate + anti-spam + save + PRG redirect
    // -------------------------------------------------------
    public function store(): void
    {
        // Read input safely
        $data = [
            'full_name'    => trim($_POST['full_name']    ?? ''),
            'email'        => trim($_POST['email']        ?? ''),
            'phone'        => trim($_POST['phone']        ?? ''),
            'student_id'   => trim($_POST['student_id']   ?? ''),
            'support_type' => trim($_POST['support_type'] ?? ''),
            'priority'     => trim($_POST['priority']     ?? ''),
            'description'  => trim($_POST['description']  ?? ''),
            'website'      => trim($_POST['website']      ?? ''), // honeypot field
        ];

        $errors = $this->validate($data);

        if (!empty($errors)) {
            flash_set('errors', $errors);
            flash_set('old', array_diff_key($data, ['website' => ''])); // don't flash honeypot
            redirect('/tickets/create');
        }

        // --- All validation passed: save + PRG ---
        $this->saveTicket($data);
        $_SESSION['last_ticket_submit_at'] = time();

        flash_set('success', 'Yêu cầu hỗ trợ đã được gửi thành công! Mã yêu cầu của bạn sẽ được xử lý trong vòng 24h.');
        redirect('/tickets');
    }

    // -------------------------------------------------------
    // Validation — returns array of field => message
    // -------------------------------------------------------
    private function validate(array $data): array
    {
        $errors = [];

        // --- Anti-spam checks (run first so they can short-circuit) ---

        // Honeypot: real users never fill this hidden field
        if ($data['website'] !== '') {
            $errors['_global'] = 'Yêu cầu bị từ chối do phát hiện hành vi tự động (honeypot).';
            return $errors; // short-circuit, don't bother validating
        }

        // Rate limit: block submitting more than once in 5 seconds
        $lastSubmit = $_SESSION['last_ticket_submit_at'] ?? 0;
        if ($lastSubmit > 0 && (time() - $lastSubmit) < 5) {
            $errors['_global'] = 'Bạn gửi yêu cầu quá nhanh. Vui lòng thử lại sau vài giây.';
            return $errors;
        }

        // --- Field validation ---

        // full_name: required, min 2 chars
        if ($data['full_name'] === '') {
            $errors['full_name'] = 'Vui lòng nhập họ tên.';
        } elseif (preg_match('/[<>]/', $data['full_name'])) {
            $errors['full_name'] = 'Họ tên không được chứa ký tự HTML hoặc script.';
        } elseif (mb_strlen($data['full_name']) < 2) {
            $errors['full_name'] = 'Họ tên phải có ít nhất 2 ký tự.';
        } elseif (mb_strlen($data['full_name']) > 100) {
            $errors['full_name'] = 'Họ tên không được vượt quá 100 ký tự.';
        }

        // email: required, valid format
        if ($data['email'] === '') {
            $errors['email'] = 'Vui lòng nhập email.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email không đúng định dạng (ví dụ: sinhvien@school.edu.vn).';
        }

        // phone: required, pattern digits/spaces/+/-  9-15 chars
        if ($data['phone'] === '') {
            $errors['phone'] = 'Vui lòng nhập số điện thoại.';
        } elseif (!preg_match('/^[0-9+\-\s]{9,15}$/', $data['phone'])) {
            $errors['phone'] = 'Số điện thoại chỉ gồm số, +, - hoặc khoảng trắng, dài 9-15 ký tự.';
        }

        // student_id: optional but if provided must match pattern SVxxxxxxx
        if ($data['student_id'] !== '' && !preg_match('/^SV\d{6,9}$/i', $data['student_id'])) {
            $errors['student_id'] = 'Mã sinh viên không đúng định dạng (ví dụ: SV123456).';
        }

        // support_type: required, in-list
        if ($data['support_type'] === '' || !array_key_exists($data['support_type'], self::SUPPORT_TYPES)) {
            $errors['support_type'] = 'Vui lòng chọn loại hỗ trợ hợp lệ.';
        }

        // priority: required, in-list
        if ($data['priority'] === '' || !in_array($data['priority'], self::PRIORITY_LEVELS, true)) {
            $errors['priority'] = 'Vui lòng chọn mức độ ưu tiên.';
        }

        // description: required, 10-1000 chars
        if ($data['description'] === '') {
            $errors['description'] = 'Vui lòng mô tả vấn đề cần hỗ trợ.';
        } elseif (mb_strlen($data['description']) < 10) {
            $errors['description'] = 'Mô tả phải có ít nhất 10 ký tự.';
        } elseif (mb_strlen($data['description']) > 1000) {
            $errors['description'] = 'Mô tả không được vượt quá 1000 ký tự.';
        }

        return $errors;
    }

    // -------------------------------------------------------
    // Storage helpers
    // -------------------------------------------------------
    private function storageFile(): string
    {
        return __DIR__ . '/../../storage/tickets.json';
    }

    private function loadTickets(): array
    {
        $file = $this->storageFile();
        if (!file_exists($file)) {
            return [];
        }
        $json = file_get_contents($file);
        return json_decode($json, true) ?: [];
    }

    private function saveTicket(array $data): void
    {
        $items    = $this->loadTickets();
        $nextId   = count($items) + 1;
        $items[]  = [
            'id'           => 'TK' . str_pad((string) $nextId, 4, '0', STR_PAD_LEFT),
            'full_name'    => $data['full_name'],
            'email'        => $data['email'],
            'phone'        => $data['phone'],
            'student_id'   => $data['student_id'],
            'support_type' => $data['support_type'],
            'priority'     => $data['priority'],
            'description'  => $data['description'],
            'status'       => 'new',
            'created_at'   => date('Y-m-d H:i:s'),
        ];

        file_put_contents(
            $this->storageFile(),
            json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
