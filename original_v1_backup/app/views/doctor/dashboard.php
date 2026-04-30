<?php // app/views/doctor/dashboard.php ?>

<!-- Status toggle -->
<form method="POST" action="<?= BASE_URL ?>/public/doctor/toggle-status.php" style="margin-bottom:20px">
  <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
  <div style="display:flex;align-items:center;gap:12px">
    <span style="font-size:13px;font-weight:600">Availability:</span>
    <?php foreach (['Available','Unavailable','On Leave'] as $s):
      $active = $doctor['availability_status'] === $s;
      $btnClass = $s === 'Available' ? 'btn-success' : ($s === 'On Leave' ? 'btn-danger' : 'btn-secondary');
    ?>
    <button type="submit" name="availability_status" value="<?= $s ?>"
            class="btn btn-sm <?= $active ? $btnClass : 'btn-secondary' ?>"
            style="<?= $active ? 'font-weight:700' : 'opacity:.6' ?>">
      <?= $s === 'Available' ? '🟢' : ($s === 'On Leave' ? '🔴' : '🟡') ?> <?= $s ?>
    </button>
    <?php endforeach; ?>
  </div>
</form>

<!-- Stats -->
<div class="stat-grid">
  <div class="stat-card blue"><div class="num"><?= $stats['Pending'] ?></div><div class="lbl">📋 Pending</div></div>
  <div class="stat-card teal"><div class="num"><?= $stats['Confirmed'] ?></div><div class="lbl">✅ Confirmed</div></div>
  <div class="stat-card green"><div class="num"><?= $stats['Completed'] ?></div><div class="lbl">🏁 Completed</div></div>
  <div class="stat-card red"><div class="num"><?= $stats['Cancelled'] ?></div><div class="lbl">❌ Cancelled</div></div>
</div>

<!-- Today's Queue -->
<div class="card mb-24">
  <div class="card-title">🗂️ Today's Queue — <?= date('l, d M Y') ?></div>
  <?php if (empty($queue)): ?>
    <p class="text-muted text-center" style="padding:20px">No appointments scheduled for today.</p>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Queue #</th><th>Patient</th><th>UID</th><th>Time</th><th>Priority</th><th>Blood Group</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($queue as $a):
          $prCls = $a['priority'] === 'Emergency' ? 'pill-red' : ($a['priority'] === 'Urgent' ? 'pill-orange' : 'pill-blue');
          $rowCls = $a['priority'] === 'Emergency' ? 'priority-emergency' : ($a['priority'] === 'Urgent' ? 'priority-urgent' : '');
        ?>
        <tr class="<?= $rowCls ?>">
          <td><span class="queue-number"><?= $a['queue_number'] ?></span></td>
          <td><strong><?= htmlspecialchars($a['student_name']) ?></strong></td>
          <td class="text-muted"><?= htmlspecialchars($a['student_uid'] ?? '—') ?></td>
          <td><?= substr($a['appointment_time'], 0, 5) ?></td>
          <td><span class="pill <?= $prCls ?>"><?= $a['priority'] ?></span></td>
          <td><?= htmlspecialchars($a['blood_group'] ?? '—') ?></td>
          <td><span class="pill <?= $a['status'] === 'Confirmed' ? 'pill-green' : 'pill-yellow' ?>"><?= $a['status'] ?></span></td>
          <td>
            <a href="<?= BASE_URL ?>/public/doctor/consult.php?appointment_id=<?= $a['appointment_id'] ?>" class="btn btn-sm btn-primary">Consult</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<!-- Upcoming confirmed -->
<?php if (!empty($upcoming)): ?>
<div class="card">
  <div class="card-title">📅 Upcoming Confirmed Appointments</div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Date</th><th>Time</th><th>Patient</th><th>Priority</th><th>Reason</th></tr></thead>
      <tbody>
        <?php foreach (array_slice($upcoming, 0, 5) as $a): ?>
        <tr>
          <td><?= $a['appointment_date'] ?></td>
          <td><?= substr($a['appointment_time'], 0, 5) ?></td>
          <td><?= htmlspecialchars($a['student_name']) ?></td>
          <td><span class="pill <?= $a['priority'] === 'Normal' ? 'pill-blue' : 'pill-orange' ?>"><?= $a['priority'] ?></span></td>
          <td class="text-muted"><?= htmlspecialchars(mb_substr($a['reason'] ?? '—', 0, 50)) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
