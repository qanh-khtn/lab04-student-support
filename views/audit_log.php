<div class="page-header">
    <h1>Audit Log</h1>
    <p>Nhật ký hoạt động bảo mật — chỉ hiển thị với admin. Mới nhất ở trên.</p>
</div>

<div class="card" style="padding:0;overflow:hidden;">
    <?php if (empty($lines)): ?>
        <p style="padding:24px;color:#64748b;margin:0;">Chưa có sự kiện nào được ghi lại.</p>
    <?php else: ?>
        <div class="code-block" style="margin:0;border-radius:0;max-height:600px;overflow-y:auto;">
<?php foreach ($lines as $line):
    $color = '#e6edf6';
    if (str_contains($line, 'LOGIN_SUCCESS') || str_contains($line, 'TICKET_SUBMITTED') || str_contains($line, 'REMEMBER_ME_LOGIN')) {
        $color = '#86efac';
    } elseif (str_contains($line, 'FAILED') || str_contains($line, 'HONEYPOT') || str_contains($line, 'RATE_LIMIT') || str_contains($line, 'TIMEOUT')) {
        $color = '#fca5a5';
    } elseif (str_contains($line, 'LOGOUT')) {
        $color = '#fde68a';
    }
?>
<span style="color:<?= $color ?>;display:block;"><?= h($line) ?></span>
<?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div style="margin-top:16px;display:flex;gap:10px;">
    <a href="/dashboard" class="btn btn-secondary btn-sm">Quay lại Dashboard</a>
    <span style="font-size:13px;color:#64748b;align-self:center;">Tổng: <?= count($lines) ?> dòng</span>
</div>
