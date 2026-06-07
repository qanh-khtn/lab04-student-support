<?php

declare(strict_types=1);

namespace App\Controllers;

class DashboardController
{
    // -------------------------------------------------------
    // GET /dashboard — protected by require_login()
    // -------------------------------------------------------
    public function index(): void
    {
        require_login();

        // Count tickets for the dashboard
        $ticketFile  = __DIR__ . '/../../storage/tickets.json';
        $tickets     = [];
        if (file_exists($ticketFile)) {
            $tickets = json_decode(file_get_contents($ticketFile), true) ?: [];
        }

        $stats = [
            'total'    => count($tickets),
            'new'      => count(array_filter($tickets, fn($t) => ($t['status'] ?? '') === 'new')),
            'urgent'   => count(array_filter($tickets, fn($t) => ($t['priority'] ?? '') === 'urgent')),
            'resolved' => count(array_filter($tickets, fn($t) => ($t['status'] ?? '') === 'resolved')),
        ];

        view('dashboard', [
            'view'      => 'dashboard',
            'pageTitle' => 'Dashboard',
            'stats'     => $stats,
        ]);
    }

    // -------------------------------------------------------
    // GET /session-demo — returns session info as JSON (debug)
    // -------------------------------------------------------
    public function auditLog(): void
    {
        require_login();

        if (($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403);
            die('Chỉ admin mới xem được audit log.');
        }

        $logFile = __DIR__ . '/../../storage/audit.log';
        $lines   = file_exists($logFile)
            ? array_reverse(file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES))
            : [];

        view('audit_log', [
            'view'      => 'audit_log',
            'pageTitle' => 'Audit Log',
            'lines'     => $lines,
        ]);
    }

    public function sessionDemo(): void
    {
        require_login();

        header('Content-Type: application/json; charset=utf-8');

        $idleLimit = 900; // seconds — matches check_session_timeout()
        $loginAt   = $_SESSION['login_at']         ?? null;
        $lastAct   = $_SESSION['last_activity_at'] ?? null;

        echo json_encode([
            'session_name'       => session_name(),
            'session_id'         => session_id(),
            'user_id'            => $_SESSION['user_id']    ?? null,
            'user_name'          => $_SESSION['user_name']  ?? null,
            'role'               => $_SESSION['role']       ?? null,
            'login_at'           => $loginAt  ? date('Y-m-d H:i:s', $loginAt)  : null,
            'last_activity_at'   => $lastAct  ? date('Y-m-d H:i:s', $lastAct)  : null,
            'idle_timeout_sec'   => $idleLimit,
            'idle_elapsed_sec'   => $lastAct ? (time() - $lastAct) : null,
            'cookie_httponly'    => true,
            'cookie_samesite'    => 'Lax',
            'cookie_secure'      => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'note'               => 'session_regenerate_id(true) was called at login to prevent session fixation.',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
