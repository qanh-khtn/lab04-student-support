<div class="page-header">
    <h1>Gửi yêu cầu hỗ trợ sinh viên</h1>
</div>

<?php if (!empty($errors['_global'])): ?>
    <div class="alert alert-error"><?= h($errors['_global']) ?></div>
<?php endif; ?>

<?php if (!empty($errors) && empty($errors['_global'])): ?>
    <div class="alert alert-warning">Vui lòng kiểm tra lại thông tin. Dữ liệu cũ vẫn được giữ để sửa.</div>
<?php endif; ?>

<form method="post" action="/tickets" class="card" novalidate>
    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

    <div class="form-row">
        <div class="form-group">
            <label for="full_name">Họ và tên <span style="color:#dc2626;">*</span></label>
            <input
                type="text"
                id="full_name"
                name="full_name"
                value="<?= h($old['full_name'] ?? '') ?>"
                placeholder="Nguyễn Văn A"
                class="<?= !empty($errors['full_name']) ? 'input-error' : '' ?>"
            >
            <?php if (!empty($errors['full_name'])): ?>
                <div class="error-text"><?= h($errors['full_name']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="student_id">Mã sinh viên <span style="color:#64748b;font-weight:400;">(tùy chọn)</span></label>
            <input
                type="text"
                id="student_id"
                name="student_id"
                value="<?= h($old['student_id'] ?? '') ?>"
                placeholder="SV123456"
                class="<?= !empty($errors['student_id']) ? 'input-error' : '' ?>"
            >
            <?php if (!empty($errors['student_id'])): ?>
                <div class="error-text"><?= h($errors['student_id']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="email">Email <span style="color:#dc2626;">*</span></label>
            <input
                type="email"
                id="email"
                name="email"
                value="<?= h($old['email'] ?? '') ?>"
                placeholder="sinhvien@school.edu.vn"
                class="<?= !empty($errors['email']) ? 'input-error' : '' ?>"
            >
            <?php if (!empty($errors['email'])): ?>
                <div class="error-text"><?= h($errors['email']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="phone">Số điện thoại <span style="color:#dc2626;">*</span></label>
            <input
                type="tel"
                id="phone"
                name="phone"
                value="<?= h($old['phone'] ?? '') ?>"
                placeholder="0901234567"
                class="<?= !empty($errors['phone']) ? 'input-error' : '' ?>"
            >
            <?php if (!empty($errors['phone'])): ?>
                <div class="error-text"><?= h($errors['phone']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="support_type">Loại hỗ trợ cần <span style="color:#dc2626;">*</span></label>
            <select
                id="support_type"
                name="support_type"
                class="<?= !empty($errors['support_type']) ? 'input-error' : '' ?>"
            >
                <option value="">-- Chọn loại hỗ trợ --</option>
                <?php foreach ($supportTypes as $key => $label): ?>
                    <option value="<?= h($key) ?>" <?= (($old['support_type'] ?? '') === $key) ? 'selected' : '' ?>>
                        <?= h($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['support_type'])): ?>
                <div class="error-text"><?= h($errors['support_type']) ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="priority">Mức độ ưu tiên <span style="color:#dc2626;">*</span></label>
            <select
                id="priority"
                name="priority"
                class="<?= !empty($errors['priority']) ? 'input-error' : '' ?>"
            >
                <option value="">-- Chọn mức độ --</option>
                <option value="normal"  <?= (($old['priority'] ?? '') === 'normal')  ? 'selected' : '' ?>>Bình thường</option>
                <option value="urgent"  <?= (($old['priority'] ?? '') === 'urgent')  ? 'selected' : '' ?>>Khẩn cấp</option>
            </select>
            <?php if (!empty($errors['priority'])): ?>
                <div class="error-text"><?= h($errors['priority']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-group">
        <label for="description">Mô tả vấn đề cần hỗ trợ <span style="color:#dc2626;">*</span></label>
        <textarea
            id="description"
            name="description"
            placeholder="Mô tả chi tiết vấn đề bạn đang gặp phải... (ít nhất 10 ký tự)"
            class="<?= !empty($errors['description']) ? 'input-error' : '' ?>"
        ><?= h($old['description'] ?? '') ?></textarea>
        <?php if (!empty($errors['description'])): ?>
            <div class="error-text"><?= h($errors['description']) ?></div>
        <?php endif; ?>
        <div style="font-size:12px;color:#94a3b8;margin-top:4px;">Tối đa 1000 ký tự.</div>
    </div>

    <div class="honeypot" aria-hidden="true">
        <label>Website (không điền)</label>
        <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
    </div>

    <hr class="divider">

    <div style="display:flex;gap:12px;flex-wrap:wrap;">
        <button type="submit" class="btn btn-primary">Gửi yêu cầu hỗ trợ</button>
        <a href="/tickets" class="btn btn-secondary">Quay lại danh sách</a>
    </div>
    <p style="font-size:12px;color:#94a3b8;margin-top:12px;">
        (*) Là trường bắt buộc. Dữ liệu sẽ được xử lý theo quy trình bảo mật.
    </p>
</form>
