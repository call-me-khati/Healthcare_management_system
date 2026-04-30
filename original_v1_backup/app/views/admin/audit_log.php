<?php // app/views/admin/audit_log.php ?>
<div class="page-header">
  <h2>🛡️ Audit Log</h2>
  <span class="text-muted">Last 100 system events</span>
</div>

<div class="alert alert-info" style="font-size:12px">
  This log records who accessed and modified data in the system. All actions are timestamped and include the actor's IP address.
</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr><th>Time</th><th>User</th><th>Action</th><th>Table</th><th>Record ID</th><th>IP Address</th></tr>
    </thead>
    <tbody>
      <?php foreach ($logs as $log):
        $actionColors = [
          'LOGIN'          => 'pill-green',
          'LOGOUT'         => 'pill-gray',
          'LOGIN_FAIL'     => 'pill-red',
          'DELETE_USER'    => 'pill-red',
          'BOOK_APPOINTMENT'=> 'pill-blue',
          'CONSULTATION'   => 'pill-purple',
          'DISPENSE_MEDICINE' => 'pill-orange',
        ];
        $cls = $actionColors[$log['action']] ?? 'pill-gray';
      ?>
      <tr>
        <td class="text-muted" style="white-space:nowrap"><?= date('d M Y H:i', strtotime($log['created_at'])) ?></td>
        <td>
          <?php if ($log['full_name']): ?>
            <strong><?= htmlspecialchars($log['full_name']) ?></strong><br>
            <span class="text-muted"><?= htmlspecialchars($log['email'] ?? '') ?></span>
          <?php else: ?>
            <span class="text-muted">System</span>
          <?php endif; ?>
        </td>
        <td><span class="pill <?= $cls ?>"><?= htmlspecialchars($log['action']) ?></span></td>
        <td class="text-muted"><?= htmlspecialchars($log['table_name'] ?? '—') ?></td>
        <td class="text-muted"><?= $log['record_id'] ?? '—' ?></td>
        <td class="text-muted"><?= htmlspecialchars($log['ip_address'] ?? '—') ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($logs)): ?>
        <tr><td colspan="6" class="text-center text-muted" style="padding:30px">No audit records yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
