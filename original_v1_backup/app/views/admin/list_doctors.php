<?php /* app/views/admin/list_doctors.php — var: $doctors */ ?>

<div style="display:flex;justify-content:flex-end;margin-bottom:16px">
  <a href="<?= BASE_URL ?>/public/admin/create-staff.php" class="btn btn-primary btn-sm">+ Add Doctor</a>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Employee ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Specialization</th>
          <th>Department</th>
          <th>Fee</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($doctors)): ?>
          <tr><td colspan="8" style="text-align:center;padding:28px;color:#9ca3af">
            No doctors yet. <a href="<?= BASE_URL ?>/public/admin/create-staff.php">Add one →</a>
          </td></tr>
        <?php else: foreach ($doctors as $d): ?>
          <tr>
            <td style="font-family:monospace;font-size:12px">
              <?= htmlspecialchars($d['employee_id']) ?>
            </td>
            <td>
              <strong><?= htmlspecialchars($d['full_name'] ?: '—') ?></strong>
              <?php if ($d['is_first_login']): ?>
                <span class="badge badge-pending" style="margin-left:4px">Pending</span>
              <?php endif; ?>
            </td>
            <td style="font-size:12px;color:#6b7280"><?= htmlspecialchars($d['email']) ?></td>
            <td><?= htmlspecialchars($d['specialization'] ?? '—') ?></td>
            <td><?= htmlspecialchars($d['department'] ?? '—') ?></td>
            <td><?= $d['consultation_fee'] ? '৳'.number_format($d['consultation_fee']) : '—' ?></td>
            <td>
              <span class="badge badge-<?= $d['availability_status'] === 'Available' ? 'available' : 'unavail' ?>">
                <?= htmlspecialchars($d['availability_status']) ?>
              </span>
            </td>
            <td>
              <form method="POST" action="<?= BASE_URL ?>/public/admin/delete-user.php"
                    style="display:inline"
                    onsubmit="return confirm('Delete this doctor account? This cannot be undone.')">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <input type="hidden" name="user_id"   value="<?= $d['user_id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
