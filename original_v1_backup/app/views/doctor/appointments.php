<?php // app/views/doctor/appointments.php ?>
<?php if ($success = getFlash('success')): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

<div class="page-header">
  <h2>📅 Appointments</h2>
  <div style="display:flex;gap:8px">
    <?php foreach (['','Pending','Confirmed','Completed','Cancelled'] as $s): ?>
      <a href="?status=<?= $s ?>" class="btn btn-sm <?= ($_GET['status'] ?? '') === $s ? 'btn-primary' : 'btn-secondary' ?>"><?= $s ?: 'All' ?></a>
    <?php endforeach; ?>
  </div>
</div>

<div class="stat-grid">
  <div class="stat-card blue"><div class="num"><?= $stats['Pending'] ?></div><div class="lbl">Pending</div></div>
  <div class="stat-card teal"><div class="num"><?= $stats['Confirmed'] ?></div><div class="lbl">Confirmed</div></div>
  <div class="stat-card green"><div class="num"><?= $stats['Completed'] ?></div><div class="lbl">Completed</div></div>
  <div class="stat-card red"><div class="num"><?= $stats['Cancelled'] ?></div><div class="lbl">Cancelled</div></div>
</div>

<div class="table-wrap">
  <table>
    <thead>
      <tr><th>#</th><th>Queue</th><th>Patient</th><th>Date & Time</th><th>Priority</th><th>Reason</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($appointments as $i => $a):
        $prCls = $a['priority'] === 'Emergency' ? 'pill-red' : ($a['priority'] === 'Urgent' ? 'pill-orange' : 'pill-blue');
        $rowCls = $a['priority'] === 'Emergency' ? 'priority-emergency' : ($a['priority'] === 'Urgent' ? 'priority-urgent' : '');
        $stCls = $a['status'] === 'Confirmed' ? 'pill-green' : ($a['status'] === 'Completed' ? 'pill-gray' : ($a['status'] === 'Cancelled' ? 'pill-red' : 'pill-yellow'));
      ?>
      <tr class="<?= $rowCls ?>">
        <td><?= $i+1 ?></td>
        <td><span class="queue-number"><?= $a['queue_number'] ?? '—' ?></span></td>
        <td>
          <strong><?= htmlspecialchars($a['student_name']) ?></strong><br>
          <span class="text-muted"><?= htmlspecialchars($a['student_uid'] ?? '') ?></span>
        </td>
        <td><?= $a['appointment_date'] ?> <span class="text-muted">@ <?= substr($a['appointment_time'],0,5) ?></span></td>
        <td><span class="pill <?= $prCls ?>"><?= $a['priority'] ?></span></td>
        <td class="text-muted"><?= htmlspecialchars(mb_substr($a['reason'] ?? '—', 0, 50)) ?></td>
        <td><span class="pill <?= $stCls ?>"><?= $a['status'] ?></span></td>
        <td>
          <?php if (in_array($a['status'], ['Pending','Confirmed'])): ?>
            <a href="<?= BASE_URL ?>/public/doctor/consult.php?appointment_id=<?= $a['appointment_id'] ?>" class="btn btn-sm btn-primary">Consult</a>
            <form method="POST" action="<?= BASE_URL ?>/public/doctor/appointments.php" style="display:inline">
              <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
              <input type="hidden" name="appointment_id" value="<?= $a['appointment_id'] ?>">
              <?php if ($a['status'] === 'Pending'): ?>
                <button name="status" value="Confirmed" class="btn btn-sm btn-success">Confirm</button>
              <?php endif; ?>
              <button name="status" value="Cancelled" class="btn btn-sm btn-danger"
                onclick="return confirm('Cancel this appointment?')">Cancel</button>
            </form>
          <?php endif; ?>
          <a href="<?= BASE_URL ?>/public/doctor/patient-record.php?student_id=<?= $a['student_id'] ?>" class="btn btn-sm btn-secondary">📋 Record</a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($appointments)): ?>
        <tr><td colspan="8" class="text-center text-muted" style="padding:30px">No appointments found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
