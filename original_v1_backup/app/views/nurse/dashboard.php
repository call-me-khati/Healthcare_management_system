<?php // app/views/nurse/dashboard.php ?>

<div class="stat-grid">
  <div class="stat-card blue"><div class="num"><?= $stats['Pending'] ?></div><div class="lbl">📋 Pending</div></div>
  <div class="stat-card teal"><div class="num"><?= $stats['Confirmed'] ?></div><div class="lbl">✅ Confirmed</div></div>
  <div class="stat-card green"><div class="num"><?= $stats['Completed'] ?></div><div class="lbl">🏁 Completed</div></div>
  <div class="stat-card orange"><div class="num"><?= $stats['total'] ?></div><div class="lbl">📊 Total</div></div>
</div>

<?php if (!empty($lowStock)): ?>
  <div class="alert alert-warning">⚠️ <?= count($lowStock) ?> medicine(s) are low on stock. <a href="<?= BASE_URL ?>/public/nurse/medicines.php">View →</a></div>
<?php endif; ?>
<?php if (!empty($expiring)): ?>
  <div class="alert alert-danger">🚨 <?= count($expiring) ?> medicine(s) expiring soon. <a href="<?= BASE_URL ?>/public/nurse/medicines.php">View →</a></div>
<?php endif; ?>

<div class="card">
  <div class="card-title">📅 Today's Appointments — <?= date('d M Y') ?></div>
  <?php if (empty($today)): ?>
    <p class="text-muted text-center" style="padding:20px">No appointments today.</p>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Queue #</th><th>Patient</th><th>Doctor</th><th>Time</th><th>Priority</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($today as $a):
          $prCls = $a['priority'] === 'Emergency' ? 'pill-red' : ($a['priority'] === 'Urgent' ? 'pill-orange' : 'pill-blue');
          $stCls = $a['status'] === 'Confirmed' ? 'pill-green' : ($a['status'] === 'Completed' ? 'pill-gray' : 'pill-yellow');
        ?>
        <tr class="<?= $a['priority'] === 'Emergency' ? 'priority-emergency' : ($a['priority'] === 'Urgent' ? 'priority-urgent' : '') ?>">
          <td><span class="queue-number"><?= $a['queue_number'] ?? '—' ?></span></td>
          <td>
            <strong><?= htmlspecialchars($a['student_name']) ?></strong><br>
            <span class="text-muted"><?= htmlspecialchars($a['student_uid'] ?? '') ?></span>
          </td>
          <td class="text-muted"><?= htmlspecialchars($a['doctor_name']) ?></td>
          <td><?= substr($a['appointment_time'], 0, 5) ?></td>
          <td><span class="pill <?= $prCls ?>"><?= $a['priority'] ?></span></td>
          <td><span class="pill <?= $stCls ?>"><?= $a['status'] ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
