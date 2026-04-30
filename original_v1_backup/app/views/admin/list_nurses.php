<?php /* app/views/admin/list_nurses.php — var: $nurses */ ?>

<div style="display:flex;justify-content:flex-end;margin-bottom:16px">
  <a href="<?= BASE_URL ?>/public/admin/create-staff.php" class="btn btn-primary btn-sm">+ Add Nurse</a>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Employee ID</th><th>Name</th><th>Email</th><th>Department</th><th>Gender</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if (empty($nurses)): ?>
          <tr><td colspan="7" style="text-align:center;padding:28px;color:#9ca3af">
            No nurses yet. <a href="<?= BASE_URL ?>/public/admin/create-staff.php">Add one →</a>
          </td></tr>
        <?php else: foreach ($nurses as $n): ?>
          <tr>
            <td style="font-family:monospace;font-size:12px"><?= htmlspecialchars($n['employee_id']) ?></td>
            <td>
              <strong><?= htmlspecialchars($n['full_name'] ?: '—') ?></strong>
              <?php if ($n['is_first_login']): ?>
                <span class="badge badge-pending" style="margin-left:4px">Pending</span>
              <?php endif; ?>
            </td>
            <td style="font-size:12px;color:#6b7280"><?= htmlspecialchars($n['email']) ?></td>
            <td><?= htmlspecialchars($n['department'] ?? '—') ?></td>
            <td><?= htmlspecialchars($n['gender'] ?? '—') ?></td>
            <td>
              <span class="badge <?= $n['is_first_login'] ? 'badge-pending' : 'badge-available' ?>">
                <?= $n['is_first_login'] ? 'Pending Setup' : 'Active' ?>
              </span>
            </td>
            <td>
              <form method="POST" action="<?= BASE_URL ?>/public/admin/delete-user.php"
                    style="display:inline"
                    onsubmit="return confirm('Delete this nurse? Cannot be undone.')">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <input type="hidden" name="user_id"   value="<?= $n['user_id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
