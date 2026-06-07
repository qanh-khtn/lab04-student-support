<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="margin:0 0 4px;">Danh sách yêu cầu hỗ trợ</h1>
        <p style="margin:0;color:#64748b;">Tổng số: <?= count($items) ?> yêu cầu</p>
    </div>
    <a href="/tickets/create" class="btn btn-primary">+ Gửi yêu cầu mới</a>
</div>

<?php if (empty($items)): ?>
    <div class="card" style="text-align:center;padding:40px;">
        <p style="color:#64748b;margin:0;">Chưa có yêu cầu nào. <a href="/tickets/create">Gửi yêu cầu đầu tiên</a>.</p>
    </div>
<?php else: ?>
<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Mã</th>
                <th>Họ tên</th>
                <th>Email</th>
                <th>SBD/MSSV</th>
                <th>Loại hỗ trợ</th>
                <th>Ưu tiên</th>
                <th>Trạng thái</th>
                <th>Ngày gửi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (array_reverse($items) as $item): ?>
            <tr>
                <td><strong><?= h($item['id']) ?></strong></td>
                <td><?= h($item['full_name']) ?></td>
                <td><?= h($item['email']) ?></td>
                <td><?= h($item['student_id'] ?: '-') ?></td>
                <td><?= h($supportTypes[$item['support_type']] ?? $item['support_type']) ?></td>
                <td>
                    <?php if ($item['priority'] === 'urgent'): ?>
                        <span class="badge badge-urgent">Khẩn cấp</span>
                    <?php else: ?>
                        <span class="badge badge-new">Bình thường</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    $statusMap = ['new' => 'Mới', 'open' => 'Đang xử lý', 'resolved' => 'Đã xử lý'];
                    $statusClass = ['new' => 'badge-new', 'open' => 'badge-open', 'resolved' => 'badge-resolved'];
                    $s = $item['status'] ?? 'new';
                    ?>
                    <span class="badge <?= h($statusClass[$s] ?? 'badge-new') ?>"><?= h($statusMap[$s] ?? $s) ?></span>
                </td>
                <td style="white-space:nowrap;"><?= h($item['created_at']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
